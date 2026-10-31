<?php

use App\Support\Exceptions\InvalidStateTransitionException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

dataset('pemetaan exception', [
    'UNAUTHENTICATED' => [new AuthenticationException, 401, 'UNAUTHENTICATED'],
    'FORBIDDEN' => [new AuthorizationException, 403, 'FORBIDDEN'],
    'NOT_FOUND (model)' => [new ModelNotFoundException, 404, 'NOT_FOUND'],
    'INVALID_STATE_TRANSITION' => [new InvalidStateTransitionException, 409, 'INVALID_STATE_TRANSITION'],
    'RATE_LIMITED' => [new TooManyRequestsHttpException(60), 429, 'RATE_LIMITED'],
]);

it('memetakan exception ke envelope dan kode SDD §4.3', function (Throwable $exception, int $status, string $code) {
    Route::middleware('api')->get('/api/v1/__uji', fn () => throw $exception);

    $this->getJson('/api/v1/__uji')
        ->assertStatus($status)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', $code)
        ->assertJsonStructure(['success', 'error' => ['code', 'message']]);
})->with('pemetaan exception');

it('memetakan route yang tidak ada di /api ke NOT_FOUND', function () {
    $this->getJson('/api/v1/tidak-ada')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('memetakan validasi ke VALIDATION_ERROR dengan details per field', function () {
    Route::middleware('api')->post('/api/v1/__uji', fn (Request $request) => $request->validate(['email' => ['required']]));

    $this->postJson('/api/v1/__uji')
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR')
        ->assertJsonPath('error.message', 'Input tidak valid')
        ->assertJsonStructure(['error' => ['details' => ['email']]]);
});

it('menyembunyikan detail error tak terduga saat debug mati', function () {
    config(['app.debug' => false]);
    Route::middleware('api')->get('/api/v1/__uji', fn () => throw new RuntimeException('rahasia internal'));

    $this->getJson('/api/v1/__uji')
        ->assertStatus(500)
        ->assertJsonPath('error.code', 'INTERNAL_ERROR')
        ->assertDontSee('rahasia internal');
});
