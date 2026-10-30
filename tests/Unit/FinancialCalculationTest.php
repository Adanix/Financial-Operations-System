<?php

namespace Tests\Unit;

use App\Services\DecimalMath;
use App\Services\InvoiceCalculator;
use App\Services\ExpenseCalculator;
use PHPUnit\Framework\TestCase;

class FinancialCalculationTest extends TestCase
{
    public function test_example_a_inclusive_tax(): void
    {
        // Example A: One line, qty 2, unit price 110.00, tax 10%, inclusive mode
        $res = InvoiceCalculator::calculate([
            [
                'quantity' => '2',
                'unit_price' => '110.00',
                'line_discount_amount' => '0',
                'tax_rate' => '10.00',
            ]
        ], 'inclusive', 'none', 0, 2);

        $this->assertEquals('220.00', $res['total']);
        $this->assertEquals('20.00', $res['tax_total']);
        $this->assertEquals('200.00', $res['net_sales_excluding_tax']);
        $this->assertEquals('220.00', $res['gross_subtotal']);
    }

    public function test_example_b_exclusive_tax(): void
    {
        // Example B: One line, qty 2, unit price 100.00, tax 10%, exclusive mode
        $res = InvoiceCalculator::calculate([
            [
                'quantity' => '2',
                'unit_price' => '100.00',
                'line_discount_amount' => '0',
                'tax_rate' => '10.00',
            ]
        ], 'exclusive', 'none', 0, 2);

        $this->assertEquals('200.00', $res['taxable_subtotal']);
        $this->assertEquals('20.00', $res['tax_total']);
        $this->assertEquals('220.00', $res['total']);
        $this->assertEquals('200.00', $res['net_sales_excluding_tax']);
    }

    public function test_example_d_discount_allocation_largest_remainder(): void
    {
        // Two lines with different amounts and a fixed discount
        $lines = [
            [
                'quantity' => '1',
                'unit_price' => '100.00',
                'line_discount_amount' => '0',
                'tax_rate' => '10.00',
            ],
            [
                'quantity' => '1',
                'unit_price' => '200.00',
                'line_discount_amount' => '0',
                'tax_rate' => '10.00',
            ]
        ];

        $res = InvoiceCalculator::calculate($lines, 'exclusive', 'fixed', '25.00', 2);

        $this->assertEquals('25.00', $res['invoice_discount_total']);
        
        $allocSum = '0.00';
        foreach ($res['lines'] as $line) {
            $allocSum = DecimalMath::add($allocSum, $line['invoice_discount_allocation'], 2);
        }

        // Must reconcile exactly
        $this->assertEquals('25.00', $allocSum);
        // Line 1: 100 / 300 * 25 = 8.3333... -> 8.33
        // Line 2: 200 / 300 * 25 = 16.6666... -> 16.67
        $this->assertEquals('8.33', $res['lines'][0]['invoice_discount_allocation']);
        $this->assertEquals('16.67', $res['lines'][1]['invoice_discount_allocation']);
        $this->assertEquals('91.67', $res['lines'][0]['taxable_amount']);
        $this->assertEquals('183.33', $res['lines'][1]['taxable_amount']);
        $this->assertEquals('275.00', $res['taxable_subtotal']);
    }

    public function test_line_discount_capped_at_gross(): void
    {
        $res = InvoiceCalculator::calculate([
            [
                'quantity' => '1',
                'unit_price' => '50.00',
                'line_discount_amount' => '75.00', // exceeds gross
                'tax_rate' => '10.00',
            ]
        ], 'exclusive', 'none', 0, 2);

        $this->assertEquals('50.00', $res['lines'][0]['line_discount_amount']);
        $this->assertEquals('0.00', $res['lines'][0]['net_amount']);
        $this->assertEquals('0.00', $res['total']);
    }

    public function test_percentage_invoice_discount(): void
    {
        $res = InvoiceCalculator::calculate([
            [
                'quantity' => '1',
                'unit_price' => '200.00',
                'line_discount_amount' => '0',
                'tax_rate' => '10.00',
            ]
        ], 'exclusive', 'percent', '15', 2); // 15% of 200 = 30.00

        $this->assertEquals('30.00', $res['invoice_discount_total']);
        $this->assertEquals('170.00', $res['taxable_subtotal']);
        $this->assertEquals('17.00', $res['tax_total']);
        $this->assertEquals('187.00', $res['total']);
    }

    public function test_zero_decimal_currency(): void
    {
        // JPY mode (decimals = 0)
        $res = InvoiceCalculator::calculate([
            [
                'quantity' => '3',
                'unit_price' => '1000',
                'line_discount_amount' => '200',
                'tax_rate' => '10.00',
            ]
        ], 'exclusive', 'none', 0, 0);

        $this->assertEquals('3000', $res['gross_subtotal']);
        $this->assertEquals('200', $res['line_discount_total']);
        $this->assertEquals('2800', $res['net_subtotal']);
        $this->assertEquals('280', $res['tax_total']);
        $this->assertEquals('3080', $res['total']);
    }

    public function test_fractional_quantity_three_decimals(): void
    {
        $res = InvoiceCalculator::calculate([
            [
                'quantity' => '2.500',
                'unit_price' => '80.00',
                'line_discount_amount' => '0',
                'tax_rate' => '0',
            ]
        ], 'exclusive', 'none', 0, 2);

        $this->assertEquals('200.00', $res['gross_subtotal']);
        $this->assertEquals('200.00', $res['total']);
    }

    public function test_expense_calculations(): void
    {
        // Exclusive expense with recoverable tax
        $exp1 = ExpenseCalculator::calculate('100.00', '10.00', 'exclusive', true, 2);
        $this->assertEquals('100.00', $exp1['net_amount']);
        $this->assertEquals('10.00', $exp1['tax_amount']);
        $this->assertEquals('110.00', $exp1['gross_amount']);
        $this->assertEquals('100.00', $exp1['expense_for_pnl']);
        $this->assertEquals('10.00', $exp1['recoverable_input_tax']);

        // Exclusive expense with NON-recoverable tax
        $exp2 = ExpenseCalculator::calculate('100.00', '10.00', 'exclusive', false, 2);
        $this->assertEquals('100.00', $exp2['net_amount']);
        $this->assertEquals('10.00', $exp2['tax_amount']);
        $this->assertEquals('110.00', $exp2['gross_amount']);
        $this->assertEquals('110.00', $exp2['expense_for_pnl']); // Gross enters P&L
        $this->assertEquals('0.00', $exp2['recoverable_input_tax']);

        // Inclusive expense with recoverable tax
        $exp3 = ExpenseCalculator::calculate('110.00', '10.00', 'inclusive', true, 2);
        $this->assertEquals('100.00', $exp3['net_amount']);
        $this->assertEquals('10.00', $exp3['tax_amount']);
        $this->assertEquals('110.00', $exp3['gross_amount']);
        $this->assertEquals('100.00', $exp3['expense_for_pnl']);
        $this->assertEquals('10.00', $exp3['recoverable_input_tax']);
    }
}
