<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Candidate;
use App\Models\ClientJobRole;
use App\Models\ClientRequirement;
use App\Models\InterviewLevel;
use App\Models\JobRole;
use App\Models\Location;
use App\Models\Mode;
use App\Models\Pages;
use App\Models\Recruiter;
use App\Policies\CandidatePolicy;
use App\Policies\ClientJobRolePolicy;
use App\Policies\ClientRequirementPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ContactEnquiryPolicy;
use App\Policies\GeneralPolicy;
use App\Policies\InterviewLevelPolicy;
use App\Policies\JobRolePolicy;
use App\Policies\LocationPolicy;
use App\Policies\ModePolicy;
use App\Policies\RecruiterPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\PagesPolicy;
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
        'App\Models\ContactEnquiry' => ContactEnquiryPolicy::class,
        'App\Models\Pages' => PagesPolicy::class,
        Client::class => ClientPolicy::class,
        InterviewLevel::class => InterviewLevelPolicy::class,
        Location::class => LocationPolicy::class,
        Recruiter::class => RecruiterPolicy::class,
        JobRole::class => JobRolePolicy::class,
        Mode::class => ModePolicy::class,
        ClientJobRole::class => ClientJobRolePolicy::class,
        ClientRequirement::class => ClientRequirementPolicy::class,
        Candidate::class => CandidatePolicy::class,
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
        Gate::Resource('ContactEnquiry','App\Policies\ContactEnquiryPolicy');
        Gate::Resource('Pages','App\Policies\PagesPolicy');
        Gate::Resource('Client','App\Policies\ClientPolicy');
        Gate::Resource('InterviewLevel','App\Policies\InterviewLevelPolicy');
        Gate::Resource('Location','App\Policies\LocationPolicy');
        Gate::Resource('Recruiter','App\Policies\RecruiterPolicy');
        Gate::Resource('JobRole','App\Policies\JobRolePolicy');
        Gate::Resource('Mode','App\Policies\ModePolicy');
        Gate::Resource('ClientJobRole','App\Policies\ClientJobRolePolicy');
        Gate::Resource('ClientRequirement','App\Policies\ClientRequirementPolicy');
        Gate::Resource('Candidate','App\Policies\CandidatePolicy');
    }
}
