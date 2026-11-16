<?php

namespace App\Providers;

use App\Support\Contracts\Module;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
        foreach (config('modules.enabled', []) as $class) {
            $module = $this->app->make($class);

            if (! $module instanceof Module) {
                throw new InvalidArgumentException($class.' harus mengimplementasikan '.Module::class);
            }

            foreach ($module->policies() as $model => $policy) {
                Gate::policy($model, $policy);
            }

            foreach ($module->events() as $event => $listeners) {
                foreach ($listeners as $listener) {
                    Event::listen($event, $listener);
                }
            }

            if ($path = $module->routes()) {
                Route::prefix('api/v1')->middleware('api')->group($path);
            }
        }
    }
}
