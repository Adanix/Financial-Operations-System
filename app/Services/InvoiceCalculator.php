<?php

namespace App\Services;

class InvoiceCalculator
{
    /**
     * Calculate financial totals for an invoice and its lines.
     *
     * @param array $lines Array of line items:
     *                     [
     *                       'quantity' => '2',
     *                       'unit_price' => '100.00',
     *                       'line_discount_amount' => '0.00',
     *                       'tax_rate' => '10.00',
     *                       ...
     *                     ]
     * @param string $pricingMode 'exclusive' | 'inclusive'
     * @param string $discountType 'none' | 'percent' | 'fixed'
     * @param string|float|int $discountValue
     * @param int $decimals Currency decimal places (default 2)
     * @return array Calculated results including line breakdowns and aggregates.
     */
    public static function calculate(
        array $lines,
        string $pricingMode = 'exclusive',
        string $discountType = 'none',
        mixed $discountValue = 0,
        int $decimals = 2
    ): array {
        $computedLines = [];
        $grossSubtotal = '0.00';
        $lineDiscountTotal = '0.00';
        $netSubtotal = '0.00';

        // Step 1: Compute gross and net per line
        foreach ($lines as $index => $line) {
            $qty = (string) ($line['quantity'] ?? '1');
            $price = (string) ($line['unit_price'] ?? '0');
            $lineDiscount = (string) ($line['line_discount_amount'] ?? '0');
            $taxRate = (string) ($line['tax_rate'] ?? '0');

            $gross = DecimalMath::round(DecimalMath::mul($qty, $price, 6), $decimals);
            
            // Ensure line discount is between 0 and gross
            if (DecimalMath::comp($lineDiscount, '0') < 0) {
                $lineDiscount = '0.00';
            }
            if (DecimalMath::comp($lineDiscount, $gross) > 0) {
                $lineDiscount = $gross;
            }
            $lineDiscount = DecimalMath::round($lineDiscount, $decimals);

            $net = DecimalMath::sub($gross, $lineDiscount, $decimals);

            $grossSubtotal = DecimalMath::add($grossSubtotal, $gross, $decimals);
            $lineDiscountTotal = DecimalMath::add($lineDiscountTotal, $lineDiscount, $decimals);
            $netSubtotal = DecimalMath::add($netSubtotal, $net, $decimals);

            $computedLines[] = array_merge($line, [
                '_index' => $index,
                'quantity' => DecimalMath::round($qty, 3),
                'unit_price' => DecimalMath::round($price, $decimals),
                'line_discount_amount' => $lineDiscount,
                'tax_rate' => DecimalMath::round($taxRate, 4),
                'gross_amount' => $gross,
                'net_amount' => $net,
                'invoice_discount_allocation' => '0.00',
                'taxable_amount' => $net,
                'tax_amount' => '0.00',
                'line_total' => $net,
                'line_net_of_tax' => $net,
            ]);
        }

        // Step 2: Compute invoice-level discount D
        $invoiceDiscount = '0.00';
        if ($discountType === 'percent') {
            $pct = DecimalMath::round($discountValue, 4);
            if (DecimalMath::comp($pct, '0') > 0) {
                $pct = DecimalMath::min($pct, '100.0000', 4);
                $unroundedD = DecimalMath::div(DecimalMath::mul($netSubtotal, $pct, 6), '100', 6);
                $invoiceDiscount = DecimalMath::round($unroundedD, $decimals);
            }
        } elseif ($discountType === 'fixed') {
            $fixedVal = DecimalMath::round($discountValue, $decimals);
            if (DecimalMath::comp($fixedVal, '0') > 0) {
                $invoiceDiscount = DecimalMath::min($fixedVal, $netSubtotal, $decimals);
            }
        }

        // Step 3: Proportional allocation with largest-remainder rule
        if (DecimalMath::comp($invoiceDiscount, '0') > 0 && DecimalMath::comp($netSubtotal, '0') > 0) {
            $multiplier = pow(10, $decimals);
            $remainders = [];
            $sumFloored = '0.00';

            foreach ($computedLines as $idx => $cline) {
                $net = $cline['net_amount'];
                // Exact share: D * net / netSubtotal
                $share = DecimalMath::div(DecimalMath::mul($invoiceDiscount, $net, 8), $netSubtotal, 8);
                $flooredShare = DecimalMath::round(floor((float) $share * $multiplier) / $multiplier, $decimals);
                $rem = (float) DecimalMath::sub($share, $flooredShare, 8);

                $computedLines[$idx]['invoice_discount_allocation'] = $flooredShare;
                $sumFloored = DecimalMath::add($sumFloored, $flooredShare, $decimals);

                $remainders[] = [
                    'index' => $idx,
                    'rem' => $rem,
                ];
            }

            // Distribute remaining cents
            $centsToDistribute = (int) round(((float) $invoiceDiscount - (float) $sumFloored) * $multiplier);

            // Sort remainders descending by rem, ties resolved by index
            usort($remainders, function ($a, $b) {
                if ($b['rem'] > $a['rem']) return 1;
                if ($b['rem'] < $a['rem']) return -1;
                return $a['index'] <=> $b['index'];
            });

            $unitStep = DecimalMath::div('1', (string) $multiplier, $decimals);
            for ($k = 0; $k < $centsToDistribute && $k < count($remainders); $k++) {
                $targetIdx = $remainders[$k]['index'];
                $computedLines[$targetIdx]['invoice_discount_allocation'] = DecimalMath::add(
                    $computedLines[$targetIdx]['invoice_discount_allocation'],
                    $unitStep,
                    $decimals
                );
            }
        }

        // Step 4: Line tax and totals calculation
        $taxableSubtotal = '0.00';
        $taxTotal = '0.00';

        foreach ($computedLines as $idx => $cline) {
            $taxableBase = DecimalMath::max(
                '0.00',
                DecimalMath::sub($cline['net_amount'], $cline['invoice_discount_allocation'], $decimals),
                $decimals
            );
            $rate = $cline['tax_rate'];

            if ($pricingMode === 'inclusive') {
                if (DecimalMath::isZero($rate)) {
                    $tax = '0.00';
                } else {
                    // tax = taxableBase * r / (100 + r)
                    $denom = DecimalMath::add('100', $rate, 4);
                    $unroundedTax = DecimalMath::div(DecimalMath::mul($taxableBase, $rate, 6), $denom, 6);
                    $tax = DecimalMath::round($unroundedTax, $decimals);
                }
                $lineTotal = $taxableBase;
                $lineNetOfTax = DecimalMath::sub($taxableBase, $tax, $decimals);
            } else {
                // exclusive
                $unroundedTax = DecimalMath::div(DecimalMath::mul($taxableBase, $rate, 6), '100', 6);
                $tax = DecimalMath::round($unroundedTax, $decimals);
                $lineTotal = DecimalMath::add($taxableBase, $tax, $decimals);
                $lineNetOfTax = $taxableBase;
            }

            $computedLines[$idx]['taxable_amount'] = $taxableBase;
            $computedLines[$idx]['tax_amount'] = $tax;
            $computedLines[$idx]['line_total'] = $lineTotal;
            $computedLines[$idx]['line_net_of_tax'] = $lineNetOfTax;

            $taxableSubtotal = DecimalMath::add($taxableSubtotal, $taxableBase, $decimals);
            $taxTotal = DecimalMath::add($taxTotal, $tax, $decimals);
        }

        // Aggregates
        $discountTotal = DecimalMath::add($lineDiscountTotal, $invoiceDiscount, $decimals);
        if ($pricingMode === 'inclusive') {
            $total = $taxableSubtotal;
            $netSalesExcludingTax = DecimalMath::sub($taxableSubtotal, $taxTotal, $decimals);
        } else {
            $total = DecimalMath::add($taxableSubtotal, $taxTotal, $decimals);
            $netSalesExcludingTax = $taxableSubtotal;
        }

        return [
            'lines' => $computedLines,
            'pricing_mode' => $pricingMode,
            'currency_decimals' => $decimals,
            'gross_subtotal' => $grossSubtotal,
            'line_discount_total' => $lineDiscountTotal,
            'net_subtotal' => $netSubtotal,
            'invoice_discount_type' => $discountType,
            'invoice_discount_value' => DecimalMath::round($discountValue, $discountType === 'percent' ? 4 : $decimals),
            'invoice_discount_total' => $invoiceDiscount,
            'discount_total' => $discountTotal,
            'taxable_subtotal' => $taxableSubtotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'net_sales_excluding_tax' => $netSalesExcludingTax,
        ];
    }
}
