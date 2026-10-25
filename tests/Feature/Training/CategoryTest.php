<?php

namespace Tests\Feature\Training\Category;

use Database\Factories\Training\CategoryFactory;
use Database\Factories\Training\Skills\SkillFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * Training skill categories, which admins manage in modals on /training/categories.
 */

describe('the list of categories', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('training.category.index')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should list the categories by name', function () {
        CategoryFactory::new()->createOne(['name' => 'Lighting']);
        CategoryFactory::new()->createOne(['name' => 'Audio']);
        loginAs('committee');

        get(route('training.category.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Audio', 'Lighting']);
    });
});

describe('creating a category', function () {
    it('should only let admins create categories', function (string $group, int $status) {
        loginAs($group);

        postAjax(route('training.category.store'), ['name' => 'Rigging'])->assertStatus($status);

        assertDatabaseCount('training_categories', $status === 200 ? 1 : 0);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should create the category', function () {
        loginAs('committee');

        postAjax(route('training.category.store'), ['name' => 'Rigging & <b>Flying</b>'])
            ->assertOk()
            ->assertExactJson(['response' => true]);

        assertDatabaseHas('training_categories', ['name' => 'Rigging &amp; Flying']);
        expectNotification('success', 'Category created');
    });

    it('should need a unique name', function (string $name, string $message) {
        CategoryFactory::new()->createOne(['name' => 'Lighting']);
        loginAs('committee');

        postAjax(route('training.category.store'), ['name' => $name])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name' => $message]);
    })->with([['', 'Please enter the category name'], ['Lighting', 'A category with that name already exists']]);

    it('should treat names that only differ in case as the same', function () {
        CategoryFactory::new()->createOne(['name' => 'Lighting']);
        loginAs('committee');

        postAjax(route('training.category.store'), ['name' => 'LIGHTING'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name' => 'A category with that name already exists']);
    })->group('mariadb');

    it('should only accept AJAX requests', function () {
        loginAs('committee');

        post(route('training.category.store'), ['name' => 'Rigging'])->assertNotFound();
    });
});

describe('changing a category', function () {
    it('should only let admins rename categories', function (string $group, int $status) {
        $category = CategoryFactory::new()->createOne(['name' => 'Old']);
        loginAs($group);

        postAjax(route('training.category.update', ['id' => $category->id]), ['name' => 'New'])->assertStatus($status);

        assertDatabaseHas('training_categories', ['id' => $category->id, 'name' => $status === 200 ? 'New' : 'Old']);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should rename the category', function () {
        $category = CategoryFactory::new()->createOne();
        loginAs('committee');

        postAjax(route('training.category.update', ['id' => $category->id]), ['name' => 'Renamed'])->assertOk();

        assertDatabaseHas('training_categories', ['id' => $category->id, 'name' => 'Renamed']);
        expectNotification('success', 'Category updated');
    });

    it('should let a category keep its name', function () {
        $category = CategoryFactory::new()->createOne(['name' => 'Lighting']);
        loginAs('committee');

        postAjax(route('training.category.update', ['id' => $category->id]), ['name' => 'Lighting'])->assertOk();
    });

    it('should need a unique name', function () {
        CategoryFactory::new()->createOne(['name' => 'Audio']);
        $category = CategoryFactory::new()->createOne(['name' => 'Lighting']);
        loginAs('committee');

        postAjax(route('training.category.update', ['id' => $category->id]), ['name' => 'Audio'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name' => 'A category with that name already exists']);
    });

    it('should give a 404 for an unknown category', function () {
        loginAs('committee');

        postAjax(route('training.category.update', ['id' => 999]), ['name' => 'New'])->assertNotFound();
    });
});

describe('deleting a category', function () {
    it('should only let admins delete categories', function (string $group, int $status) {
        $category = CategoryFactory::new()->createOne();
        loginAs($group);

        postAjax(route('training.category.destroy', ['id' => $category->id]))->assertStatus($status);

        $status === 200 ? assertModelMissing($category) : assertModelExists($category);
    })->with([['guest', 401], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should move the category\'s skills to Uncategorised', function () {
        $category = CategoryFactory::new()->createOne();
        $skill = SkillFactory::new()->for($category)->createOne();
        loginAs('committee');

        postAjax(route('training.category.destroy', ['id' => $category->id]))->assertOk();

        assertDatabaseHas('training_skills', ['id' => $skill->id, 'category_id' => null]);
        expectNotification('success', 'Category deleted');
    });

    it('should only accept AJAX requests', function () {
        $category = CategoryFactory::new()->createOne();
        loginAs('committee');

        post(route('training.category.destroy', ['id' => $category->id]))->assertNotFound();

        assertModelExists($category);
    });
});
