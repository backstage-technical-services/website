<?php

namespace App\Providers;

use App\Models\Resources\Category;
use App\Models\Resources\Resource;
use App\Models\Resources\Tag;
use App\Models\Training\Category as TrainingCategory;
use App\Models\Training\Skills\Skill;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Package\WebDevTools\Laravel\Traits\CorrectsPaginatorPath;

class ViewServiceProvider extends ServiceProvider
{
    use CorrectsPaginatorPath;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->attachMemberEvents();
        $this->attachMemberSkills();
        $this->attachResourceVariables();
        $this->attachResourceSearchVariables();
        $this->attachTrainingSkillLevels();
        $this->attachTrainingSkillCategories();
        $this->attachTrainingSkillList();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Attach the list of events for the given member.
     */
    private function attachMemberEvents()
    {
        view()->composer('members.profile.events', function ($view) {
            $user = $view->getData()['user'];

            $events = $user
                ->events()
                ->distinctPaginate(20)
                ->withPath($this->paginatorPath(['tab' => 'events']));

            $view->with([
                'events' => $events,
            ]);
        });
    }

    /**
     * Attach the list of skills.
     */
    private function attachMemberSkills()
    {
        view()->composer('members.profile.training', function ($view) {
            $view->with([
                'skill_categories' => [],
            ]);
        });
    }

    /**
     * Attach the variables for the resources section
     */
    private function attachResourceVariables()
    {
        // Access levels
        view()->composer('resources.*', function ($view) {
            $view->with([
                'AccessLevels' => Resource::ACCESS,
                'ResourceCategories' => Category::orderBy('name', 'ASC')->get(),
                'ResourceTags' => Tag::orderBy('name', 'ASC')->get(),
                'ResourceTypes' => Resource::TYPES,
            ]);
        });
    }

    /**
     * Attach the variables to the search results view.
     */
    private function attachResourceSearchVariables()
    {
        view()->composer('resources.search.results', function ($view) {
            $resources = $view->getData()['resources'];
            $search = $view->getData()['search'];

            // Result counts
            $counts = [
                'lower' => ($resources->currentPage() - 1) * $resources->perPage() + 1,
                'upper' => min($resources->currentPage() * $resources->perPage(), $resources->total()),
                'total' => $resources->total(),
            ];

            // Categories
            $categories = Category::orderBy('name', 'ASC')->get();
            $request = Request::except('page', 'category');
            $category_list = [];
            foreach ($categories as $category) {
                $category_list[] = (object) [
                    'name' => $category->name,
                    'link' => route('resource.search', $request + ['category' => $category->slug]),
                    'current' => $search->category && $search->category == $category->slug,
                ];
            }

            // Tags
            $tags = Tag::orderBy('name', 'ASC')->get();
            $request = Request::except('page', 'tag');
            $tag_list = [];
            foreach ($tags as $tag) {
                if (in_array($tag->slug, $search->tags)) {
                    $tag_param = array_filter($search->tags, function ($slug) use ($tag) {
                        return $slug != $tag->slug;
                    });
                } else {
                    $tag_param = array_merge($search->tags, [$tag->slug]);
                }

                $tag_list[] = (object) [
                    'name' => $tag->name,
                    'link' => route('resource.search', $request + ['tag' => $tag_param]),
                    'current' => in_array($tag->slug, $search->tags),
                ];
            }

            $view->with([
                'Counts' => $counts,
                'CategoryList' => $category_list,
                'TagList' => $tag_list,
            ]);
        });
    }

    /**
     * Attach the names of the training skill levels.
     *
     * @return void
     */
    private function attachTrainingSkillLevels()
    {
        view()->composer('training.skills.*', function ($view) {
            $view->with([
                'LevelNames' => Skill::LEVEL_NAMES,
            ]);
        });
    }

    /**
     * Attach the list of training skills.
     *
     * @return void
     */
    private function attachTrainingSkillList()
    {
        view()->composer(
            ['training.skills.applications.apply', 'training.skills.award', 'training.skills.revoke'],
            function ($view) {
                $view->with([
                    'SkillList' => Skill::select('training_skills.*')
                        ->leftJoin('training_categories', 'training_skills.category_id', '=', 'training_categories.id')
                        ->orderBy('training_categories.name', 'ASC')
                        ->orderBy('training_skills.name', 'ASC')
                        ->get()
                        ->groupBy(function ($skill) {
                            return $skill->isCategorised() ? $skill->category->name : 'Uncategorised';
                        })
                        ->map(function ($category) {
                            return $category->mapWithKeys(function ($skill) {
                                return [$skill->id => $skill->name];
                            });
                        })
                        ->toArray(),
                ]);
            },
        );
    }

    /**
     * Attach the training skill categories.
     *
     * @return void
     */
    private function attachTrainingSkillCategories()
    {
        view()->composer(['training.skills.index', 'members.profile.training'], function ($view) {
            $categories = TrainingCategory::orderBy('name', 'ASC')->get();
            $categories->add(
                (object) [
                    'id' => null,
                    'name' => 'Uncategorised',
                    'skills' => Skill::whereNull('category_id')->orderBy('name', 'ASC')->get(),
                ],
            );

            $view->with([
                'SkillCategories' => $categories,
            ]);
        });

        view()->composer('training.skills.form', function ($view) {
            $view->with([
                'categories' => TrainingCategory::orderBy('name', 'ASC')
                    ->pluck('name', 'id')
                    ->prepend('Uncategorised', ''),
            ]);
        });
    }
}
