<?php

namespace Tests\Feature\Resources\Resource;

use App\Models\Resources\Resource;
use Database\Factories\Events\EventFactory;
use Database\Factories\Resources\CategoryFactory;
use Database\Factories\Resources\IssueFactory;
use Database\Factories\Resources\ResourceFactory;
use Database\Factories\Resources\TagFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The resources library of PDFs. Each document has numbered issues (versions) and an access level. These pages don't
 * require a login: guests get a 403 rather than a redirect to log in.
 */

function pdf(string $name = 'plan.pdf', string $contents = IssueFactory::PDF): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, $contents);
}

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function resourceForm(array $changes = []): array
{
    return array_merge(
        [
            'title' => 'Rigging plan',
            'description' => 'How to rig the *truss*',
            'type' => (string) Resource::TYPE_FILE,
            'file' => pdf(),
            'category_id' => '',
            'access' => (string) Resource::ACCESS_PUBLIC,
            'event_id' => '',
        ],
        $changes,
    );
}

function resourceWithIssue(?int $access = Resource::ACCESS_PUBLIC): Resource
{
    return ResourceFactory::new()
        ->access($access)
        ->withIssue()
        ->createOne(['title' => 'Rigging plan']);
}

describe('the admin list of resources', function () {
    it('should only be for admins', function (string $group, int $status) {
        loginAs($group);

        get(route('resource.index'))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list every resource by title, whatever its access', function () {
        ResourceFactory::new()
            ->access(Resource::ACCESS_COMMITTEE)
            ->createOne(['title' => 'Budget']);
        ResourceFactory::new()->createOne(['title' => 'Agenda']);
        loginAs('super_admin');

        get(route('resource.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Agenda', 'Budget']);
    });

    it('should filter by category or access', function (string $filter, string $shown, string $hidden) {
        $category = CategoryFactory::new()->createOne(['slug' => 'minutes']);
        ResourceFactory::new()
            ->for($category)
            ->createOne(['title' => 'In the category']);
        ResourceFactory::new()
            ->access(Resource::ACCESS_MEMBER)
            ->createOne(['title' => 'Members only']);
        loginAs('committee');

        get(route('resource.index', ['filter' => $filter]))
            ->assertOk()
            ->assertSeeText($shown)
            ->assertDontSeeText($hidden);
    })->with([
        ['category:minutes', 'In the category', 'Members only'],
        ['access:4', 'Members only', 'In the category'],
    ]);
});

describe('who can see a resource', function () {
    it('should depend on its access level', function (?int $access, string $group, int $status) {
        $resource = resourceWithIssue($access);
        loginAs($group);

        get(route('resource.view', ['id' => $resource->id]))->assertStatus($status);
        get(route('resource.stream', ['id' => $resource->id]))->assertStatus($status);
        get(route('resource.download', ['id' => $resource->id]))->assertStatus($status);
    })->with([
        'no level, guest' => [null, 'guest', 200],
        'everyone, guest' => [1, 'guest', 200],
        'registered, guest' => [3, 'guest', 403],
        'registered, staff' => [3, 'staff', 200],
        'members, staff' => [4, 'staff', 403],
        'members, member' => [4, 'member', 200],
        'members, super admin' => [4, 'super_admin', 403],
        'committee, member' => [5, 'member', 403],
        'committee, committee' => [5, 'committee', 200],
        'committee, super admin' => [5, 'super_admin', 403],
    ]);

    it('should give a 404 if the file is missing', function () {
        $resource = ResourceFactory::new()->has(IssueFactory::new(), 'issues')->createOne();

        get(route('resource.view', ['id' => $resource->id]))->assertNotFound();
        get(route('resource.download', ['id' => $resource->id]))->assertNotFound();
    });

    it('should give a 404 for an unknown resource', function () {
        get(route('resource.view', ['id' => 999]))->assertNotFound();
    });

    it('should fail for a resource without any issues', function () {
        $resource = ResourceFactory::new()->createOne();

        get(route('resource.view', ['id' => $resource->id]))->assertServerError();
    });
});

describe('viewing a resource', function () {
    it('should show the document with its details', function () {
        $author = createUser('member', ['forename' => 'Ann', 'surname' => 'Author']);
        $category = CategoryFactory::new()->createOne(['name' => 'Meeting Minutes']);
        $tag = TagFactory::new()->createOne(['name' => 'AGM']);
        $event = EventFactory::new()->createOne(['name' => 'The AGM']);
        $resource = ResourceFactory::new()
            ->for($category)
            ->for($event)
            ->hasAttached($tag, [], 'tags')
            ->has(IssueFactory::new()->for($author, 'author')->withFile(), 'issues')
            ->createOne(['title' => 'AGM Minutes']);

        get(route('resource.view', ['id' => $resource->id]))
            ->assertOk()
            ->assertSeeText(['AGM Minutes', 'Meeting Minutes', 'Issue 1 added by Ann Author', 'The AGM', 'AGM'])
            ->assertSee('src="' . route('resource.stream', ['id' => $resource->id]) . '"', false)
            ->assertSee(route('resource.download', ['id' => $resource->id]), false);
    });

    it('should show the uploader\'s name to everyone', function () {
        $author = createUser('member', ['forename' => 'Ann', 'surname' => 'Author']);
        $resource = ResourceFactory::new()
            ->has(IssueFactory::new()->for($author, 'author')->withFile(), 'issues')
            ->createOne();

        get(route('resource.view', ['id' => $resource->id]))
            ->assertOk()
            ->assertSeeText('Ann Author');
    });

    it('should only show the admin tools to admins', function (string $group, bool $shown) {
        $resource = resourceWithIssue();
        loginAs($group);

        $response = get(route('resource.view', ['id' => $resource->id]))->assertOk();
        $link = route('resource.edit', ['id' => $resource->id]);
        $shown ? $response->assertSee($link, false) : $response->assertDontSee($link, false);
    })->with([['guest', false], ['member', false], ['committee', true], ['super_admin', true]]);
});

describe('opening the file', function () {
    it('should stream the latest issue inline', function () {
        $resource = ResourceFactory::new()
            ->has(IssueFactory::new()->number(1)->withFile('issue one'), 'issues')
            ->has(IssueFactory::new()->number(2)->withFile('issue two'), 'issues')
            ->createOne(['title' => 'Rigging plan: v2/final']);

        get(route('resource.stream', ['id' => $resource->id]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="Rigging plan- v2-final.pdf"')
            ->assertHeader('Content-Length', '9')
            ->assertContent('issue two');
    });

    it('should download the latest issue', function () {
        $resource = ResourceFactory::new()
            ->has(IssueFactory::new()->number(1)->withFile('issue one'), 'issues')
            ->has(IssueFactory::new()->number(2)->withFile('issue two'), 'issues')
            ->createOne(['title' => 'Rigging plan']);

        $response = get(route('resource.download', ['id' => $resource->id]))
            ->assertOk()
            ->assertDownload('Rigging plan.pdf')
            ->assertHeader('Content-Type', 'application/pdf');

        expect($response->baseResponse)->toBeInstanceOf(BinaryFileResponse::class);
        assert($response->baseResponse instanceof BinaryFileResponse);
        expect($response->baseResponse->getFile()->getContent())->toBe('issue two');
    });

    it('should serve the latest issue when an old one is asked for', function () {
        $resource = ResourceFactory::new()
            ->has(IssueFactory::new()->number(1)->withFile('issue one'), 'issues')
            ->has(IssueFactory::new()->number(2)->withFile('issue two'), 'issues')
            ->createOne();

        get(route('resource.stream', ['id' => $resource->id, 'issue' => 1]))
            ->assertOk()
            ->assertContent('issue two');
    })->group('known-bug', 'bug:old-issues');

    it('should give a 404 for an issue that doesn\'t exist', function () {
        $resource = resourceWithIssue();

        get(route('resource.stream', ['id' => $resource->id, 'issue' => 5]))->assertNotFound();
    });
});

describe('the history', function () {
    it('should list the issues with their authors and reasons', function () {
        $author = createUser('member', ['forename' => 'Ann', 'surname' => 'Author']);
        $resource = ResourceFactory::new()
            ->has(IssueFactory::new()->number(1)->for($author, 'author')->withFile(), 'issues')
            ->has(
                IssueFactory::new()
                    ->number(2)
                    ->for($author, 'author')
                    ->withFile()
                    ->state(['reason' => 'Fixed **typos**']),
                'issues',
            )
            ->createOne();

        get(route('resource.history', ['id' => $resource->id]))
            ->assertOk()
            ->assertSeeTextInOrder(['1', 'Ann Author', 'Initial issue', '2', 'Ann Author'])
            ->assertSee('Fixed <strong>typos</strong>', false)
            ->assertSee(route('resource.stream', ['id' => $resource->id, 'issue' => 1]), false);
    });

    it('should follow the resource\'s access level', function () {
        $resource = resourceWithIssue(Resource::ACCESS_MEMBER);

        get(route('resource.history', ['id' => $resource->id]))->assertForbidden();
    });
});

describe('creating a resource', function () {
    it('should only let admins see the form', function (string $group, int $status) {
        loginAs($group);

        get(route('resource.create'))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should only let admins create resources', function (string $group, int $status) {
        loginAs($group);

        post(route('resource.store'), resourceForm())->assertStatus($status);

        assertDatabaseCount('resources', $status === 302 ? 1 : 0);
    })->with([['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should fail for guests', function () {
        post(route('resource.store'), resourceForm())->assertServerError();
    });

    it('should create the resource and its first issue', function () {
        $category = CategoryFactory::new()->createOne();
        $tags = TagFactory::new()->count(2)->create();
        $event = EventFactory::new()->createOne();
        $admin = loginAs('committee');

        $response = post(
            route('resource.store'),
            resourceForm([
                'title' => 'Rigging & flying plan',
                'category_id' => (string) $category->id,
                'tags' => $tags->pluck('id')->all(),
                'event_id' => (string) $event->id,
                'access' => (string) Resource::ACCESS_MEMBER,
                'file' => pdf(contents: 'the plan'),
            ]),
        );

        $resource = Resource::firstOrFail();
        $response->assertRedirect(route('resource.view', ['id' => $resource->id]));
        assertDatabaseHas('resources', [
            'id' => $resource->id,
            'title' => 'Rigging &amp; flying plan',
            'description' => 'How to rig the *truss*',
            'category_id' => $category->id,
            'event_id' => $event->id,
            'type' => Resource::TYPE_FILE,
            'href' => null,
            'access' => Resource::ACCESS_MEMBER,
        ]);
        expect($resource->tags)->toEqualCanonicalizing($tags->pluck('id')->all());
        assertDatabaseHas('resource_issues', [
            'resource_id' => $resource->id,
            'issue' => 1,
            'author_id' => $admin?->id,
            'reason' => 'Initial issue',
        ]);
        expect(File::get(uploadPath("resources/resources/{$resource->id}/iss01.pdf")))->toBe('the plan');
        expectNotification('success', 'Resource created');
    });

    it('should not let Google Docs be created', function () {
        loginAs('committee');

        post(
            route('resource.store'),
            resourceForm(['type' => (string) Resource::TYPE_GDOC, 'drive_id' => 'abc']),
        )->assertSessionHasErrors(['type' => 'Select a valid resource type']);
    })->group('known-bug', 'bug:gdoc-resources');

    it('should need valid tags', function () {
        loginAs('committee');

        post(route('resource.store'), resourceForm(['tags' => ['999']]))->assertSessionHasErrors([
            'tags.0' => 'Select a valid tag',
        ]);
    });

    it('should validate the resource', function (string $field, mixed $value, string $message) {
        loginAs('committee');

        from(route('resource.create'))
            ->post(route('resource.store'), resourceForm([$field => $value]))
            ->assertRedirect(route('resource.create'))
            ->assertSessionHasErrors([$field => $message]);
    })->with([
        ['title', '', "Enter the resource's name"],
        ['type', '', 'Select the resource type'],
        ['category_id', '999', 'Select a valid category'],
        ['tags', 'not-a-list', 'Select some tags'],
        ['event_id', '999', 'Select a valid event'],
        ['access', '2', 'Select a valid access type'],
    ]);

    it('should only accept a PDF up to 50MB', function (callable $file, string $message) {
        loginAs('committee');

        post(route('resource.store'), resourceForm(['file' => $file()]))->assertSessionHasErrors(['file' => $message]);
    })->with([
        'missing' => [fn() => null, 'Select a file to upload'],
        'not a PDF' => [fn() => UploadedFile::fake()->create('notes.docx', 10), 'Only PDFs are currently supported'],
        'too big' => [
            fn() => UploadedFile::fake()->create('huge.pdf', 50001, 'application/pdf'),
            'Maximum file size is 50MB',
        ],
    ]);
});

describe('editing a resource', function () {
    it('should only let admins see the form', function (string $group, int $status) {
        $resource = resourceWithIssue();
        loginAs($group);

        get(route('resource.edit', ['id' => $resource->id]))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should let super admins edit resources they can\'t see', function () {
        $resource = resourceWithIssue(Resource::ACCESS_COMMITTEE);
        loginAs('super_admin');

        get(route('resource.edit', ['id' => $resource->id]))->assertOk();
        get(route('resource.view', ['id' => $resource->id]))->assertForbidden();
    });

    it('should update the details and tags', function () {
        $old = TagFactory::new()->createOne();
        $new = TagFactory::new()->createOne();
        $resource = ResourceFactory::new()->hasAttached($old, [], 'tags')->withIssue()->createOne();
        loginAs('committee');

        post(
            route('resource.update', ['id' => $resource->id]),
            ['title' => 'New title', 'description' => '', 'access' => (string) Resource::ACCESS_REGISTERED] + [
                'tags' => [$new->id],
            ],
        )->assertRedirect(route('resource.view', ['id' => $resource->id]));

        assertDatabaseHas('resources', [
            'id' => $resource->id,
            'title' => 'New title',
            'description' => '',
            'access' => Resource::ACCESS_REGISTERED,
            'category_id' => null,
        ]);
        expect($resource->fresh()?->tags)->toBe([$new->id]);
        expectNotification('success', 'Resource saved');
    });

    it('should only let admins update resources', function (string $group, int $status) {
        $resource = resourceWithIssue();
        loginAs($group);

        post(route('resource.update', ['id' => $resource->id]), ['title' => 'New title'])->assertStatus($status);
    })->with([['staff', 403], ['member', 403], ['committee', 302]]);
});

describe('issuing a new version', function () {
    it('should only let admins see the form', function (string $group, int $status) {
        $resource = resourceWithIssue();
        loginAs($group);

        get(route('resource.issue', ['id' => $resource->id]))->assertStatus($status);
    })->with([['guest', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should add the next issue', function () {
        $resource = resourceWithIssue();
        $admin = loginAs('committee');

        post(route('resource.issue.submit', ['id' => $resource->id]), [
            'file' => pdf(contents: 'version two'),
            'reason' => 'Updated <b>numbers</b>',
        ])->assertRedirect(route('resource.view', ['id' => $resource->id]));

        assertDatabaseHas('resource_issues', [
            'resource_id' => $resource->id,
            'issue' => 2,
            'author_id' => $admin?->id,
            'reason' => 'Updated numbers',
        ]);
        expect(File::get(uploadPath("resources/resources/{$resource->id}/iss02.pdf")))->toBe('version two');
        expect(uploadPath("resources/resources/{$resource->id}/iss01.pdf"))->toBeFile();
        expectNotification('success', 'Issue submitted');
    });

    it('should validate the new issue', function (string $field, string $message) {
        $resource = resourceWithIssue();
        loginAs('committee');

        post(
            route('resource.issue.submit', ['id' => $resource->id]),
            array_merge(['file' => pdf(), 'reason' => 'Because'], [$field => null]),
        )->assertSessionHasErrors([$field => $message]);
    })->with([['file', 'Select a file to upload'], ['reason', 'Please enter a reason for the new issue']]);
});

describe('deleting a resource', function () {
    it('should only let admins delete resources', function (string $group, int $status) {
        $resource = resourceWithIssue();
        loginAs($group);

        postAjax(route('resource.destroy', ['id' => $resource->id]))->assertStatus($status);

        $status === 200 ? assertModelMissing($resource) : assertModelExists($resource);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should delete the resource, its issues and its files', function () {
        $resource = resourceWithIssue();
        loginAs('committee');

        postAjax(route('resource.destroy', ['id' => $resource->id]))
            ->assertOk()
            ->assertExactJson(['response' => 'Resource deleted']);

        assertDatabaseCount('resource_issues', 0);
        expect(uploadPath("resources/resources/{$resource->id}"))->not->toBeDirectory();
        expectNotification('success', 'Resource deleted');
    });

    it('should accept requests that aren\'t AJAX', function () {
        $resource = resourceWithIssue();
        loginAs('committee');

        post(route('resource.destroy', ['id' => $resource->id]))->assertOk();

        assertModelMissing($resource);
    });
});
