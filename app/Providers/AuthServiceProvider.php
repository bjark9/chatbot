<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use app\Models\User;

class AuthServiceProvider extends ServiceProvider{

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('use-ask-feature', function (User $user) {
        return $user->is_admin;
        });
    }

}

?>