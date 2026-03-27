<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'gateway_fee_source')) {
                $table->string('gateway_fee_source', 20)->nullable()->after('gateway_net_amount');
            }
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_logs', 'fee_source')) {
                $table->string('fee_source', 20)->nullable()->after('net_amount');
            }
        });

        $successLogs = DB::table('payment_logs')
            ->where('payment_method', 'xendit')
            ->where('status', 'success')
            ->orderBy('id')
            ->get(['id', 'id_order', 'payment_channel', 'amount', 'fee_amount', 'net_amount']);

        $latestByOrder = [];

        foreach ($successLogs as $log) {
            $grossAmount = (float) ($log->amount ?? 0);
            $feeAmount = $log->fee_amount !== null ? (float) $log->fee_amount : null;
            $netAmount = $log->net_amount !== null ? (float) $log->net_amount : null;
            $feeSource = ($feeAmount !== null || $netAmount !== null) ? 'actual' : 'estimated';

            if ($feeAmount === null || $netAmount === null) {
                [$feeAmount, $netAmount] = $this->estimateXenditFee((string) ($log->payment_channel ?? ''), $grossAmount);
            }

            DB::table('payment_logs')
                ->where('id', $log->id)
                ->update([
                    'fee_amount' => $feeAmount,
                    'net_amount' => $netAmount,
                    'fee_source' => $feeSource,
                ]);

            $latestByOrder[$log->id_order] = [
                'gateway_fee_amount' => $feeAmount,
                'gateway_net_amount' => $netAmount,
                'gateway_fee_source' => $feeSource,
            ];
        }

        foreach ($latestByOrder as $orderId => $payload) {
            DB::table('orders')
                ->where('id_order', $orderId)
                ->update($payload);
        }

        DB::table('orders')
            ->where('payment_method', 'stripe')
            ->whereNotNull('gateway_fee_amount')
            ->whereNull('gateway_fee_source')
            ->update(['gateway_fee_source' => 'actual']);
    }

    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            if (Schema::hasColumn('payment_logs', 'fee_source')) {
                $table->dropColumn('fee_source');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'gateway_fee_source')) {
                $table->dropColumn('gateway_fee_source');
            }
        });
    }

    private function estimateXenditFee(string $channel, float $grossAmount): array
    {
        $channel = strtoupper($channel);

        $rules = config('payment.methods.xendit.reporting_fee_rules.channels.' . $channel)
            ?? config('payment.methods.xendit.reporting_fee_rules.default', []);

        $percentage = (float) ($rules['percentage'] ?? 0);
        $fixed = (float) ($rules['fixed'] ?? 0);
        $minimum = (float) ($rules['minimum'] ?? 0);

        $feeAmount = ($grossAmount * $percentage) + $fixed;
        $feeAmount = max($feeAmount, $minimum);
        $feeAmount = min($feeAmount, $grossAmount);

        return [round($feeAmount, 2), round(max(0, $grossAmount - $feeAmount), 2)];
    }
};
