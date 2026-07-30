<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse {
                public function toResponse($request)
                {
                    $user = auth()->user();
                    $user->createToken('api-bearer-token');

                    return redirect()->route('books.index');
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
