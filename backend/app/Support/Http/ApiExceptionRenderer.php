<?php

namespace App\Support\Http;

use App\Support\Exceptions\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (ApiException $e, Request $request) {
            if (! self::wantsApi($request)) {
                return null;
            }

            return ApiResponse::error($e->errorCode, $e->getMessage(), $e->httpStatus, $e->details);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! self::wantsApi($request)) {
                return null;
            }

            return ApiResponse::error('VALIDATION_ERROR', 'Input tidak valid', 422, $e->errors());
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! self::wantsApi($request)) {
                return null;
            }

            return ApiResponse::error('UNAUTHENTICATED', 'Sesi tidak valid atau sudah berakhir. Silakan login.', 401);
        });

        // Laravel mengubah AuthorizationException jadi 403 dan ModelNotFoundException jadi 404
        // sebelum callback ini dipanggil, jadi cukup memetakan berdasarkan status HTTP.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! self::wantsApi($request)) {
                return null;
            }

            return match ($e->getStatusCode()) {
                403 => ApiResponse::error('FORBIDDEN', 'Anda tidak memiliki akses untuk aksi ini.', 403),
                404 => ApiResponse::error('NOT_FOUND', 'Data tidak ditemukan.', 404),
                429 => ApiResponse::error('RATE_LIMITED', 'Terlalu banyak permintaan. Coba lagi nanti.', 429, null, $e->getHeaders()),
                default => null,
            };
        });

        // Cadangan: error tak terduga tidak boleh membocorkan detail saat debug mati.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! self::wantsApi($request) || config('app.debug')) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface || $e instanceof HttpResponseException) {
                return null;
            }

            return ApiResponse::error('INTERNAL_ERROR', 'Terjadi kesalahan pada server.', 500);
        });
    }

    private static function wantsApi(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
