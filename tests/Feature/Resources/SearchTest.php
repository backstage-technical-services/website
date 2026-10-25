<?php

namespace Tests\Feature\Resources\Search;

use Database\Factories\Resources\CategoryFactory;
use Database\Factories\Resources\ResourceFactory;
use Database\Factories\Resources\TagFactory;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Searching the resources library. The search uses MariaDB's FULLTEXT index in boolean mode, which only sees committed
 * rows, so these tests commit their data.
 */

/**
 * @param array<string, mixed> $query
 *
 * @return TestResponse<Response>
 */
function search(array $query): TestResponse
{
    return get(route('resource.search', $query));
}

/**
 * Get the titles of the results.
 *
 * @param TestResponse<Response> $response
 *
 * @return list<string>
 */
function resultTitles(TestResponse $response): array
{
    preg_match_all(
        '/<div class="search-result">.*?<h1><a href="[^"]*">(.*?)<\/a><\/h1>/s',
        (string) $response->getContent(),
        $titles,
    );

    return $titles[1];
}

describe('the search page', function () {
    it('should be open to everyone', function (string $group) {
        loginAs($group);

        get(route('resource.search'))->assertOk();
    })->with(['guest', 'staff', 'member', 'super_admin']);

    it('should list the categories with how many resources each user can see, and every tag', function () {
        $category = CategoryFactory::new()->createOne(['name' => 'Meeting Minutes']);
        ResourceFactory::new()->for($category)->createOne();
        ResourceFactory::new()->for($category)->access(4)->createOne();
        TagFactory::new()->createOne(['name' => 'AGM']);

        get(route('resource.search'))
            ->assertOk()
            ->assertSeeText(['Meeting Minutes', '(1)', 'AGM']);

        loginAs('member');
        get(route('resource.search'))->assertOk()->assertSeeText('(2)');
    });

    it('should turn the search form into a search URL', function () {
        post(route('resource.search.process'), [
            'query' => 'rigging category:minutes tag:agm',
            'tag' => ['plans'],
        ])->assertRedirect(
            route('resource.search', [
                'query' => 'rigging',
                'tag' => ['plans', 'agm'],
                'category' => 'minutes',
            ]),
        );
    });

    it('should go back to the search page for an empty search', function () {
        post(route('resource.search.process'), ['query' => ''])->assertRedirect(route('resource.search'));
    });
});

describe('the results', function () {
    beforeEach(function () {
        commitTestData();
    });

    it('should match any of the words, by prefix', function () {
        ResourceFactory::new()
            ->withIssue()
            ->createOne(['title' => 'Lighting rig plan', 'description' => null]);
        ResourceFactory::new()
            ->withIssue()
            ->createOne(['title' => 'Sound desk manual', 'description' => null]);
        ResourceFactory::new()
            ->withIssue()
            ->createOne(['title' => 'Stage plot', 'description' => 'For the rigging crew']);

        expect(resultTitles(search(['query' => 'light manual'])->assertOk()))->toEqualCanonicalizing([
            'Lighting rig plan',
            'Sound desk manual',
        ]);
        expect(resultTitles(search(['query' => 'rig'])))->toEqualCanonicalizing(['Lighting rig plan', 'Stage plot']);
    })->group('mariadb');

    it('should list everything in a category, by title', function () {
        $category = CategoryFactory::new()->createOne(['slug' => 'minutes']);
        ResourceFactory::new()
            ->withIssue()
            ->for($category)
            ->createOne(['title' => 'B minutes']);
        ResourceFactory::new()
            ->withIssue()
            ->for($category)
            ->createOne(['title' => 'A minutes']);
        ResourceFactory::new()
            ->withIssue()
            ->createOne(['title' => 'Not minutes']);

        expect(resultTitles(search(['category' => 'minutes'])->assertOk()))->toBe(['A minutes', 'B minutes']);
    });

    it('should only list resources with every tag', function () {
        $agm = TagFactory::new()->createOne(['slug' => 'agm']);
        $finance = TagFactory::new()->createOne(['slug' => 'finance']);
        ResourceFactory::new()
            ->withIssue()
            ->hasAttached($agm, [], 'tags')
            ->hasAttached($finance, [], 'tags')
            ->createOne(['title' => 'Both']);
        ResourceFactory::new()
            ->withIssue()
            ->hasAttached($agm, [], 'tags')
            ->createOne(['title' => 'Just AGM']);

        expect(resultTitles(search(['tag' => ['agm', 'finance']])->assertOk()))->toBe(['Both']);
        expect(resultTitles(search(['tag' => ['agm']])))->toBe(['Both', 'Just AGM']);
    });

    it('should combine the words with the category and tags', function () {
        $category = CategoryFactory::new()->createOne(['slug' => 'plans']);
        $tag = TagFactory::new()->createOne(['slug' => 'lighting']);
        ResourceFactory::new()
            ->withIssue()
            ->for($category)
            ->hasAttached($tag, [], 'tags')
            ->createOne(['title' => 'Rig plan']);
        ResourceFactory::new()
            ->withIssue()
            ->for($category)
            ->createOne(['title' => 'Rig plan without the tag']);
        ResourceFactory::new()
            ->withIssue()
            ->hasAttached($tag, [], 'tags')
            ->createOne(['title' => 'Rig plan elsewhere']);

        expect(resultTitles(search(['query' => 'rig', 'category' => 'plans', 'tag' => ['lighting']])))->toBe([
            'Rig plan',
        ]);
    })->group('mariadb');

    it('should only list the resources the user can see', function (string $group, array $titles) {
        ResourceFactory::new()
            ->withIssue()
            ->access(null)
            ->createOne(['title' => 'Plan none']);
        ResourceFactory::new()
            ->withIssue()
            ->access(1)
            ->createOne(['title' => 'Plan everyone']);
        ResourceFactory::new()
            ->withIssue()
            ->access(3)
            ->createOne(['title' => 'Plan registered']);
        ResourceFactory::new()
            ->withIssue()
            ->access(4)
            ->createOne(['title' => 'Plan members']);
        ResourceFactory::new()
            ->withIssue()
            ->access(5)
            ->createOne(['title' => 'Plan committee']);
        loginAs($group);

        expect(resultTitles(search(['query' => 'plan'])))->toEqualCanonicalizing($titles);
    })
        ->with([
            ['guest', ['Plan none', 'Plan everyone']],
            ['staff', ['Plan none', 'Plan everyone', 'Plan registered']],
            ['member', ['Plan none', 'Plan everyone', 'Plan registered', 'Plan members']],
            ['committee', ['Plan none', 'Plan everyone', 'Plan registered', 'Plan members', 'Plan committee']],
            ['super_admin', ['Plan none', 'Plan everyone', 'Plan registered']],
        ])
        ->group('mariadb');

    it('should count the results and show 20 a page', function () {
        foreach (range(1, 21) as $i) {
            ResourceFactory::new()
                ->withIssue()
                ->createOne(['title' => sprintf('Document %02d', $i)]);
        }

        search(['query' => 'document'])
            ->assertOk()
            ->assertSeeText('Showing 1 to 20 of 21 results');
        search(['query' => 'document', 'page' => 2])
            ->assertOk()
            ->assertSeeText('Showing 21 to 21 of 21 results');
    })->group('mariadb');

    it('should say when nothing matches', function () {
        search(['query' => 'nothing'])
            ->assertOk()
            ->assertSee("We couldn't find anything that matched your search.", false)
            ->assertDontSeeText('Showing');
    })->group('mariadb');

    it('should accept a query with stray boolean operators', function () {
        ResourceFactory::new()
            ->withIssue()
            ->createOne(['title' => 'Rig plan']);

        search(['query' => '"rig +-'])->assertOk();

        expect(notifications())->toBeEmpty();
    })->group('mariadb');
});
