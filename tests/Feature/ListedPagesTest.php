<?php

namespace Tests\Feature;

use App\Models\TourType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The pages on the client's site map: Self Drive, Chauffeur, Coach Tours, FAQ, Testimonials, About (team). */
class ListedPagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_each_tour_type_has_its_own_landing_page(): void
    {
        foreach (['self-drive', 'chauffeur-driven', 'coach-tours'] as $slug) {
            $type = TourType::where('slug', $slug)->firstOrFail();
            $this->get(route('tour-types.show', $type))->assertOk()
                ->assertSee('<h1', false)->assertSee($type->name)
                ->assertSee('rel="canonical" href="'.route('tour-types.show', $type).'"', false);
        }
    }

    public function test_inactive_or_missing_tour_type_is_404(): void
    {
        $this->get('/tours/nope')->assertNotFound();
        TourType::where('slug', 'coach-tours')->update(['is_active' => false]);
        $this->get('/tours/coach-tours')->assertNotFound();
    }

    public function test_faq_page_lists_questions_and_has_faq_schema(): void
    {
        $this->get('/faq')->assertOk()->assertSee('Frequently asked questions')->assertSee('"@type":"FAQPage"', false)->assertSee('BreadcrumbList', false);
    }

    public function test_faq_items_form_an_exclusive_accordion(): void
    {
        $html = $this->get('/faq')->assertOk()->getContent();
        preg_match_all('/<details name="([^"]+)"/', $html, $m);
        $this->assertGreaterThan(1, count($m[1]), 'FAQ items are <details> with a group name');
        $this->assertCount(1, array_unique($m[1]), 'all items of one list share one group, so only one opens at a time');
        $this->assertStringContainsString('toggle', file_get_contents(base_path('resources/js/app.js')), 'script fallback for older browsers');
    }

    public function test_testimonials_page_renders(): void
    {
        $this->get('/testimonials')->assertOk()->assertSee('What our travelers say')->assertSee('Sample review');
    }

    public function test_about_page_has_meet_the_team(): void
    {
        $this->get('/about')->assertOk()->assertSee('Meet the')->assertSee('[CONFIRM: team member 1 name]');
    }

    public function test_sitemap_and_menus_link_the_new_pages(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        foreach (['/faq', '/testimonials', '/tours/self-drive', '/tours/chauffeur-driven', '/tours/coach-tours'] as $path) {
            $this->assertStringContainsString($path, $sitemap);
        }
        $this->get('/')->assertSee(route('tour-types.show', 'self-drive'), false)->assertSee(route('faq'), false)->assertSee(route('testimonials'), false);
    }
}
