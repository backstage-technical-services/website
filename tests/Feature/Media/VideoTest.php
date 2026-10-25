<?php

namespace Tests\Feature\Media\Video;

use function Pest\Laravel\get;

/*
 * The videos pages list the BTS YouTube channel's videos, fetched from the YouTube API on every view.
 */

describe('the list of videos', function () {
    it('should be open to everyone', function (string $group) {
        fakeYoutube()->addVideo('abc');
        loginAs($group);

        get(route('media.videos.index'))->assertOk();
    })->with(['guest', 'staff', 'member', 'committee', 'super_admin']);

    it('should list the channel\'s 10 latest videos', function () {
        foreach (range(1, 11) as $i) {
            fakeYoutube()->addVideo("video{$i}", ['title' => "Video {$i}"]);
        }

        get(route('media.videos.index'))
            ->assertOk()
            ->assertSeeTextInOrder(['Video 11', 'Video 10', 'Video 2'])
            ->assertDontSeeText('Video 1 ')
            ->assertSee('background-image:url(https://i.ytimg.com/vi/video11/hqdefault.jpg);', false)
            ->assertSee('href="' . route('media.videos.show', ['id' => 'video11']) . '"', false);

        expect(fakeYoutube()->searches)->toBe([
            [
                'type' => 'video',
                'channelId' => 'UCz6b0CQClaUH9poF83PNRCw',
                'part' => 'id, snippet',
                'maxResults' => 10,
                'order' => 'date',
            ],
        ]);
    });

    it('should fail when the channel has no videos', function () {
        get(route('media.videos.index'))->assertServerError();
    })->group('known-bug', 'bug:videos-errors');

    it('should fail when the YouTube API fails', function () {
        fakeYoutube()->addVideo('abc');
        fakeYoutube()->failWith();

        get(route('media.videos.index'))->assertServerError();
    })->group('known-bug', 'bug:videos-errors');
});

describe('watching a video', function () {
    it('should embed the video', function () {
        fakeYoutube()->addVideo('abc123', ['title' => 'Freshers Gig 2026']);

        get(route('media.videos.show', ['id' => 'abc123']))
            ->assertOk()
            ->assertSeeText('Freshers Gig 2026')
            ->assertSee('<iframe src="//www.youtube.com/embed/abc123?rel=0&amp;hd=1"', false);
    });

    it('should give a 404 for an unknown video', function () {
        get(route('media.videos.show', ['id' => 'nope']))->assertNotFound();
    });
});

it('should send the images page to Instagram', function () {
    get('/media/images')->assertRedirect('https://www.instagram.com/backstageatbath/');
});
