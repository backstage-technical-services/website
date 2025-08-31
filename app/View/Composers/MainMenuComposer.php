<?php

namespace App\View\Composers;

use App\Models\Users\User;
use Illuminate\View\View;
use Lavary\Menu\Builder;
use Lavary\Menu\Menu;

class MainMenuComposer implements ViewComposer
{
    /**
     * @var Menu
     */
    private $menu;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $isRegistered;

    /**
     * @var bool
     */
    private $isMember;

    /**
     * @var bool
     */
    private $isAdmin;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
        $this->user = auth()->user();
        $this->isRegistered = auth()->check();
        $this->isMember = $this->isRegistered && $this->user->isMember();
        $this->isAdmin = $this->isRegistered && $this->user->isAdmin();
    }

    public function compose(View $view)
    {
        $mainMenu = $this->menu->make('mainMenu', function (Builder $menu) {
            $menu->add('Home', route('home'));
            $menu->add('About Us', route('page.show', ['slug' => 'about']));
            $this->addMediaMenu($menu);
            $menu->add('The Committee', route('committee.view'));
            $this->addEventsMenu($menu);
            $this->addMembersMenu($menu);
            $this->addResourcesMenu($menu);
            $menu->add('Enquiries & Book Us', route('contact.book'))->active('contact/book');
        });

        $view->with([
            'mainMenu' => $mainMenu->asUl(['class' => 'nav navbar-nav dropdown-menu'], ['class' => 'dropdown-menu']),
        ]);
    }

    private function addMediaMenu(Builder $menu)
    {
        $mediaMenu = $menu->add('Media', '#')->active('media/*')->attr('class', 'dropdown');

        $mediaMenu->add('Image Gallery', [
            'url' => config('bts.links.instagram'),
            '_target' => 'blank',
        ]);
        $mediaMenu->add('Videos', route('media.videos.index'))->active('media/images/*');
    }

    private function addEventsMenu(Builder $menu)
    {
        $eventsMenu = $menu->add('Events Diary', route('event.diary'))->active('events/*')->attr('class', 'dropdown');

        if ($this->isMember) {
            $eventsMenu->add('Submit event report', route('event.report'))->active('events/report');
        }

        if ($this->isAdmin) {
            $eventsMenu->add('View all events', route('event.index'));
            $eventsMenu->add('Create a new event', route('event.create'));
        }
    }

    private function addMembersMenu(Builder $menu)
    {
        $membersMenu = $menu
            ->add('Members\' Area', route('auth.login'))
            ->active('members/*')
            ->attr('class', 'dropdown');

        if ($this->isRegistered) {
            $profileMenu = $membersMenu->add('My Profile', route('member.profile'))->attr('class', 'dropdown profile');

            if ($this->isMember) {
                // Profile
                $profileMenu->add('My events', route('member.profile', ['tab' => 'events']));
                $profileMenu->add('My training', route('member.profile', ['tab' => 'training']));

                // Users
                $usersMenu = $membersMenu
                    ->add('The Membership', route('membership.view'))
                    ->attr('class', 'dropdown admin-users');
                if ($this->isAdmin) {
                    $usersMenu->add('View all users', route('user.index'));
                    $usersMenu->add('Create a new user', route('user.create'));
                }

                // Quotes Board
                $membersMenu->add('Quotes Board', route('quotes.view'));

                // Wiki
                $membersMenu->add('Wiki', config('bts.links.wiki'));

                // Equipment
                $equipMenu = $membersMenu->add('Equipment', '#')->attr('class', 'dropdown equipment');
                $equipMenu->add('Asset register', route('equipment.assets'));
                $equipMenu->add('View repairs db', route('equipment.repairs.index'));
                $equipMenu->add('Report broken kit', route('equipment.repairs.create'));
                $equipMenu->add('PAT database', 'https://assets.bts-crew.com');

                // Training
                $trainingMenu = $membersMenu->add('Training', '#')->attr('class', 'dropdown training');
                $trainingMenu->add('Skills matrix', config('bts.links.skills_matrix'));

                $trainingArchiveMenu = $trainingMenu
                    ->add('Archive', '#')
                    ->attr('class', 'dropdown')
                    ->active('/training/*');
                $trainingArchiveMenu->add('View skills', route('training.skill.index'))->active('/training/skills/*');
                if ($this->isAdmin) {
                    $trainingArchiveMenu->add('View categories', route('training.category.index'));
                    $trainingArchiveMenu
                        ->add('Review applications', route('training.skill.application.index'))
                        ->active('training/applications/*');
                    $trainingArchiveMenu->add('Skills log', route('training.skill.log'));
                }

                // Misc
                $miscMenu = $membersMenu->add('Other', '#')->attr('class', 'dropdown misc')->divide();
                if ($this->isMember) {
                    $miscMenu->add('Committee elections', route('election.index'))->active('elections/*');
                    $miscMenu->add('Awards', route('award.season.index'))->active('awards/*');
                    $miscMenu->add('PC deployment portal', config('bts.links.pc_deployment'));
                    $miscMenu->add('Network management portal', config('bts.links.network_management'));
                    $miscMenu->add('Telephony portal', config('bts.links.telephony'));
                }
                if ($this->isAdmin) {
                    $miscMenu->add('Backups', route('backup.index'));
                    $miscMenu->add('Webpages', route('page.index'));
                }

                // H&S reporting
                $membersMenu->add('Report a Near Miss', route('contact.near-miss'));
                $membersMenu->add('Report an Accident', route('contact.accident'))->divide();
            }

            $membersMenu->add('Log out', route('auth.logout'));
        }
    }

    private function addResourcesMenu(Builder $menu)
    {
        $resourcesMenu = $menu
            ->add('Resources', route('resource.search'))
            ->active('resources/*')
            ->attr('class', 'dropdown');

        if ($this->isRegistered) {
            $resourcesMenu->add('Event Reports', route('resource.search', ['category' => 'event-reports']));
            $resourcesMenu->add(
                'Event Risk Assessments',
                route('resource.search', ['category' => 'event-risk-assessments']),
            );
            $resourcesMenu->add('Meeting Minutes', route('resource.search', ['category' => 'meeting-minutes']));
            $resourcesMenu->add('Meeting Agendas', route('resource.search', ['category' => 'meeting-agendas']));
        }

        $resourcesMenu->add('Links', route('page.show', ['slug' => 'links']));
        $resourcesMenu->add('FAQ', route('page.show', ['slug' => 'faq']));
    }
}
