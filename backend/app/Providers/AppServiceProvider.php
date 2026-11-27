<?php

namespace App\Providers;

use App\Modules\Platform\Tenant\OutletContext;
use App\Modules\Platform\Tenant\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
        $this->app->scoped(OutletContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // maksimal 5 percobaan login per menit per email+IP.
        RateLimiter::for('login', function (Request $request): Limit {
            $email = $request->input('email');

            return Limit::perMinute(5)->by(mb_strtolower(is_string($email) ? $email : '').'|'.$request->ip());
        });
    }
}
