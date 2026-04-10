<?php

namespace App\Services\Payments;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentReconciliationQueryService
{
    private const RECONCILABLE_METHODS = ['stripe', 'xendit'];

    public function getDashboardData(array $filters): array
    {
        $filters = $this->normalizeFilters($filters);
        $baseQuery = $this->buildBaseQuery($filters);
        $ordersQuery = clone $baseQuery;

        $this->applyIssueScope($ordersQuery, $filters['issue_type']);

        $orders = $ordersQuery
            ->select(
                'orders.id_order',
                'orders.customer_name',
                'orders.base_amount',
                'orders.payment_method',
                'orders.payment_channel',
                'orders.gateway_fee_amount',
                'orders.gateway_net_amount',
                'orders.gateway_fee_source',
                'orders.status',
                'orders.created_at',
                'orders.paid_at',
                'orders.refund_status',
                'orders.refund_reason'
            )
            ->orderByRaw("
                CASE
                    WHEN orders.status = 'refund_requested' THEN 1
                    WHEN orders.status = 'pending' THEN 2
                    ELSE 3
                END
            ")
            ->orderByDesc('orders.created_at')
            ->paginate(12, ['*'], 'reconciliation_page')
            ->withQueryString();

        $orders->setCollection(
            $orders->getCollection()->map(fn ($order) => $this->decorateOrder($order))
        );

        return [
            'filters' => $filters,
            'summary' => $this->buildSummary($baseQuery),
            'orders' => $orders,
            'availablePaymentMethods' => $this->getAvailablePaymentMethods($filters),
            'availablePaymentChannels' => $this->getAvailablePaymentChannels($filters),
        ];
    }

    private function normalizeFilters(array $filters): array
    {
        return [
            'start_date' => (string) ($filters['start_date'] ?? now()->startOfMonth()->format('Y-m-d')),
            'end_date' => (string) ($filters['end_date'] ?? now()->format('Y-m-d')),
            'issue_type' => (string) ($filters['issue_type'] ?? 'all'),
            'payment_method' => (string) ($filters['payment_method'] ?? 'all'),
            'payment_channel' => (string) ($filters['payment_channel'] ?? 'all'),
        ];
    }

    private function buildSummary(Builder $baseQuery): array
    {
        $estimatedQuery = clone $baseQuery;
        $pendingQuery = clone $baseQuery;
        $refundQuery = clone $baseQuery;
        $estimatedAmountQuery = clone $baseQuery;
        $estimatedGrossQuery = clone $baseQuery;
        $gatewayMixQuery = clone $baseQuery;

        $this->scopeEstimatedFee($estimatedQuery);
        $this->scopePendingPayment($pendingQuery);
        $this->scopeRefundRequested($refundQuery);
        $this->scopeEstimatedFee($estimatedAmountQuery);
        $this->scopeEstimatedFee($estimatedGrossQuery);
        $this->applyIssueScope($gatewayMixQuery, 'all');

        $estimatedFeeCount = (int) $estimatedQuery->count();
        $pendingPaymentCount = (int) $pendingQuery->count();
        $refundRequestedCount = (int) $refundQuery->count();
        $gatewayMix = $gatewayMixQuery
            ->select('orders.payment_method', DB::raw('COUNT(*) as total'))
            ->groupBy('orders.payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return (object) [
                    'payment_method' => $row->payment_method,
                    'label' => payment_method_label($row->payment_method),
                    'total' => (int) $row->total,
                ];
            });

        return [
            'total_exceptions' => $estimatedFeeCount + $pendingPaymentCount + $refundRequestedCount,
            'estimated_fee_count' => $estimatedFeeCount,
            'pending_payment_count' => $pendingPaymentCount,
            'refund_requested_count' => $refundRequestedCount,
            'estimated_fee_amount' => (float) $estimatedAmountQuery->sum('orders.gateway_fee_amount'),
            'estimated_gross_amount' => (float) $estimatedGrossQuery->sum('orders.base_amount'),
            'refreshable_count' => $estimatedFeeCount,
            'gateway_mix' => $gatewayMix,
        ];
    }

    private function buildBaseQuery(array $filters): Builder
    {
        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']);

        if ($filters['payment_method'] !== 'all') {
            $query->where('orders.payment_method', $filters['payment_method']);
        }

        if ($filters['payment_channel'] !== 'all') {
            $query->where('orders.payment_channel', $filters['payment_channel']);
        }

        return $query;
    }

    private function applyIssueScope(Builder $query, string $issueType): void
    {
        if ($issueType === 'estimated_fee') {
            $this->scopeEstimatedFee($query);

            return;
        }

        if ($issueType === 'pending_payment') {
            $this->scopePendingPayment($query);

            return;
        }

        if ($issueType === 'refund_requested') {
            $this->scopeRefundRequested($query);

            return;
        }

        $query->where(function ($builder) {
            $builder->where(function ($estimatedBuilder) {
                $this->scopeEstimatedFee($estimatedBuilder);
            })->orWhere(function ($pendingBuilder) {
                $this->scopePendingPayment($pendingBuilder);
            })->orWhere(function ($refundBuilder) {
                $this->scopeRefundRequested($refundBuilder);
            });
        });
    }

    private function scopeEstimatedFee(Builder $query): void
    {
        $query->whereIn('orders.payment_method', self::RECONCILABLE_METHODS)
            ->whereIn('orders.status', ['paid', 'confirmed', 'completed', 'refunded'])
            ->whereRaw('LOWER(COALESCE(orders.gateway_fee_source, "")) <> ?', ['actual']);
    }

    private function scopePendingPayment(Builder $query): void
    {
        $threshold = Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s');

        $query->whereIn('orders.payment_method', self::RECONCILABLE_METHODS)
            ->where('orders.status', 'pending')
            ->where('orders.created_at', '<=', $threshold);
    }

    private function scopeRefundRequested(Builder $query): void
    {
        $query->where('orders.status', 'refund_requested');
    }

    private function decorateOrder(object $order): object
    {
        $issueType = $this->resolveIssueType($order);
        $ageReference = $order->paid_at ?: $order->created_at;
        $ageInHours = Carbon::parse($ageReference)->diffInHours(now());
        $gatewayAmounts = resolve_gateway_amounts(
            $order->base_amount ?? 0,
            $order->gateway_fee_amount ?? 0,
            $order->gateway_net_amount ?? null
        );

        $order->gateway_fee_amount = $gatewayAmounts['fee_amount'];
        $order->gateway_net_amount = $gatewayAmounts['net_amount'];
        $order->issue_type = $issueType;
        $order->issue_label = match ($issueType) {
            'estimated_fee' => 'Fee still estimated',
            'pending_payment' => 'Pending payment > 30 min',
            'refund_requested' => 'Refund request waiting',
            default => 'Needs review',
        };
        $order->issue_badge_class = match ($issueType) {
            'estimated_fee' => 'bg-warning-subtle text-warning',
            'pending_payment' => 'bg-info-subtle text-info',
            'refund_requested' => 'bg-danger-subtle text-danger',
            default => 'bg-secondary-subtle text-secondary',
        };
        $order->issue_priority = $this->resolveIssuePriority($issueType, $ageInHours);
        $order->issue_priority_badge_class = match ($order->issue_priority) {
            'High' => 'bg-danger-subtle text-danger',
            'Medium' => 'bg-warning-subtle text-warning',
            default => 'bg-success-subtle text-success',
        };
        $order->age_label = $ageInHours >= 48
            ? $ageInHours . 'h'
            : max(1, Carbon::parse($ageReference)->diffInMinutes(now())) . 'm';
        $order->can_refresh_fee = in_array($order->payment_method, self::RECONCILABLE_METHODS, true)
            && $issueType === 'estimated_fee';

        return $order;
    }

    private function resolveIssueType(object $order): string
    {
        if (($order->status ?? null) === 'refund_requested') {
            return 'refund_requested';
        }

        if (($order->status ?? null) === 'pending' && Carbon::parse($order->created_at)->lte(now()->subMinutes(30))) {
            return 'pending_payment';
        }

        if (
            in_array(($order->payment_method ?? null), self::RECONCILABLE_METHODS, true)
            && in_array(($order->status ?? null), ['paid', 'confirmed', 'completed', 'refunded'], true)
            && strtolower(trim((string) ($order->gateway_fee_source ?? ''))) !== 'actual'
        ) {
            return 'estimated_fee';
        }

        return 'needs_review';
    }

    private function resolveIssuePriority(string $issueType, int $ageInHours): string
    {
        return match ($issueType) {
            'pending_payment' => $ageInHours >= 2 ? 'High' : 'Medium',
            'refund_requested' => $ageInHours >= 24 ? 'High' : 'Medium',
            'estimated_fee' => $ageInHours >= 48 ? 'High' : 'Medium',
            default => 'Low',
        };
    }

    private function getAvailablePaymentMethods(array $filters): Collection
    {
        $query = $this->buildBaseQuery(array_merge($filters, ['payment_method' => 'all']));
        $this->applyIssueScope($query, $filters['issue_type']);

        return $query
            ->select('orders.payment_method')
            ->distinct()
            ->get()
            ->pluck('payment_method')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function getAvailablePaymentChannels(array $filters): Collection
    {
        $query = $this->buildBaseQuery(array_merge($filters, ['payment_channel' => 'all']));
        $this->applyIssueScope($query, $filters['issue_type']);

        return $query
            ->select('orders.payment_channel')
            ->distinct()
            ->get()
            ->pluck('payment_channel')
            ->filter()
            ->unique()
            ->sortBy(fn ($channel) => payment_channel_label($channel))
            ->values();
    }
}
