<?php

namespace App\Support;

use InvalidArgumentException;
use OverflowException;

/**
 * Uang = int rupiah (ADR-004, ERD §7.3). Tanpa float.
 * Semua pembulatan half-up: pecahan 0,5 dibulatkan menjauhi nol (12,5 → 13; −12,5 → −13).
 */
final class Money
{
    private function __construct() {}

    /**
     * Bulatkan angka desimal ke rupiah terdekat (half-up). Input string supaya tidak lewat float.
     * Contoh: "12.5" → 13, "12.49" → 12, "0.5" → 1, "0" → 0, "-12.5" → -13.
     */
    public static function halfUp(int|string $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (preg_match('/^(-)?(\d+)(?:\.(\d+))?$/', $value, $m) !== 1) {
            throw new InvalidArgumentException("Angka tidak valid: {$value}");
        }

        $whole = self::digitsToInt($m[2]);
        $fraction = $m[3] ?? '';
        $result = ($fraction !== '' && $fraction[0] >= '5') ? self::add($whole, 1) : $whole;

        return $m[1] === '-' ? -$result : $result;
    }

    /**
     * numerator ÷ denominator, dibulatkan half-up. Dasar semua pembagian uang.
     */
    public static function halfUpDiv(int $numerator, int $denominator): int
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException('Pembagi harus > 0.');
        }

        $absolute = abs($numerator);
        $quotient = intdiv($absolute, $denominator);
        $remainder = $absolute % $denominator;

        if ($remainder * 2 >= $denominator) {
            $quotient++;
        }

        return $numerator < 0 ? -$quotient : $quotient;
    }

    /**
     * Persentase dari nominal, half-up. Persen berupa string maks 2 desimal (kolom DECIMAL(5,2)).
     * Contoh: percentOf(54000, "5.00") = 2700; percentOf(10010, "5") = 501 (500,5 → 501).
     */
    public static function percentOf(int $amount, string $percent): int
    {
        return self::halfUpDiv(self::multiply($amount, self::hundredths($percent)), 10000);
    }

    /**
     * Bulatkan ke kelipatan increment (half-up). Increment 0 = tanpa pembulatan (BR-036).
     * Contoh: (24350, 100) → 24400; (24340, 100) → 24300; (62370, 0) → 62370.
     */
    public static function roundToIncrement(int $amount, int $increment): int
    {
        if ($increment === 0) {
            return $amount;
        }

        if ($increment < 0) {
            throw new InvalidArgumentException('Increment pembulatan tidak boleh negatif.');
        }

        return self::multiply(self::halfUpDiv($amount, $increment), $increment);
    }

    /**
     * Angka non-negatif dengan maks 2 desimal → seperseratus (int). "10" → 1000, "12.5" → 1250.
     */
    public static function hundredths(string $value): int
    {
        if (preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $value, $m) !== 1) {
            throw new InvalidArgumentException("Nilai harus angka ≥ 0 dengan maks 2 desimal: {$value}");
        }

        return self::add(self::multiply(self::digitsToInt($m[1]), 100), (int) str_pad($m[2] ?? '', 2, '0'));
    }

    public static function add(int $a, int $b): int
    {
        return self::guard($a + $b);
    }

    public static function multiply(int $a, int $b): int
    {
        return self::guard($a * $b);
    }

    /**
     * PHP diam-diam mengubah int yang meluap menjadi float. Tolak, jangan dibiarkan.
     */
    private static function guard(int|float $result): int
    {
        if (! is_int($result)) {
            throw new OverflowException('Nilai melebihi batas BIGINT.');
        }

        return $result;
    }

    private static function digitsToInt(string $digits): int
    {
        $digits = ltrim($digits, '0');

        if (strlen($digits) > 18) {
            throw new OverflowException('Nilai melebihi batas BIGINT.');
        }

        return (int) $digits;
    }
}
