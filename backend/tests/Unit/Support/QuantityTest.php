<?php

use App\Support\Quantity;

it('of + toString selalu 4 desimal', function (int|string $input, string $expected) {
    expect(Quantity::of($input)->toString())->toBe($expected);
})->with([
    'bulat' => ['150', '150.0000'],
    'satu desimal' => ['150.5', '150.5000'],
    'empat desimal' => ['0.0001', '0.0001'],
    'negatif' => ['-12.25', '-12.2500'],
    'int' => [3, '3.0000'],
    'nol' => ['0', '0.0000'],
]);

it('menolak lebih dari 4 desimal dan teks bukan angka (tidak dibulatkan diam-diam)', function (string $input) {
    expect(fn () => Quantity::of($input))->toThrow(InvalidArgumentException::class);
})->with(['5 desimal' => ['1.00001'], 'koma' => ['1,5'], 'kosong' => ['']]);

it('add, sub, negate eksak tanpa selisih float', function () {
    $a = Quantity::of('0.1');
    $b = Quantity::of('0.2');

    expect($a->add($b)->toString())->toBe('0.3000')
        ->and($a->sub($b)->toString())->toBe('-0.1000')
        ->and($a->negate()->toString())->toBe('-0.1000');
});

it('multiplyInt: qty recipe × qty order', function () {
    expect(Quantity::of('18.5')->multiplyInt(3)->toString())->toBe('55.5000');
});

it('multiply: qty × faktor, half-up ke 4 desimal', function () {
    expect(Quantity::of('2')->multiply(Quantity::of('1000'))->toString())->toBe('2000.0000')
        ->and(Quantity::of('0.0003')->multiply(Quantity::of('0.5'))->toString())->toBe('0.0002')   // 0,00015 → 0,0002
        ->and(Quantity::of('-0.0003')->multiply(Quantity::of('0.5'))->toString())->toBe('-0.0002');
});

it('pembanding dan tanda', function () {
    expect(Quantity::of('1')->compare(Quantity::of('2')))->toBe(-1)
        ->and(Quantity::of('2.0')->equals(Quantity::of('2')))->toBeTrue()
        ->and(Quantity::zero()->isZero())->toBeTrue()
        ->and(Quantity::of('-1')->isNegative())->toBeTrue()
        ->and(Quantity::of('1')->isPositive())->toBeTrue();
});

it('menolak nilai di luar DECIMAL(18,4)', function () {
    expect(fn () => Quantity::of('123456789012345'))->toThrow(OverflowException::class);
});
