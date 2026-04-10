<?php

namespace Tests\Unit\Sales;

use App\Services\Finance\OrderFinancialProjectionService;
use App\Services\Sales\SalesProfitCalculator;
use Mockery;
use Tests\TestCase;

class SalesProfitCalculatorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_builds_recent_transactions_using_shared_projection_values(): void
    {
        $projectionService = Mockery::mock(OrderFinancialProjectionService::class);
        $projectionService->shouldReceive('projectOrder')
            ->once()
            ->andReturn([
                'gross_profit' => 30.00,
                'net_profit_impact' => 25.50,
                'vendor_breakdown' => [
                    'boat_total' => 20.00,
                    'homestay_total' => 50.00,
                    'culinary_total' => 30.00,
                    'kiosk_total' => 20.00,
                ],
            ]);

        $calculator = new SalesProfitCalculator($projectionService);

        $orderItems = collect([
            (object) [
                'id_order' => 'ORD-001',
                'customer_name' => 'Aqsha',
                'base_amount' => 150.00,
                'payment_amount' => 150.00,
                'payment_currency' => 'MYR',
                'display_currency' => 'MYR',
                'display_amount' => 150.00,
                'payment_method' => 'stripe',
                'payment_channel' => 'card_visa',
                'gateway_fee_amount' => 4.50,
                'gateway_net_amount' => 145.50,
                'gateway_fee_source' => 'actual',
                'refund_fee' => 0.0,
                'status' => 'paid',
                'created_at' => '2026-04-10 10:00:00',
                'nama_paket' => 'Paket Cihuy',
            ],
        ]);

        $grouped = $calculator->buildGroupedOrders($orderItems);

        $this->assertCount(1, $grouped);
        $this->assertSame('ORD-001', $grouped[0]->id_order);
        $this->assertSame(30.00, $grouped[0]->original_profit);
        $this->assertSame(25.50, $grouped[0]->company_profit);
        $this->assertSame(20.00, $grouped[0]->revenue_breakdown['boat_total']);
        $this->assertSame('Paket Cihuy', $grouped[0]->package_names);
    }
}
