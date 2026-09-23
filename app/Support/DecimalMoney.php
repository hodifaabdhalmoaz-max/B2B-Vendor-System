<?php

namespace App\Support;

use InvalidArgumentException;

final class DecimalMoney
{
    public static function toCents(string|int|float $amount): int
    {
        if (! preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', (string) $amount, $parts)) {
            throw new InvalidArgumentException('Expected a monetary amount with at most two decimal places.');
        }

        $cents = ((int) $parts[2] * 100) + (int) str_pad($parts[3] ?? '', 2, '0');

        return $parts[1] === '-' ? -$cents : $cents;
    }

    public static function formatCents(int $cents): string
    {
        $absolute = abs($cents);

        return ($cents < 0 ? '-' : '').intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }
}
