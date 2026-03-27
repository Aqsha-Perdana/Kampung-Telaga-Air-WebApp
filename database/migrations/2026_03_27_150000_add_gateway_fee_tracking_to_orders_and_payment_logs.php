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
            if (!Schema::hasColumn('orders', 'payment_channel')) {
                $table->string('payment_channel', 100)->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'gateway_fee_amount')) {
                $table->decimal('gateway_fee_amount', 12, 2)->nullable()->after('payment_amount');
            }

            if (!Schema::hasColumn('orders', 'gateway_fee_currency')) {
                $table->string('gateway_fee_currency', 3)->nullable()->after('gateway_fee_amount');
            }

            if (!Schema::hasColumn('orders', 'gateway_net_amount')) {
                $table->decimal('gateway_net_amount', 12, 2)->nullable()->after('gateway_fee_currency');
            }
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_logs', 'payment_channel')) {
                $table->string('payment_channel', 100)->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('payment_logs', 'fee_amount')) {
                $table->decimal('fee_amount', 12, 2)->nullable()->after('amount');
            }

            if (!Schema::hasColumn('payment_logs', 'fee_currency')) {
                $table->string('fee_currency', 3)->nullable()->after('fee_amount');
            }

            if (!Schema::hasColumn('payment_logs', 'net_amount')) {
                $table->decimal('net_amount', 12, 2)->nullable()->after('fee_currency');
            }
        });

        $successLogs = DB::table('payment_logs')
            ->where('payment_method', 'xendit')
            ->where('status', 'success')
            ->orderBy('id')
            ->get(['id', 'id_order', 'payment_intent_id', 'response_data', 'amount', 'currency']);

        $latestByOrder = [];

        foreach ($successLogs as $log) {
            $payload = json_decode((string) $log->response_data, true);
            if (!is_array($payload)) {
                continue;
            }

            $grossAmount = isset($payload['paid_amount'])
                ? (float) $payload['paid_amount']
                : (float) ($payload['amount'] ?? $log->amount ?? 0);
            $netAmount = null;
            $feeAmount = null;

            if (isset($payload['adjusted_received_amount'])) {
                $netAmount = (float) $payload['adjusted_received_amount'];
                $feeAmount = max(0, $grossAmount - $netAmount);
            } elseif (isset($payload['fees_paid_amount'])) {
                $feeAmount = max(0, (float) $payload['fees_paid_amount']);
                $netAmount = max(0, $grossAmount - $feeAmount);
            }

            $channel = (string) ($payload['payment_channel'] ?? $payload['payment_method'] ?? $payload['bank_code'] ?? '');
            $currency = strtoupper((string) ($payload['currency'] ?? $log->currency ?? 'MYR'));

            DB::table('payment_logs')
                ->where('id', $log->id)
                ->update([
                    'payment_channel' => $channel !== '' ? $channel : null,
                    'fee_amount' => $feeAmount,
                    'fee_currency' => $currency,
                    'net_amount' => $netAmount,
                ]);

            $latestByOrder[$log->id_order] = [
                'payment_channel' => $channel !== '' ? $channel : null,
                'gateway_fee_amount' => $feeAmount,
                'gateway_fee_currency' => $currency,
                'gateway_net_amount' => $netAmount,
                'payment_intent_id' => $log->payment_intent_id,
            ];
        }

        foreach ($latestByOrder as $orderId => $data) {
            DB::table('orders')
                ->where('id_order', $orderId)
                ->update($data);
        }
    }

    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('payment_logs', 'payment_channel') ? 'payment_channel' : null,
                Schema::hasColumn('payment_logs', 'fee_amount') ? 'fee_amount' : null,
                Schema::hasColumn('payment_logs', 'fee_currency') ? 'fee_currency' : null,
                Schema::hasColumn('payment_logs', 'net_amount') ? 'net_amount' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('orders', 'payment_channel') ? 'payment_channel' : null,
                Schema::hasColumn('orders', 'gateway_fee_amount') ? 'gateway_fee_amount' : null,
                Schema::hasColumn('orders', 'gateway_fee_currency') ? 'gateway_fee_currency' : null,
                Schema::hasColumn('orders', 'gateway_net_amount') ? 'gateway_net_amount' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
