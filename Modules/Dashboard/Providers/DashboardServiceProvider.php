<?php

namespace Modules\Dashboard\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register the views namespace for the Overview module
        $this->loadViewsFrom(base_path('Modules/Dashboard/Overview/Resources/views'), 'overview');

        Route::middleware('web')->group(base_path('Modules/Dashboard/Overview/routes/web.php'));
    }
}
