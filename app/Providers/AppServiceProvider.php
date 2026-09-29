<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | API Rate Limit
        |--------------------------------------------------------------------------
        */

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by(
                    $request->user()?->id
                    ?: $request->ip()
                );
        });

        /*
        |--------------------------------------------------------------------------
        | Authentication Rate Limit
        |--------------------------------------------------------------------------
        */

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip());
        });

        /*
        |--------------------------------------------------------------------------
        | Forgot Password Rate Limit
        |--------------------------------------------------------------------------
        */

        RateLimiter::for(
            'forgot-password',
            function (Request $request) {
                $email = Str::lower(
                    (string) $request->input('email')
                );

                return [
                    Limit::perMinute(3)
                        ->by(
                            $email . '|' . $request->ip()
                        ),

                    Limit::perMinute(10)
                        ->by($request->ip()),
                ];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Reset Password Rate Limit
        |--------------------------------------------------------------------------
        */

        RateLimiter::for(
            'reset-password',
            function (Request $request) {
                return Limit::perMinute(5)
                    ->by($request->ip());
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Password Reset URL
        |--------------------------------------------------------------------------
        */

        ResetPassword::createUrlUsing(
            function (
                User $user,
                string $token
            ): string {
                $query = http_build_query([
                    'token' => $token,
                    'email' => $user->email,
                ]);

                return sprintf(
                    '%s/reset-password?%s',
                    rtrim(
                        config('app.frontend_url'),
                        '/'
                    ),
                    $query
                );
            }
        );
    }
}