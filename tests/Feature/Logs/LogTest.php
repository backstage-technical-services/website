<?php

namespace Tests\Feature\Logs\Log;

use App\Models\Log;
use Database\Factories\LogFactory;

use function Pest\Laravel\get;

/*
 * The audit log, which the model observers and some controllers write to (see App\Logger). Only admins can read it.
 */

describe('the log', function () {
    it('should only be for admins', function (string $group, string|int $access) {
        loginAs($group);

        expectAccess(get(route('logs.index')), $access);
    })->with([['guest', 'login'], ['staff', 403], ['member', 403], ['committee', 200], ['super_admin', 200]]);

    it('should show who did what, when and from where', function () {
        $user = createUser('member', ['forename' => 'Lou', 'surname' => 'Logger', 'username' => 'll123']);
        LogFactory::new()
            ->payload(['name' => 'Panto', 'crew' => ['id' => 4]])
            ->createOne([
                'user_id' => $user->id,
                'action' => 'event.create',
                'ip_address' => '10.0.0.1',
                'created_at' => '2026-10-20 18:30:15',
            ]);
        loginAs('committee');

        get(route('logs.index'))
            ->assertOk()
            ->assertSeeInOrder(
                [
                    'title="Success"',
                    '20/10/2026',
                    '6:30:15 pm',
                    'Lou Logger',
                    'll123',
                    'event.create',
                    '<code>name:</code> Panto',
                    '<code>crew:</code> {&quot;id&quot;:4}',
                    '10.0.0.1',
                ],
                false,
            );
    });

    it('should show guests, failures and entries without details', function () {
        LogFactory::new()->createOne(['action' => 'auth.failed', 'status' => false]);
        loginAs('committee');

        get(route('logs.index'))
            ->assertOk()
            ->assertSee('title="Failed"', false)
            ->assertSee('<em>Guest</em>', false)
            ->assertSee('<em>&ndash; none &ndash;</em>', false);
    });

    it('should show details that aren\'t a list as they are', function () {
        LogFactory::new()->createOne(['action' => 'custom', 'payload' => json_encode('just a string')]);
        loginAs('committee');

        get(route('logs.index'))->assertOk()->assertSeeText('just a string');
    });

    it('should show the newest entries first, 100 to a page', function () {
        LogFactory::new()->createOne(['action' => 'oldest.entry', 'created_at' => '2020-01-01 00:00:00']);
        LogFactory::new()
            ->count(100)
            ->create(['action' => 'newer.entry', 'created_at' => '2026-01-01 00:00:00']);
        loginAs('committee');
        $total = Log::count();

        $firstPage = get(route('logs.index'))->assertOk();
        $secondPage = get(route('logs.index', ['page' => 2]))->assertOk();

        expect(substr_count((string) $firstPage->getContent(), '<td col="action">'))->toBe(100);
        expect(substr_count((string) $secondPage->getContent(), '<td col="action">'))->toBe($total - 100);
        $firstPage->assertDontSee('oldest.entry');
        $secondPage->assertSee('oldest.entry');
    });

    it('should record changes made through the site', function () {
        $admin = loginAs('committee');

        get(route('logs.index'))
            ->assertOk()
            ->assertSee('<td col="action">user.create</td>', false)
            ->assertSeeText((string) $admin?->username);
    });
});
