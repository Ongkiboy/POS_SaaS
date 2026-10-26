<?php

test('lingkungan test berjalan', function () {
    expect(PHP_VERSION_ID)->toBeGreaterThanOrEqual(80400);
});

arch('tidak ada dd/dump tertinggal')
    ->expect(['dd', 'dump'])
    ->not->toBeUsed();