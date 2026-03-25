<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(\App\Models\User::class, \App\Policies\RegisterUserPolicy::class);

        Relation::morphMap([
            'project' => \App\Models\Projects::class,
            'artifact' => \App\Models\Artifacts::class,
            'module' => \App\Models\Modules::class,
        ]);
    }
}
