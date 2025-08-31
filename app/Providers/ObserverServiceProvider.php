<?php

namespace App\Providers;

use App\Models\Awards\Award;
use App\Models\Awards\Nomination;
use App\Models\Awards\Season;
use App\Models\Awards\Vote;
use App\Models\Committee\Role;
use App\Models\Elections\Election;
use App\Models\Elections\Nomination as ElectionNomination;
use App\Models\Equipment\Breakage;
use App\Models\Events\Crew;
use App\Models\Events\Email;
use App\Models\Events\Event;
use App\Models\Events\Time;
use App\Models\Page;
use App\Models\Quote;
use App\Models\Resources\Category;
use App\Models\Resources\Issue;
use App\Models\Resources\Resource;
use App\Models\Resources\Tag;
use App\Models\Training\Category as TrainingCategory;
use App\Models\Training\Skills\AwardedSkill;
use App\Models\Training\Skills\Application;
use App\Models\Training\Skills\Skill;
use App\Models\Users\Group;
use App\Models\Users\User;
use App\Observers\Awards\AwardObserver;
use App\Observers\Awards\NominationObserver;
use App\Observers\Awards\SeasonObserver;
use App\Observers\Awards\VoteObserver;
use App\Observers\Committee\RoleObserver;
use App\Observers\Elections\ElectionObserver;
use App\Observers\Elections\NominationObserver as ElectionNominationObserver;
use App\Observers\Equipment\BreakageObserver;
use App\Observers\Events\CrewObserver;
use App\Observers\Events\EmailObserver;
use App\Observers\Events\EventObserver;
use App\Observers\Events\TimeObserver;
use App\Observers\PageObserver;
use App\Observers\QuoteObserver;
use App\Observers\Resources\CategoryObserver;
use App\Observers\Resources\IssueObserver;
use App\Observers\Resources\ResourceObserver;
use App\Observers\Resources\TagObserver;
use App\Observers\Training\CategoryObserver as TrainingCategoryObserver;
use App\Observers\Training\Skills\AwardedSkillObserver;
use App\Observers\Training\Skills\ApplicationObserver;
use App\Observers\Training\Skills\SkillObserver;
use App\Observers\Users\GroupObserver;
use App\Observers\Users\UserObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->observeAwards();
        $this->observeCommittee();
        $this->observeElections();
        $this->observeEquipment();
        $this->observeEvents();
        $this->observeResources();
        $this->observeTraining();
        $this->observeUsers();

        Page::observe(PageObserver::class);
        Quote::observe(QuoteObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    private function observeAwards()
    {
        Award::observe(AwardObserver::class);
        Nomination::observe(NominationObserver::class);
        Season::observe(SeasonObserver::class);
        Vote::observe(VoteObserver::class);
    }

    private function observeCommittee()
    {
        Role::observe(RoleObserver::class);
    }

    private function observeElections()
    {
        Election::observe(ElectionObserver::class);
        ElectionNomination::observe(ElectionNominationObserver::class);
    }

    private function observeEquipment()
    {
        Breakage::observe(BreakageObserver::class);
    }

    private function observeEvents()
    {
        Crew::observe(CrewObserver::class);
        Email::observe(EmailObserver::class);
        Event::observe(EventObserver::class);
        Time::observe(TimeObserver::class);
    }

    private function observeResources()
    {
        Category::observe(CategoryObserver::class);
        Issue::observe(IssueObserver::class);
        Resource::observe(ResourceObserver::class);
        Tag::observe(TagObserver::class);
    }

    private function observeTraining()
    {
        TrainingCategory::observe(TrainingCategoryObserver::class);
        AwardedSkill::observe(AwardedSkillObserver::class);
        Application::observe(ApplicationObserver::class);
        Skill::observe(SkillObserver::class);
    }

    private function observeUsers()
    {
        Group::observe(GroupObserver::class);
        User::observe(UserObserver::class);
    }
}
