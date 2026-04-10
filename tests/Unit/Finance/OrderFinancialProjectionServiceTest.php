<?php

namespace Tests\Unit\Finance;

use App\Services\Finance\OrderFinancialProjectionService;
use App\Services\FinancialReport\OrderCostCalculator;
use Mockery;
use Tests\TestCase;

class OrderFinancialProjectionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_projects_operating_order_with_shared_financial_formula(): void
    {
        $costCalculator = Mockery::mock(OrderCostCalculator::class);
        $costCalculator->shouldReceive('calculateOrderCost')
            ->once()
            ->with('ORD-001')
            ->andReturn([
                'total' => 120.00,
                'breakdown' => [
                    'boats' => ['items' => [], 'total' => 20.00],
                    'homestays' => ['items' => [], 'total' => 50.00],
                    'culinary' => ['items' => [], 'total' => 30.00],
                    'kiosks' => ['items' => [], 'total' => 20.00],
                ],
            ]);

        $service = new OrderFinancialProjectionService($costCalculator);

        $order = (object) [
            'id_order' => 'ORD-001',
            'customer_name' => 'Aqsha',
            'status' => 'paid',
            'base_amount' => 150.00,
            'gateway_fee_amount' => 4.50,
            'gateway_net_amount' => 145.50,
            'payment_method' => 'stripe',
            'payment_channel' => 'card_visa',
            'gateway_fee_source' => 'actual',
            'display_currency' => 'MYR',
            'display_amount' => 150.00,
            'created_at' => '2026-04-10 10:00:00',
        ];

        $orderItems = collect([
            (object) ['original_subtotal' => 100.00, 'subtotal' => 100.00, 'discount_amount' => 0],
            (object) ['original_subtotal' => 60.00, 'subtotal' => 50.00, 'discount_amount' => 10.00],
        ]);

        $projection = $service->projectOrder($order, $orderItems);

        $this->assertSame(160.00, $projection['gross_revenue']);
        $this->assertSame(10.00, $projection['sales_discount']);
        $this->assertSame(150.00, $projection['net_revenue']);
        $this->assertSame(120.00, $projection['cost_of_sales']);
        $this->assertSame(30.00, $projection['gross_profit']);
        $this->assertSame(4.50, $projection['gateway_fee']);
        $this->assertSame(145.50, $projection['gateway_net_amount']);
        $this->assertSame(25.50, $projection['net_profit_impact']);
        $this->assertSame(20.00, $projection['vendor_breakdown']['boat_total']);
    }

    public function test_it_projects_refunded_order_with_refund_fee_as_other_income(): void
    {
        $costCalculator = Mockery::mock(OrderCostCalculator::class);
        $costCalculator->shouldNotReceive('calculateOrderCost');

        $service = new OrderFinancialProjectionService($costCalculator);

        $order = (object) [
            'id_order' => 'ORD-002',
            'customer_name' => 'Aqsha',
            'status' => 'refunded',
            'base_amount' => 125.00,
            'gateway_fee_amount' => 1.62,
            'gateway_net_amount' => 123.38,
            'refund_fee' => 5.00,
            'payment_method' => 'xendit',
            'payment_channel' => 'SHOPEEPAY',
            'gateway_fee_source' => 'actual',
            'created_at' => '2026-04-10 11:00:00',
        ];

        $projection = $service->projectOrder($order, collect());

        $this->assertSame(0.0, $projection['revenue']);
        $this->assertSame(0.0, $projection['cost_of_sales']);
        $this->assertSame(5.00, $projection['other_income']);
        $this->assertSame(1.62, $projection['gateway_fee']);
        $this->assertSame(3.38, $projection['net_profit_impact']);
    }
}
