<?php

/*
 * Satuan global dan faktor konversi baku (ERD §3.2, BR-046).
 * Baris ingredient_unit_conversions dibuat per bahan dari daftar ini (BE-ING-001).
 * [KEPUTUSAN OI-26]
 */
return [
    'units' => [
        'g' => 'Gram',
        'kg' => 'Kilogram',
        'ml' => 'Mililiter',
        'L' => 'Liter',
        'pcs' => 'Pcs',
    ],

    // Base unit yang boleh dipakai ingredients.base_unit_id (ERD §3.2).
    'base_units' => ['g', 'ml', 'pcs'],

    // Faktor ke base unit. String agar tidak menjadi float.
    'conversions' => [
        'kg' => ['base' => 'g', 'factor' => '1000'],
        'L' => ['base' => 'ml', 'factor' => '1000'],
    ],
];
