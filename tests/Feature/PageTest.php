<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\Page;
use Tests\TestCase;

class PageTest extends TestCase
{
    public function test_viewingListOfPagesAsAdminShouldShowTable()
    {
        $pageOne = $this->createPage(true);
        $pageTwo = $this->createPage(true);
    
        $response = $this->actingAs($this->createAdmin())
            ->get('/page');
    
        $response->assertOk()
            ->assertSee('Page Editor')
            ->assertSeeText($pageOne->title)
            ->assertSeeText("/page/$pageOne->slug")
            ->assertSeeText($pageTwo->title)
            ->assertSeeText("/page/$pageTwo->slug")
            ->assertSeeText('New Page');
    }
    
    public function test_viewingListOfPagesAsMemberShouldReturnForbidden()
    {
        $response = $this->actingAs($this->createMember())
            ->get('/page');
        
        $response->assertForbidden();
    }
    
    public function test_viewingCreatePageAsAdminShouldShowForm()
    {
        $this->markTestIncomplete();
    }
    
    public function test_viewingCreatePageAsMemberShouldReturnForbidden()
    {
        $this->markTestIncomplete();
    }
    
    public function test_creatingPageAsAdminShouldCreatePageAndReturnRedirect()
    {
        $this->markTestIncomplete();
    }
    
    public function test_creatingPageAsMemberShouldReturnForbidden()
    {
        $this->markTestIncomplete();
    }
    
    public function test_viewingUnknownPageReturnsNotFound()
    {
        $response = $this->get('/page/unknown');
        
        $this->assertShowsNotFound($response);
    }
    
    public function test_viewingAKnownPublishedPageReturnsThePage()
    {
        $page = $this->createPage(true);
        
        $response = $this->get("/page/{$page->slug}");
        
        $response->assertOk()
            ->assertSee($page->title)
            ->assertSeeText($page->content)
            ->assertDontSee(PageController::UNPUBLISHED_WARNING);
    }
    
    public function test_viewingAKnownUnpublishedPageAsAMemberReturnsNotFound()
    {
        $page = $this->createPage(false);
        
        $response = $this->actingAs($this->createMember())
            ->get("/page/{$page->slug}");
        
        $this->assertShowsNotFound($response);
    }
    
    public function test_viewingAKnownUnpublishedPageAsAnAdminReturnsThePage()
    {
        $page = $this->createPage(false);
        
        $response = $this->actingAs($this->createAdmin())
            ->get("/page/{$page->slug}");
        
        $response->assertOk()
            ->assertSee($page->title)
            ->assertSeeText($page->content)
            ->assertSee(PageController::UNPUBLISHED_WARNING);
    }
    
    public function test_viewingEditPageForKnownPageAsAdminShouldShowForm()
    {
        $this->markTestIncomplete();
    }
    
    public function test_viewingEditPageForUnknownPageAsAdminShouldShowNotFound()
    {
        $this->markTestIncomplete();
    }
    
    public function test_viewingEditPageAsMemberShouldReturnForbidden()
    {
        $this->markTestIncomplete();
    }
    
    public function test_updatingUnknownPageShouldShowNotFound()
    {
        $this->markTestIncomplete();
    }
    
    public function test_updatingKnownPageAsAdminShouldUpdatePageAndReturnRedirectToList()
    {
        $this->markTestIncomplete();
    }
    
    public function test_updatingPageAsMemberShouldReturnForbidden()
    {
        $this->markTestIncomplete();
    }
    
    public function test_deletingAKnownPageAsAdminShouldDeletePageAndRedirectToList()
    {
        $this->markTestIncomplete();
    }
    
    public function test_deletingAnUnknownPageShouldShowNotFound()
    {
        $this->markTestIncomplete();
    }
    
    public function test_deletingAPageAsAMemberShouldReturnForbidden()
    {
        $this->markTestIncomplete();
    }
    
    private function createPage(bool $published): Page
    {
        return Page::factory()
            ->for($this->createCommitteeMember(), 'author')
            ->create([
                'published' => $published
            ]);
    }
}
