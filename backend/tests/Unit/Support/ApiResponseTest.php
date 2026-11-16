<?php

use App\Support\Http\ApiResponse;

test('success memuat success, data, message; meta hanya bila diberikan', function () {
    $tanpaMeta = ApiResponse::success(['a' => 1])->getData(true);

    expect($tanpaMeta)->toBe(['success' => true, 'data' => ['a' => 1], 'message' => 'Berhasil']);

    $denganMeta = ApiResponse::success([], 'OK', ['page' => 1, 'per_page' => 20, 'total' => 100])->getData(true);

    expect($denganMeta['meta'])->toBe(['page' => 1, 'per_page' => 20, 'total' => 100]);
});

test('error memuat code dan message; details hanya bila diberikan', function () {
    $response = ApiResponse::error('VALIDATION_ERROR', 'Input tidak valid', 422, ['email' => ['wajib']]);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getData(true))->toBe([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Input tidak valid',
                'details' => ['email' => ['wajib']],
            ],
        ]);

    expect(ApiResponse::error('NOT_FOUND', 'x', 404)->getData(true)['error'])->not->toHaveKey('details');
});
