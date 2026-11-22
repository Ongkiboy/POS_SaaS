<?php

namespace App\Support;

use InvalidArgumentException;
use OverflowException;
use Stringable;

/**
 * Qty stok DECIMAL(18,4) base unit (ADR-004). Disimpan sebagai int satuan 1/10.000 supaya tanpa float
 * dan tanpa ekstensi bcmath. Nilai terbesar DECIMAL(18,4) (±10^14) muat di int 64-bit.
 * Input lebih dari 4 desimal DITOLAK, tidak dibulatkan diam-diam.
 */
final readonly class Quantity implements Stringable
{
    public const SCALE = 4;

    private const FACTOR = 10000;

    private function __construct(private int $units) {}

    /**
     * "150", "150.5", "-12.0000" atau int (dibaca sebagai bilangan bulat).
     */
    public static function of(int|string $value): self
    {
        if (is_int($value)) {
            return new self(Money::multiply($value, self::FACTOR));
        }

        if (preg_match('/^(-)?(\d+)(?:\.(\d{1,4}))?$/', $value, $m) !== 1) {
            throw new InvalidArgumentException("Qty harus angka dengan maks 4 desimal: {$value}");
        }

        $whole = ltrim($m[2], '0');

        if (strlen($whole) > 14) {
            throw new OverflowException('Qty melebihi DECIMAL(18,4).');
        }

        $units = Money::add(Money::multiply((int) $whole, self::FACTOR), (int) str_pad($m[3] ?? '', self::SCALE, '0'));

        return new self($m[1] === '-' ? -$units : $units);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self(Money::add($this->units, $other->units));
    }

    public function sub(self $other): self
    {
        return new self(Money::add($this->units, -$other->units));
    }

    public function negate(): self
    {
        return new self(-$this->units);
    }

    /** Kali bilangan bulat (mis. qty recipe × qty order). Hasil eksak. */
    public function multiplyInt(int $factor): self
    {
        return new self(Money::multiply($this->units, $factor));
    }

    /** Kali qty lain (mis. qty beli × faktor konversi). Dibulatkan half-up ke 4 desimal. */
    public function multiply(self $other): self
    {
        return new self(Money::halfUpDiv(Money::multiply($this->units, $other->units), self::FACTOR));
    }

    public function isZero(): bool
    {
        return $this->units === 0;
    }

    public function isNegative(): bool
    {
        return $this->units < 0;
    }

    public function isPositive(): bool
    {
        return $this->units > 0;
    }

    /** -1, 0, atau 1. */
    public function compare(self $other): int
    {
        return $this->units <=> $other->units;
    }

    public function equals(self $other): bool
    {
        return $this->units === $other->units;
    }

    /** Selalu 4 desimal, sama dengan yang dikembalikan PostgreSQL: "150.0000". */
    public function toString(): string
    {
        $absolute = abs($this->units);
        $sign = $this->units < 0 ? '-' : '';

        return $sign.intdiv($absolute, self::FACTOR).'.'.str_pad((string) ($absolute % self::FACTOR), self::SCALE, '0', STR_PAD_LEFT);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
