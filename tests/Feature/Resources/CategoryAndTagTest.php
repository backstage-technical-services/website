<?php

namespace Tests\Feature\Resources\CategoryAndTag;

use Database\Factories\Resources\CategoryFactory;
use Database\Factories\Resources\ResourceFactory;
use Database\Factories\Resources\TagFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Resource categories and tags, which admins manage in modals. They work the same way, except that categories have a
 * "flag".
 */

dataset('kinds', [
    'categories' => ['resource.category', 'resource_categories', fn() => CategoryFactory::new()],
    'tags' => ['resource.tag', 'resource_tags', fn() => TagFactory::new()],
]);

it('should only show the list to admins', function (
    string $route,
    string $table,
    callable $factory,
    string $group,
    int $status,
) {
    loginAs($group);

    get(route("{$route}.index"))->assertStatus($status);
})
    ->with('kinds')
    ->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

it('should list them by name, 20 a page', function (string $route, string $table, callable $factory) {
    foreach (range(1, 21) as $i) {
        $factory()->createOne(['name' => sprintf('Name %02d', $i), 'slug' => "name-{$i}"]);
    }
    loginAs('committee');

    get(route("{$route}.index"))
        ->assertOk()
        ->assertSeeText('Name 20')
        ->assertDontSeeText('Name 21');
    get(route("{$route}.index", ['page' => 2]))
        ->assertOk()
        ->assertSeeText('Name 21');
})->with('kinds');

it('should only let admins create them', function (
    string $route,
    string $table,
    callable $factory,
    string $group,
    int $status,
) {
    loginAs($group);

    postAjax(route("{$route}.store"), ['name' => 'Meeting Minutes'])->assertStatus($status);

    assertDatabaseCount($table, $status === 200 ? 1 : 0);
})
    ->with('kinds')
    ->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

it('should create one, making a slug from the name if there isn\'t one', function (
    string $route,
    string $table,
    callable $factory,
) {
    loginAs('committee');

    postAjax(route("{$route}.store"), ['name' => 'Meeting Minutes & Agendas'])->assertOk();
    postAjax(route("{$route}.store"), ['name' => 'Other', 'slug' => 'MY-Slug'])->assertOk();

    assertDatabaseHas($table, ['name' => 'Meeting Minutes &amp; Agendas', 'slug' => 'meeting-minutes-agendas']);
    assertDatabaseHas($table, ['name' => 'Other', 'slug' => 'my-slug']);
})->with('kinds');

it('should validate them', function (
    string $route,
    string $table,
    callable $factory,
    array $data,
    string $field,
    string $message,
) {
    $factory()->createOne(['slug' => 'taken']);
    loginAs('committee');

    postAjax(route("{$route}.store"), $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            $field => sprintf($message, $route === 'resource.category' ? 'category' : 'tag'),
        ]);
})
    ->with('kinds')
    ->with([
        [['slug' => 'a-slug'], 'name', 'Please enter the %s name'],
        [['name' => 'Name', 'slug' => 'not a slug!'], 'slug', 'The slug can only include letters, numbers and hyphens'],
        [['name' => 'Taken'], 'slug', 'That slug is already in use'],
    ]);

it('should give categories a flag', function (?string $flag, ?int $stored) {
    loginAs('committee');

    postAjax(route('resource.category.store'), ['name' => 'Risk Assessments', 'flag' => $flag])->assertOk();

    assertDatabaseHas('resource_categories', ['slug' => 'risk-assessments', 'flag' => $stored]);
})->with([['1', 1], ['0', 0], [null, null]]);

it('should reject an unknown flag', function () {
    loginAs('committee');

    postAjax(route('resource.category.store'), ['name' => 'Odd', 'flag' => '9'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['flag' => 'Please choose a valid type']);
});

it('should only let admins change them', function (
    string $route,
    string $table,
    callable $factory,
    string $group,
    int $status,
) {
    $model = $factory()->createOne(['name' => 'Old']);
    loginAs($group);

    postAjax(route("{$route}.update", ['id' => $model->getKey()]), ['name' => 'New', 'slug' => 'new'])->assertStatus(
        $status,
    );

    assertDatabaseHas($table, ['id' => $model->getKey(), 'name' => $status === 200 ? 'New' : 'Old']);
})
    ->with('kinds')
    ->with([['guest', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

it('should let one keep its slug', function (string $route, string $table, callable $factory) {
    $model = $factory()->createOne(['slug' => 'minutes']);
    loginAs('committee');

    postAjax(route("{$route}.update", ['id' => $model->getKey()]), [
        'name' => 'Renamed',
        'slug' => 'minutes',
    ])->assertOk();

    assertDatabaseHas($table, ['id' => $model->getKey(), 'name' => 'Renamed', 'slug' => 'minutes']);
})->with('kinds');

it('should only let admins delete them', function (
    string $route,
    string $table,
    callable $factory,
    string $group,
    int $status,
) {
    $model = $factory()->createOne();
    loginAs($group);

    postAjax(route("{$route}.destroy", ['id' => $model->getKey()]))->assertStatus($status);

    $status === 200 ? assertModelMissing($model) : assertModelExists($model);
})
    ->with('kinds')
    ->with([['guest', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

it('should leave a category\'s resources uncategorised when it is deleted', function () {
    $category = CategoryFactory::new()->createOne();
    $resource = ResourceFactory::new()->for($category)->createOne();
    loginAs('committee');

    postAjax(route('resource.category.destroy', ['id' => $category->id]))->assertOk();

    assertDatabaseHas('resources', ['id' => $resource->id, 'category_id' => null]);
});

it('should remove a tag from its resources when it is deleted', function () {
    $tag = TagFactory::new()->createOne();
    ResourceFactory::new()->hasAttached($tag, [], 'tags')->createOne();
    loginAs('committee');

    postAjax(route('resource.tag.destroy', ['id' => $tag->id]))->assertOk();

    assertDatabaseCount('resource_tag', 0);
});

it('should only accept AJAX requests', function (string $route, string $table, callable $factory) {
    $model = $factory()->createOne();
    loginAs('committee');

    post(route("{$route}.store"), ['name' => 'Name'])->assertNotFound();
    post(route("{$route}.update", ['id' => $model->getKey()]), ['name' => 'Name'])->assertNotFound();
    post(route("{$route}.destroy", ['id' => $model->getKey()]))->assertNotFound();
})->with('kinds');
