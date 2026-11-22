<?php

use App\Support\Money;

it('halfUp: .5 dibulatkan menjauhi nol, .4 ke bawah (AC1)', function (int|string $input, int $expected) {
    expect(Money::halfUp($input))->toBe($expected);
})->with([
    'nol' => ['0', 0],
    'nol desimal' => ['0.00', 0],
    'nol koma lima' => ['0.5', 1],
    'nol koma empat sembilan' => ['0.49', 0],
    '12,4' => ['12.4', 12],
    '12,5' => ['12.5', 13],
    '12,6' => ['12.6', 13],
    'negatif 12,4' => ['-12.4', -12],
    'negatif 12,5' => ['-12.5', -13],
    'int apa adanya' => [62400, 62400],
]);

it('halfUp menolak teks bukan angka', function (string $input) {
    expect(fn () => Money::halfUp($input))->toThrow(InvalidArgumentException::class);
})->with(['kosong' => [''], 'koma' => ['12,5'], 'huruf' => ['abc'], 'spasi' => [' 1']]);

it('halfUpDiv: batas .5 positif dan negatif', function (int $num, int $den, int $expected) {
    expect(Money::halfUpDiv($num, $den))->toBe($expected);
})->with([
    'pas' => [10000, 100, 100],
    'sisa kurang setengah' => [24349, 100, 243],
    'tepat setengah' => [24350, 100, 244],
    'negatif tepat setengah' => [-24350, 100, -244],
    'nol' => [0, 100, 0],
]);

it('percentOf: persen 2 desimal, half-up', function (int $amount, string $percent, int $expected) {
    expect(Money::percentOf($amount, $percent))->toBe($expected);
})->with([
    'service 5% (vektor ACT-ORD-02)' => [54000, '5.00', 2700],
    'pajak 10%' => [56700, '10.00', 5670],
    '500,5 → 501' => [10010, '5', 501],
    'persen desimal' => [10010, '15', 1502],
    'nol persen' => [54000, '0.00', 0],
]);

it('roundToIncrement: increment 0/100/500/1000 (BR-036)', function (int $amount, int $increment, int $expected) {
    expect(Money::roundToIncrement($amount, $increment))->toBe($expected);
})->with([
    'nonaktif' => [62370, 0, 62370],
    '100 naik' => [62370, 100, 62400],
    '100 batas .5' => [24350, 100, 24400],
    '100 turun' => [24340, 100, 24300],
    '500' => [62370, 500, 62500],
    '1000' => [62370, 1000, 62000],
]);

it('hundredths menolak lebih dari 2 desimal atau negatif', function (string $input) {
    expect(fn () => Money::hundredths($input))->toThrow(InvalidArgumentException::class);
})->with(['3 desimal' => ['12.345'], 'negatif' => ['-1']]);

it('perkalian yang meluap BIGINT ditolak, tidak jadi float', function () {
    expect(fn () => Money::multiply(PHP_INT_MAX, 2))->toThrow(OverflowException::class);
});
