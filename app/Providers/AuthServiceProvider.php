<?php

namespace App\Providers;

use App\Policies\GeneralPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\General' => GeneralPolicy::class,
        'App\Models\User'    => UserPolicy::class,
        'App\Models\Role'    => RolePolicy::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); 

        Gate::Resource('General','App\Policies\GeneralPolicy');
        Gate::Resource('User','App\Policies\UserPolicy');
        Gate::Resource('Role','App\Policies\RolePolicy');
    }
}
