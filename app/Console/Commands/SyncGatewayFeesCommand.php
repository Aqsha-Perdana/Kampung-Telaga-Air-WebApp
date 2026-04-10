<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Payments\PaymentReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SyncGatewayFeesCommand extends Command
{
    protected $signature = 'payments:sync-gateway-fees
                            {--provider=all : all, stripe, or xendit}
                            {--days=7 : Only scan recent orders created within the given number of days}
                            {--limit=100 : Maximum number of orders to scan per run}
                            {--order= : Sync one specific order id}
                            {--dry-run : Preview matching orders without calling the gateways}';

    protected $description = 'Sync actual Stripe/Xendit gateway fees for orders that are still marked as estimated.';

    public function __construct(
        private readonly PaymentReconciliationService $paymentReconciliationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $providers = $this->resolveProviders();
        if ($providers === []) {
            $this->error('Invalid provider. Use all, stripe, or xendit.');

            return self::FAILURE;
        }

        $orders = $this->resolveOrders($providers);

        if ($orders->isEmpty()) {
            $this->components->info('No estimated gateway fee orders matched this run.');

            return self::SUCCESS;
        }

        $this->components->info('Gateway fee sync candidates: ' . $orders->count());

        if ($this->option('dry-run')) {
            $this->table(
                ['Order', 'Provider', 'Status', 'Created', 'Fee Source'],
                $orders->map(fn (Order $order) => [
                    $order->id_order,
                    $order->payment_method,
                    $order->status,
                    optional($order->created_at)->format('Y-m-d H:i:s'),
                    $order->gateway_fee_source ?: 'estimated',
                ])->all()
            );

            $this->comment('Dry run only. No gateway lookup was performed.');

            return self::SUCCESS;
        }

        $summary = [
            'processed' => 0,
            'updated' => 0,
            'not_ready' => 0,
            'failed' => 0,
        ];

        foreach ($orders as $order) {
            $summary['processed']++;

            $result = $this->paymentReconciliationService->refreshGatewayFeeIfAvailable($order);

            if (($result['updated'] ?? false) === true) {
                $summary['updated']++;
                $this->line(sprintf(
                    '[updated] %s %s fee=RM %s net=RM %s',
                    $order->id_order,
                    strtoupper((string) $order->payment_method),
                    number_format((float) ($result['fee_amount'] ?? 0), 2),
                    number_format((float) ($result['net_amount'] ?? 0), 2)
                ));

                continue;
            }

            $reason = (string) ($result['reason'] ?? 'unknown');
            if (in_array($reason, ['not_ready', 'transaction_not_found'], true)) {
                $summary['not_ready']++;
                $this->line(sprintf('[waiting] %s %s reason=%s', $order->id_order, strtoupper((string) $order->payment_method), $reason));
            } else {
                $summary['failed']++;
                $this->warn(sprintf('[failed] %s %s reason=%s', $order->id_order, strtoupper((string) $order->payment_method), $reason));
            }
        }

        Log::info('Scheduled gateway fee sync completed.', [
            'providers' => $providers,
            'summary' => $summary,
            'order' => $this->option('order'),
        ]);

        $this->newLine();
        $this->table(
            ['Processed', 'Updated', 'Waiting', 'Failed'],
            [[
                $summary['processed'],
                $summary['updated'],
                $summary['not_ready'],
                $summary['failed'],
            ]]
        );

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function resolveProviders(): array
    {
        $provider = strtolower(trim((string) $this->option('provider')));

        return match ($provider) {
            '', 'all' => ['stripe', 'xendit'],
            'stripe' => ['stripe'],
            'xendit' => ['xendit'],
            default => [],
        };
    }

    /**
     * @param array<int, string> $providers
     */
    private function resolveOrders(array $providers): Collection
    {
        $query = Order::query()
            ->whereIn('payment_method', $providers)
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->where(function ($builder) {
                $builder
                    ->whereNull('gateway_fee_source')
                    ->orWhere('gateway_fee_source', '!=', 'actual');
            })
            ->orderByDesc('created_at');

        $specificOrder = trim((string) $this->option('order'));
        if ($specificOrder !== '') {
            return $query
                ->where('id_order', $specificOrder)
                ->get();
        }

        $days = max(1, (int) $this->option('days'));
        $limit = max(1, (int) $this->option('limit'));

        return $query
            ->where('created_at', '>=', now()->subDays($days))
            ->limit($limit)
            ->get();
    }
}
