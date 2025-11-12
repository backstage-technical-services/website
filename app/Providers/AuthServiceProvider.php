<?php

namespace App\Providers;

use App\Auth\UserProvider;
use App\Models\Awards\Award;
use App\Models\Awards\Nomination as AwardNomination;
use App\Models\Awards\Season as AwardSeason;
use App\Models\Committee\Role;
use App\Models\Elections\Election;
use App\Models\Elections\Nomination as ElectionNomination;
use App\Models\Equipment\Breakage;
use App\Models\Events\Crew as EventCrew;
use App\Models\Events\Event;
use App\Models\Events\Time as EventTime;
use App\Models\Quote;
use App\Models\Resources\Category as ResourceCategory;
use App\Models\Resources\Resource;
use App\Models\Resources\Tag as ResourceTag;
use App\Models\Training\Category as SkillCategory;
use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use App\Policies\Awards\AwardPolicy;
use App\Policies\Awards\NominationPolicy as AwardNominationPolicy;
use App\Policies\Awards\SeasonPolicy;
use App\Policies\CommitteePolicy;
use App\Policies\Elections\ElectionPolicy;
use App\Policies\Elections\NominationPolicy as ElectionNominationPolicy;
use App\Policies\Equipment\RepairPolicy;
use App\Policies\Events\EventCrewPolicy;
use App\Policies\Events\EventPolicy;
use App\Policies\Events\EventTimePolicy;
use App\Policies\Members\UserPolicy;
use App\Policies\QuotePolicy;
use App\Policies\Resources\CategoryPolicy as ResourceCategoryPolicy;
use App\Policies\Resources\ResourcePolicy;
use App\Policies\Resources\TagPolicy as ResourceTagPolicy;
use App\Policies\Training\CategoryPolicy as SkillCategoryPolicy;
use App\Policies\Training\Skills\ApplicationPolicy;
use App\Policies\Training\Skills\SkillPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Award::class => AwardPolicy::class,
        AwardSeason::class => SeasonPolicy::class,
        AwardNomination::class => AwardNominationPolicy::class,
        Breakage::class => RepairPolicy::class,
        Role::class => CommitteePolicy::class,
        Election::class => ElectionPolicy::class,
        ElectionNomination::class => ElectionNominationPolicy::class,
        Event::class => EventPolicy::class,
        EventCrew::class => EventCrewPolicy::class,
        EventTime::class => EventTimePolicy::class,
        Application::class => ApplicationPolicy::class,
        Quote::class => QuotePolicy::class,
        Resource::class => ResourcePolicy::class,
        ResourceCategory::class => ResourceCategoryPolicy::class,
        ResourceTag::class => ResourceTagPolicy::class,
        Skill::class => SkillPolicy::class,
        SkillCategory::class => SkillCategoryPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerAuthGates();

        // Tell Laravel to use the custom UserProvider
        Auth::provider('eloquent', function ($app, array $config) {
            return new UserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Register any general Gates used for authorisation.
     */
    public function registerAuthGates()
    {
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });
        Gate::define('member', function ($user) {
            return $user->isMember();
        });
    }
}
