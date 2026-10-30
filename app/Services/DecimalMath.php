<?php

namespace App\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class DecimalMath
{
    public static function of(mixed $value): BigDecimal
    {
        if ($value instanceof BigDecimal) {
            return $value;
        }

        if ($value === null || $value === '') {
            return BigDecimal::zero();
        }

        $str = (string) $value;
        // Clean any whitespace
        $str = trim($str);

        return BigDecimal::of($str);
    }

    public static function round(mixed $value, int $scale = 2): string
    {
        return (string) self::of($value)->toScale($scale, RoundingMode::HalfUp);
    }

    public static function add(mixed $a, mixed $b, int $scale = 2): string
    {
        return (string) self::of($a)->plus(self::of($b))->toScale($scale, RoundingMode::HalfUp);
    }

    public static function sub(mixed $a, mixed $b, int $scale = 2): string
    {
        return (string) self::of($a)->minus(self::of($b))->toScale($scale, RoundingMode::HalfUp);
    }

    public static function mul(mixed $a, mixed $b, int $scale = 2): string
    {
        return (string) self::of($a)->multipliedBy(self::of($b))->toScale($scale, RoundingMode::HalfUp);
    }

    public static function div(mixed $a, mixed $b, int $scale = 2): string
    {
        $divisor = self::of($b);
        if ($divisor->isZero()) {
            return '0.00';
        }

        return (string) self::of($a)->dividedBy($divisor, $scale, RoundingMode::HalfUp);
    }

    public static function comp(mixed $a, mixed $b): int
    {
        return self::of($a)->compareTo(self::of($b));
    }

    public static function isZero(mixed $value): bool
    {
        return self::of($value)->isZero();
    }

    public static function isPositive(mixed $value): bool
    {
        return self::of($value)->isPositive();
    }

    public static function isNegative(mixed $value): bool
    {
        return self::of($value)->isNegative();
    }

    public static function min(mixed $a, mixed $b, int $scale = 2): string
    {
        $cmp = self::comp($a, $b);
        $chosen = ($cmp <= 0) ? $a : $b;
        return self::round($chosen, $scale);
    }

    public static function max(mixed $a, mixed $b, int $scale = 2): string
    {
        $cmp = self::comp($a, $b);
        $chosen = ($cmp >= 0) ? $a : $b;
        return self::round($chosen, $scale);
    }

    public static function format(mixed $value, int $decimals = 2, string $symbol = '$'): string
    {
        $rounded = self::round($value, $decimals);
        $parts = explode('.', $rounded);
        $integerPart = number_format((int) $parts[0]);
        
        if ($decimals > 0) {
            $decimalPart = isset($parts[1]) ? str_pad($parts[1], $decimals, '0', STR_PAD_RIGHT) : str_repeat('0', $decimals);
            return $symbol . $integerPart . '.' . $decimalPart;
        }

        return $symbol . $integerPart;
    }
}
