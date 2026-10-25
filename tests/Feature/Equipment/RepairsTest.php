<?php

namespace Tests\Feature\Equipment\Repairs;

use App\Mail\Equipment\Breakage as BreakageEmail;
use App\Models\Equipment\Breakage;
use App\Models\Equipment\BreakageImage;
use Database\Factories\Equipment\BreakageFactory;
use Database\Factories\Equipment\BreakageImageFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/*
 * The asset register (an embedded Google Sheet) and the repairs database, where members report broken kit.
 *
 * These pages don't require a login: guests get a 403 rather than a redirect to log in.
 */

/**
 * @param array<string, mixed> $changes
 *
 * @return array<string, mixed>
 */
function breakageForm(array $changes = []): array
{
    return array_merge(
        ['name' => 'Par can', 'location' => 'Store', 'label' => 'BTS-0001', 'description' => 'The lamp has gone'],
        $changes,
    );
}

describe('the asset register', function () {
    it('should only be for members', function (string $group, int $status) {
        loginAs($group);

        get(route('equipment.assets'))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should embed the Google Sheet', function () {
        loginAs('member');

        get(route('equipment.assets'))
            ->assertOk()
            ->assertSee(
                'src="https://docs.google.com/spreadsheets/d/e/2PACX-1vQnhQT5kYi24oPgXIfBIoWEekj8TBwtBzM51kMxzxjVMS0OeBQrq9anGDaq0ifdVFB2cmSpnstQH1mO/pubhtml?widget=true&amp;headers=false"',
                false,
            );
    });
});

describe('the list of breakages', function () {
    it('should only be for members', function (string $group, int $status) {
        loginAs($group);

        get(route('equipment.repairs.index'))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should only list the open breakages, newest first', function () {
        BreakageFactory::new()->createOne(['name' => 'Older', 'created_at' => now()->subDay()]);
        BreakageFactory::new()->createOne(['name' => 'Newer']);
        BreakageFactory::new()
            ->resolved()
            ->createOne(['name' => 'Resolved']);
        BreakageFactory::new()
            ->closed()
            ->createOne(['name' => 'Closed']);
        loginAs('member');

        get(route('equipment.repairs.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Newer', 'Older'])
            ->assertDontSeeText(['Resolved', 'Closed']);
    })->group('known-bug', 'bug:closed-breakages-hidden');

    it('should show 20 breakages a page', function () {
        foreach (range(1, 21) as $i) {
            BreakageFactory::new()->createOne([
                'name' => sprintf('Breakage %02d', $i),
                'created_at' => now()->subDays(30 - $i),
            ]);
        }
        loginAs('member');

        get(route('equipment.repairs.index'))
            ->assertOk()
            ->assertSeeText('Breakage 21')
            ->assertDontSeeText('Breakage 01');
        get(route('equipment.repairs.index', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('Breakage 01');
    });
});

describe('reporting a breakage', function () {
    it('should only let members see the form', function (string $group, int $status) {
        loginAs($group);

        get(route('equipment.repairs.create'))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should only let members report breakages', function (string $group, int $status) {
        Mail::fake();
        loginAs($group);

        post(route('equipment.repairs.store'), breakageForm())->assertStatus($status);

        assertDatabaseCount('equipment_breakages', $status === 302 ? 1 : 0);
    })->with([['staff', 403], ['member', 302], ['committee', 302], ['super_admin', 403]]);

    it('should fail for guests', function () {
        post(route('equipment.repairs.store'), breakageForm())->assertServerError();

        assertDatabaseCount('equipment_breakages', 0);
    })->group('known-bug', 'bug:equipment-guests');

    it('should store the breakage and email the equipment officer', function () {
        Mail::fake();
        $member = loginAs('member', ['forename' => 'Rita', 'surname' => 'Reporter']);
        assert($member !== null);

        post(
            route('equipment.repairs.store'),
            breakageForm(['description' => 'The lamp & the <b>lens</b>']),
        )->assertRedirect(route('equipment.repairs.index'));

        $breakage = Breakage::first();
        assert($breakage instanceof Breakage);
        assertDatabaseHas('equipment_breakages', [
            'id' => $breakage->id,
            'name' => 'Par can',
            'location' => 'Store',
            'label' => 'BTS-0001',
            'description' => 'The lamp &amp; the lens',
            'comment' => '',
            'status' => Breakage::STATUS_REPORTED,
            'closed' => false,
            'user_id' => $member->id,
        ]);
        Mail::assertQueued(BreakageEmail::class, function (BreakageEmail $mail) use ($member, $breakage) {
            $mail->assertTo('equip@bts-crew.com');
            $mail->assertHasReplyTo($member->email, 'Rita Reporter');
            $mail->assertHasSubject('[Backstage Website] Equipment breakage');
            $mail->assertSeeInText('Par can');
            $mail->assertSeeInText("Rita Reporter ({$member->username})");
            $mail->assertSeeInHtml(route('equipment.repairs.view', ['id' => $breakage->id]));

            return true;
        });
        expectNotification('success', 'Breakage reported');
    });

    it('should rely on MariaDB filling in the comment', function () {
        Mail::fake();
        loginAs('member');

        post(route('equipment.repairs.store'), breakageForm())->assertRedirect();

        assertDatabaseHas('equipment_breakages', ['comment' => '']);
    })->group('mariadb');

    it('should save up to 5 photos', function () {
        Mail::fake();
        loginAs('member');

        post(
            route('equipment.repairs.store'),
            breakageForm([
                'images' => [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.png')],
            ]),
        )->assertRedirect();

        $images = BreakageImage::orderBy('id')->get();
        expect($images)->toHaveCount(2);
        expect($images->map(fn(BreakageImage $image) => [$image->mime, $image->extension])->all())->toBe([
            ['image/jpeg', 'jpg'],
            ['image/png', 'png'],
        ]);
        foreach ($images as $image) {
            expect(uploadPath("resources/breakages/{$image->id}.{$image->extension}"))->toBeFile();
        }
        Mail::assertQueued(
            BreakageEmail::class,
            fn(BreakageEmail $mail) => str_contains((string) $mail->render(), 'View 2 images on the website'),
        );
    });

    it('should validate the report', function (string $field, mixed $value, string $message) {
        loginAs('member');

        from(route('equipment.repairs.create'))
            ->post(route('equipment.repairs.store'), breakageForm([$field => $value]))
            ->assertRedirect(route('equipment.repairs.create'))
            ->assertSessionHasErrors([$field => $message]);
    })->with([
        ['name', '', 'Please enter the name of the broken equipment'],
        ['location', '', 'Please enter the current location of the equipment'],
        ['label', '', 'Please enter how the item is labelled'],
        ['description', '', 'Please enter the details of the breakage'],
    ]);

    it('should validate the photos', function (callable $images, string $field, string $message) {
        loginAs('member');

        post(route('equipment.repairs.store'), breakageForm(['images' => $images()]))->assertSessionHasErrors([
            $field => $message,
        ]);
    })->with([
        'too many' => [
            fn() => array_map(fn(int $i) => UploadedFile::fake()->image("{$i}.jpg"), range(1, 6)),
            'images',
            'Please upload no more than 5 images per report',
        ],
        'not an image' => [
            fn() => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
            'images.0',
            'One of the uploaded files could not be processed as an image. Ensure the file is not corrupt',
        ],
        'a GIF' => [
            fn() => [UploadedFile::fake()->image('animation.gif')],
            'images.0',
            'Only JPEG and PNG images are supported',
        ],
        'too big' => [
            fn() => [UploadedFile::fake()->image('huge.jpg')->size(20481)],
            'images.0',
            'Each image must be less than 20MB',
        ],
    ]);
});

describe('viewing a breakage', function () {
    it('should only be for members', function (string $group, int $status) {
        $breakage = BreakageFactory::new()->createOne();
        loginAs($group);

        get(route('equipment.repairs.view', ['id' => $breakage->id]))->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should show closed breakages to anyone with the link', function () {
        $breakage = BreakageFactory::new()
            ->closed()
            ->createOne(['name' => 'Long gone']);
        loginAs('member');

        get(route('equipment.repairs.view', ['id' => $breakage->id]))
            ->assertOk()
            ->assertSeeText('Long gone');
    });

    it('should only let admins change the status and comment', function (string $group, bool $editable) {
        $breakage = BreakageFactory::new()->createOne();
        loginAs($group);

        $response = get(route('equipment.repairs.view', ['id' => $breakage->id]))->assertOk();
        $editable
            ? $response->assertSee('name="status"', false)->assertSee('name="comment"', false)
            : $response->assertDontSee('name="status"', false)->assertSeeText('Reported');
    })->with([['member', false], ['committee', true]]);

    it('should give a 404 for an unknown breakage', function () {
        loginAs('member');

        get(route('equipment.repairs.view', ['id' => 999]))->assertNotFound();
    });
});

describe('viewing a photo', function () {
    it('should only be for members', function (string $group, int $status) {
        $image = BreakageImageFactory::new()->withFile()->createOne();
        loginAs($group);

        get(
            route('equipment.repairs.images.stream', ['id' => $image->equipment_breakage_id, 'imageId' => $image->id]),
        )->assertStatus($status);
    })->with([['guest', 403], ['staff', 403], ['member', 200], ['committee', 200], ['super_admin', 403]]);

    it('should serve the file inline with the type the browser gave when it was uploaded', function () {
        $image = BreakageImageFactory::new()
            ->withFile('the photo')
            ->createOne(['mime' => 'text/html', 'extension' => 'jpg']);
        loginAs('member');

        get(route('equipment.repairs.images.stream', ['id' => $image->equipment_breakage_id, 'imageId' => $image->id]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertHeader(
                'Content-Disposition',
                "inline; filename=\"break-{$image->equipment_breakage_id}-{$image->id}.jpg\"",
            )
            ->assertContent('the photo');
    })->group('known-bug', 'bug:breakage-photo-mime');

    it('should give a 404 for a photo of another breakage', function () {
        $image = BreakageImageFactory::new()->withFile()->createOne();
        $other = BreakageFactory::new()->createOne();
        loginAs('member');

        get(route('equipment.repairs.images.stream', ['id' => $other->id, 'imageId' => $image->id]))->assertNotFound();
    });

    it('should fail if the file is missing', function () {
        $image = BreakageImageFactory::new()->createOne();
        loginAs('member');

        get(
            route('equipment.repairs.images.stream', ['id' => $image->equipment_breakage_id, 'imageId' => $image->id]),
        )->assertServerError();
    });
});

describe('updating a breakage', function () {
    it('should only let admins update breakages', function (string $group, int $status) {
        $breakage = BreakageFactory::new()->createOne();
        loginAs($group);

        post(route('equipment.repairs.update', ['id' => $breakage->id]), [
            'action' => 'update',
            'status' => '2',
            'comment' => 'Diagnosed',
        ])->assertStatus($status);

        assertDatabaseHas('equipment_breakages', ['id' => $breakage->id, 'status' => $status === 302 ? 2 : 1]);
    })->with([['guest', 403], ['staff', 403], ['member', 403], ['committee', 302], ['super_admin', 302]]);

    it('should set the status and comment, without telling the reporter', function () {
        Mail::fake();
        $breakage = BreakageFactory::new()->createOne();
        loginAs('committee');

        post(route('equipment.repairs.update', ['id' => $breakage->id]), [
            'action' => 'update',
            'status' => '3',
            'comment' => 'Parts & labour ordered',
        ])->assertRedirect(route('equipment.repairs.view', ['id' => $breakage->id]));

        assertDatabaseHas('equipment_breakages', [
            'id' => $breakage->id,
            'status' => 3,
            'comment' => 'Parts &amp; labour ordered',
            'closed' => false,
        ]);
        expectNotification('success', 'Breakage updated');
        Mail::assertNothingQueued();
    });

    it('should close a breakage when it is resolved', function () {
        $breakage = BreakageFactory::new()->createOne();
        loginAs('committee');

        post(route('equipment.repairs.update', ['id' => $breakage->id]), [
            'action' => 'update',
            'status' => '0',
        ])->assertRedirect();

        assertDatabaseHas('equipment_breakages', ['id' => $breakage->id, 'status' => 0, 'closed' => true]);
    });

    it('should re-open a closed breakage when its status changes', function () {
        $breakage = BreakageFactory::new()->closed()->createOne();
        loginAs('committee');

        post(route('equipment.repairs.update', ['id' => $breakage->id]), [
            'action' => 'update',
            'status' => '4',
        ])->assertRedirect();

        assertDatabaseHas('equipment_breakages', ['id' => $breakage->id, 'closed' => false]);
    })->group('known-bug', 'bug:closed-breakages-hidden');

    it('should validate the status', function (string $status, string $message) {
        $breakage = BreakageFactory::new()->createOne();
        loginAs('committee');

        post(route('equipment.repairs.update', ['id' => $breakage->id]), [
            'action' => 'update',
            'status' => $status,
        ])->assertSessionHasErrors(['status' => $message]);
    })->with([['', 'Please choose a status for the breakage'], ['7', 'Please choose a valid status']]);

    it('should close and re-open a breakage', function () {
        $breakage = BreakageFactory::new()->createOne();
        loginAs('committee');

        post(route('equipment.repairs.update', ['id' => $breakage->id]), ['action' => 'close'])->assertRedirect();
        assertDatabaseHas('equipment_breakages', ['id' => $breakage->id, 'closed' => true]);
        expectNotification('success', 'Breakage closed');

        post(route('equipment.repairs.update', ['id' => $breakage->id]), ['action' => 'reopen'])->assertRedirect();
        assertDatabaseHas('equipment_breakages', ['id' => $breakage->id, 'closed' => false]);
        expectNotification('success', 'Breakage re-opened');
    });

    it('should do nothing for an unknown action', function () {
        $breakage = BreakageFactory::new()->createOne();
        loginAs('committee');

        post(route('equipment.repairs.update', ['id' => $breakage->id]), ['action' => 'delete'])->assertRedirect(
            route('equipment.repairs.view', ['id' => $breakage->id]),
        );

        assertDatabaseCount('equipment_breakages', 1);
    });
});

it('should store the photos as they were uploaded, keeping their EXIF data', function () {
    Mail::fake();
    loginAs('member');
    $photo = UploadedFile::fake()->image('photo.jpg');
    $jpeg = (string) file_get_contents($photo->getPathname());
    $exif = "Exif\0\0MM\0*\0\0\0\x08\0\0GPS 51.3782N 2.3264W";
    $withExif = substr($jpeg, 0, 2) . "\xFF\xE1" . pack('n', strlen($exif) + 2) . $exif . substr($jpeg, 2);

    post(
        route('equipment.repairs.store'),
        breakageForm(['images' => [UploadedFile::fake()->createWithContent('photo.jpg', $withExif)]]),
    )->assertRedirect();

    $image = BreakageImage::firstOrFail();
    expect(File::get(uploadPath("resources/breakages/{$image->id}.{$image->extension}")))->toBe($withExif);
})->group('known-bug', 'bug:breakage-photo-exif');
