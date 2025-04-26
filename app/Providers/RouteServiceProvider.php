<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Add custom route model binding resolver
        Route::bind('product', function ($value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('Invalid ID format. Please provide a valid numeric ID.');
            }
            
            $product = Product::find($value);
            
            if (!$product) {
                throw new ModelNotFoundException('Product not found');
            }
            
            return $product;
        });

        // Add patterns for model binding parameters
        Route::pattern('product', '[0-9]+');
        Route::pattern('category', '[0-9]+');
        Route::pattern('order', '[0-9]+');

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
} 