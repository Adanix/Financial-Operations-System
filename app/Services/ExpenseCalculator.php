<?php

namespace App\Services;

class ExpenseCalculator
{
    /**
     * Calculate financial amounts for an expense.
     *
     * @param mixed $enteredAmount
     * @param mixed $taxRate Percentage (0-100)
     * @param string $pricingMode 'exclusive' | 'inclusive'
     * @param bool $taxRecoverable
     * @param int $decimals
     * @return array
     */
    public static function calculate(
        mixed $enteredAmount,
        mixed $taxRate = 0,
        string $pricingMode = 'exclusive',
        bool $taxRecoverable = false,
        int $decimals = 2
    ): array {
        $amount = DecimalMath::round($enteredAmount, $decimals);
        $rate = DecimalMath::round($taxRate, 4);

        if ($pricingMode === 'inclusive') {
            $gross = $amount;
            if (DecimalMath::isZero($rate)) {
                $tax = '0.00';
            } else {
                // tax = gross * r / (100 + r)
                $denom = DecimalMath::add('100', $rate, 4);
                $unroundedTax = DecimalMath::div(DecimalMath::mul($gross, $rate, 6), $denom, 6);
                $tax = DecimalMath::round($unroundedTax, $decimals);
            }
            $net = DecimalMath::sub($gross, $tax, $decimals);
        } else {
            // exclusive
            $net = $amount;
            $unroundedTax = DecimalMath::div(DecimalMath::mul($net, $rate, 6), '100', 6);
            $tax = DecimalMath::round($unroundedTax, $decimals);
            $gross = DecimalMath::add($net, $tax, $decimals);
        }

        $expenseForPnl = $taxRecoverable ? $net : $gross;
        $recoverableInputTax = $taxRecoverable ? $tax : '0.00';

        return [
            'entered_amount' => $amount,
            'tax_rate' => $rate,
            'pricing_mode' => $pricingMode,
            'tax_recoverable' => $taxRecoverable,
            'net_amount' => $net,
            'tax_amount' => $tax,
            'gross_amount' => $gross,
            'expense_for_pnl' => $expenseForPnl,
            'recoverable_input_tax' => $recoverableInputTax,
        ];
    }
}
