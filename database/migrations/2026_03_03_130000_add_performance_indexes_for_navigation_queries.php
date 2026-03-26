<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('orders', ['status', 'created_at'], 'orders_status_created_at_idx');
        $this->addIndexIfMissing('orders', ['created_at'], 'orders_created_at_idx');
        $this->addIndexIfMissing('orders', ['user_id'], 'orders_user_id_idx');
        $this->addIndexIfMissing('orders', ['refund_status'], 'orders_refund_status_idx');
        $this->addIndexIfMissing('orders', ['display_currency'], 'orders_display_currency_idx');

        $this->addIndexIfMissing('order_items', ['id_order'], 'order_items_order_idx');
        $this->addIndexIfMissing('order_items', ['id_paket'], 'order_items_paket_idx');
        $this->addIndexIfMissing('order_items', ['tanggal_keberangkatan'], 'order_items_departure_date_idx');
        $this->addIndexIfMissing('order_items', ['id_order', 'tanggal_keberangkatan'], 'order_items_order_departure_idx');

        $this->addIndexIfMissing('paket_wisata_boat', ['id_paket', 'id_boat'], 'paket_wisata_boat_paket_boat_idx');
        $this->addIndexIfMissing('paket_wisata_homestay', ['id_paket', 'id_homestay'], 'paket_wisata_homestay_paket_homestay_idx');
        $this->addIndexIfMissing('paket_wisata_culinary', ['id_paket', 'id_paket_culinary'], 'paket_wisata_culinary_paket_culinary_idx');
        $this->addIndexIfMissing('paket_wisata_kiosk', ['id_paket', 'id_kiosk'], 'paket_wisata_kiosk_paket_kiosk_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('orders', 'orders_status_created_at_idx');
        $this->dropIndexIfExists('orders', 'orders_created_at_idx');
        $this->dropIndexIfExists('orders', 'orders_user_id_idx');
        $this->dropIndexIfExists('orders', 'orders_refund_status_idx');
        $this->dropIndexIfExists('orders', 'orders_display_currency_idx');

        $this->dropIndexIfExists('order_items', 'order_items_order_idx');
        $this->dropIndexIfExists('order_items', 'order_items_paket_idx');
        $this->dropIndexIfExists('order_items', 'order_items_departure_date_idx');
        $this->dropIndexIfExists('order_items', 'order_items_order_departure_idx');

        $this->dropIndexIfExists('paket_wisata_boat', 'paket_wisata_boat_paket_boat_idx');
        $this->dropIndexIfExists('paket_wisata_homestay', 'paket_wisata_homestay_paket_homestay_idx');
        $this->dropIndexIfExists('paket_wisata_culinary', 'paket_wisata_culinary_paket_culinary_idx');
        $this->dropIndexIfExists('paket_wisata_kiosk', 'paket_wisata_kiosk_paket_kiosk_idx');
    }

    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
        } catch (\Throwable $exception) {
            // Index may already exist in current environment.
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropIndex($indexName);
            });
        } catch (\Throwable $exception) {
            // Index may not exist in current environment.
        }
    }
};
