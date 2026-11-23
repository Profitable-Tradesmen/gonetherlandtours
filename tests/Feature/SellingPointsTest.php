<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Page;
use App\Models\Setting;
use App\Models\TourType;
use App\Support\DefaultSections;
use App\Support\UsSpelling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** The four selling points: accurate wording, proof, a reusable strip on key pages, and US spelling for US travelers. */
class SellingPointsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(): void
    {
        $this->actingAs(AdminUser::create(['name' => 'T', 'email' => 'usp@test.local', 'password' => 'test-password-123', 'role' => 'admin']), 'admin');
    }

    public function test_home_states_the_four_selling_points(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        foreach (['Authentic Dutch Culture', 'Hidden Gems', 'Expertly Crafted Itineraries'] as $usp) {
            $this->assertStringContainsStringIgnoringCase($usp, $html, $usp);
        }
        $this->assertStringContainsString('Small Group Experiences', $html);
    }

    public function test_small_group_claim_is_accurate_only_coach_tours_are_small_group(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('private self drive and chauffeur tours or small-group coach tours', $html);
        $this->assertStringContainsString('just your own party', $html);
        $this->assertStringContainsString('Your own party, or a small guided group', $html, 'value strip: small group only on guided tours');
        $this->assertStringNotContainsString('Small groups with flexible itineraries', $html);
        $this->assertStringNotContainsString('on small-group and tailor-made tours', $html, 'old blanket claim is gone');
        $this->assertStringContainsString('Private self drive and chauffeur tours or small-group coach tours', $html, 'meta description');
    }

    public function test_each_tour_type_says_how_you_travel(): void
    {
        $this->assertSame('Small group with a guide', TourType::where('slug', 'coach-tours')->value('style_note'));
        $this->assertStringContainsString('Private: just you and your own party', TourType::where('slug', 'self-drive')->value('style_note'));

        $this->get('/tours/coach-tours')->assertOk()->assertSee('Small group with a guide');
        $this->get('/tours/self-drive')->assertOk()->assertSee('Private: just you and your own party');
        $pkg = Package::where('slug', 'sample-tulip-season-self-drive')->firstOrFail();
        $html = $this->get(route('packages.show', $pkg))->getContent();
        $this->assertStringContainsString('Travel style', $html);
        $this->assertStringContainsString('Private: just you and your own party', $html);
    }

    public function test_proof_section_on_home_has_placeholders_to_confirm_and_real_links(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('What that looks like in practice', $html);
        $this->assertSame(4, substr_count($html, 'In practice</p>'));
        $this->assertStringContainsString('Coach tours are limited to [CONFIRM: N] travelers', $html, 'facts only the business knows stay marked');
        $this->assertStringContainsString('19 windmills of Kinderdijk', $html, 'real, checkable Dutch details');
        $this->assertStringContainsString('Zierikzee', $html);
        $this->assertStringContainsString(route('blog.index'), $html);
        $this->assertStringContainsString(route('packages.index'), $html);
        // sits after the "Our promise" band
        $this->assertGreaterThan(strpos($html, 'Authentic, personal, unhurried'), strpos($html, 'What that looks like in practice'));
    }

    public function test_why_choose_us_strip_appears_on_package_tour_type_and_destination_pages(): void
    {
        $pkg = Package::where('slug', 'sample-tulip-season-self-drive')->firstOrFail();
        $dest = \App\Models\Destination::where('slug', 'tulips-and-countryside')->firstOrFail();
        foreach ([route('packages.show', $pkg), route('tour-types.show', 'self-drive'), route('destinations.show', $dest)] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('Why travel with us', $html, $url);
            $this->assertStringContainsString('Hidden gems &amp; local secrets', $html, $url);
            $this->assertStringContainsString('Expertly crafted itineraries', $html, $url);
        }
    }

    public function test_strip_wording_is_editable_in_settings_and_as_a_page_section(): void
    {
        $this->login();
        $this->get(route('admin.settings'))->assertOk()->assertSee('Why choose us')->assertSee('Point 3: title');
        Setting::put('usp_3_title', 'Secret Dutch spots');
        $pkg = Package::where('slug', 'sample-tulip-season-self-drive')->firstOrFail();
        $this->get(route('packages.show', $pkg))->assertSee('Secret Dutch spots');

        // the same strip is available to the page builder
        $page = Page::bySlug('contact');
        $this->post(route('admin.pages.sections.add', $page), ['type' => 'why_us'])->assertRedirect();
        $this->get('/contact')->assertOk()->assertSee('Secret Dutch spots');
    }

    public function test_proof_section_can_be_added_and_edited(): void
    {
        $this->login();
        $page = Page::bySlug('about');
        $this->post(route('admin.pages.sections.add', $page), ['type' => 'usp_proof'])->assertRedirect();
        $s = collect($page->fresh()->allSections())->firstWhere('type', 'usp_proof');
        $this->put(route('admin.pages.sections.update', [$page, $s['id']]), ['data' => ['heading' => 'Proof it', 'items' => [['icon' => 'star', 'title' => 'Real example', 'text' => 'Claim', 'proof' => 'We run the Keukenhof morning slot', 'link_label' => 'See it', 'link' => '/packages']]]]);
        $this->get('/about')->assertSee('Proof it')->assertSee('We run the Keukenhof morning slot')->assertSee('See it');
    }

    public function test_site_copy_uses_us_spelling(): void
    {
        foreach (['/', '/about', '/faq', '/testimonials', '/contact', '/packages', '/packages/sample-tulip-season-self-drive', '/tours/coach-tours'] as $url) {
            $html = strip_tags($this->get($url)->getContent());
            $this->assertDoesNotMatchRegularExpression('/\b(traveller|travelling|colourful|favourite|organise|cosy)/i', $html, "UK spelling on $url");
        }
        $this->assertStringContainsString('Travelers Say', $this->get('/')->getContent());
        $this->assertStringContainsString('Send My Inquiry', $this->get('/')->getContent());
    }

    public function test_legal_consent_text_is_untouched_by_the_spelling_change(): void
    {
        $this->assertStringContainsString('Terms and Conditions', consent_text());
        $this->assertSame(config('gnt.consent_template'), str_replace(company_name(), '[Company Name]', consent_text()));
    }

    public function test_us_spelling_converter_rules(): void
    {
        $this->assertSame('Our travelers are traveling to a colorful cozy center.', UsSpelling::convert('Our travellers are travelling to a colourful cosy centre.'));
        $this->assertSame('Favorite tours for Travelers', UsSpelling::convert('Favourite tours for Travellers'));
        $this->assertSame('We have your inquiry. Send my inquiry.', UsSpelling::convert('We have your enquiry. Send my enquiry.'));
        // identifiers and non-phrases stay
        $this->assertSame('$enquiry->id enquiry.store x-enquiry-form', UsSpelling::convert('$enquiry->id enquiry.store x-enquiry-form'));
        // HTML: text changes, attributes and links do not
        $this->assertSame('<p class="colour">A <a href="/travellers-guide" title="travellers">traveler</a> guide</p>', UsSpelling::convert('<p class="colour">A <a href="/travellers-guide" title="travellers">traveller</a> guide</p>'));
        $this->assertSame('/travellers/colour', UsSpelling::convert('/travellers/colour'));
        $this->assertSame(['text' => 'Great travelers', 'url' => '/travellers', 'image' => 'images/colour.webp'], UsSpelling::deep(['text' => 'Great travellers', 'url' => '/travellers', 'image' => 'images/colour.webp']));
    }

    public function test_migration_converts_existing_content_and_updates_home_wording_without_overwriting_custom_text(): void
    {
        // Simulate a site built before this release: UK spelling in content, old claims on the home page, no proof section, no style notes.
        Faq::create(['question' => 'Do travellers need a visa?', 'answer' => 'Most travellers do not. Check your enquiry details.', 'is_active' => true, 'sort_order' => 99]);
        $pkg = Package::where('slug', 'sample-tulip-season-self-drive')->firstOrFail();
        $pkg->update(['highlights' => ['Colourful fields', 'A cosy hotel'], 'itinerary' => [['title' => 'Day 1', 'body' => 'Travelling by car', 'image' => null]]]);
        Setting::put('footer_text', 'Tours for travellers who love colour.');
        TourType::query()->update(['style_note' => null]);

        $home = Page::bySlug('home');
        $sections = collect($home->allSections())->reject(fn ($s) => $s['type'] === 'usp_proof')->values()->all();
        // the previous release's home page had a ticker and a photo band (the reference layout no longer seeds them)
        array_splice($sections, 1, 0, [
            ['id' => 'oldfaq1', 'type' => 'faq', 'visible' => true, 'data' => ['heading' => 'Frequently Asked Questions']],   // the FAQ list used to be on the home page
            ['id' => 'oldtick1', 'type' => 'ticker', 'visible' => true, 'data' => ['items' => [['text' => 'Authentic Dutch Culture'], ['text' => 'Small Group Experiences']]]],
            ['id' => 'oldband1', 'type' => 'feature_band', 'visible' => true, 'data' => ['bullets' => [['text' => 'Authentic Dutch culture, not a checklist'], ['text' => 'Small groups with flexible itineraries']]]],
        ]);
        foreach ($sections as &$s) {
            if ($s['type'] === 'hero_video_quiz') {
                $s['data']['subheading'] = 'Windmills, tulip fields, cheese markets and hidden gems, on small-group and tailor-made tours with expertly crafted itineraries. Answer a few quick questions and get a free quote.';
                $s['data']['badges'][1]['text'] = 'Small group experiences';
            }
            if ($s['type'] === 'feature_band') {
                $s['data']['bullets'][1]['text'] = 'Small groups with flexible itineraries';
            }
            if ($s['type'] === 'ticker') {
                $s['data']['items'][1]['text'] = 'Small Group Experiences';
            }
            if ($s['type'] === 'faq') {
                $s['data']['heading'] = 'Our own custom FAQ heading about travellers';   // an admin edit that must be spell-fixed, not replaced
            }
        }
        unset($s);
        $home->update(['sections' => $sections, 'seo_title' => 'Authentic Netherlands Tours | Small Group, Self Drive & Chauffeur | GoNetherlandTours']);

        (require base_path('database/migrations/2026_11_21_000001_usp_wording_us_spelling_and_style_notes.php'))->up();

        $this->assertSame('Do travelers need a visa?', Faq::where('sort_order', 99)->value('question'));
        $this->assertSame('Most travelers do not. Check your inquiry details.', Faq::where('sort_order', 99)->value('answer'));
        $pkg->refresh();
        $this->assertSame(['Colorful fields', 'A cozy hotel'], $pkg->highlights);
        $this->assertSame('Traveling by car', $pkg->itinerary[0]['body']);
        $this->assertSame('Tours for travelers who love color.', setting('footer_text') ?? Setting::where('key', 'footer_text')->value('value'));
        $this->assertSame('Small group with a guide', TourType::where('slug', 'coach-tours')->value('style_note'));

        $home->refresh();
        $this->assertSame('Netherlands Tours: Self Drive, Chauffeur & Small-Group Coach | GoNetherlandTours', $home->seo_title);
        $by = fn ($type) => collect($home->allSections())->firstWhere('type', $type)['data'];
        $this->assertStringContainsString('private self drive and chauffeur tours or small-group coach tours', $by('hero_video_quiz')['subheading']);
        $this->assertSame('Small-group & private tours', $by('hero_video_quiz')['badges'][1]['text']);
        $this->assertSame('Small-group coach tours and private options', $by('feature_band')['bullets'][1]['text']);
        $this->assertSame('Small-Group & Private Tours', $by('ticker')['items'][1]['text']);
        $this->assertSame('Our own custom FAQ heading about travelers', $by('faq')['heading']);

        $types = collect($home->allSections())->pluck('type')->all();
        $this->assertSame(1, count(array_keys($types, 'usp_proof')), 'proof section added once');
        $this->assertSame('usp_proof', $types[array_search('feature_band', $types) + 1], 'right after the "Our promise" band');

        // running it again changes nothing (no duplicate proof section)
        (require base_path('database/migrations/2026_11_21_000001_usp_wording_us_spelling_and_style_notes.php'))->up();
        $this->assertSame(1, count(array_keys(collect($home->fresh()->allSections())->pluck('type')->all(), 'usp_proof')));
    }

    public function test_proof_copy_migration_replaces_only_untouched_placeholders(): void
    {
        $home = Page::bySlug('home');
        $sections = $home->allSections();
        foreach ($sections as &$s) {
            if ($s['type'] === 'usp_proof') {
                // simulate the previous release: placeholders everywhere, except one proof an admin already wrote
                $s['data']['items'][0]['proof'] = '[CONFIRM: a real example, such as a named cheese market, craft workshop or local host on your itineraries]';
                $s['data']['items'][1]['proof'] = '[CONFIRM: your maximum group size on coach tours, for example "no more than N travelers"]';
                $s['data']['items'][2]['proof'] = 'Our own hand-written proof: Texel, Terschelling and Vlieland.';
                $s['data']['items'][3]['proof'] = '[CONFIRM: who designs the itineraries and what experience they have]';
            }
        }
        unset($s);
        $home->update(['sections' => $sections]);

        $migration = require base_path('database/migrations/2026_11_22_000001_fill_usp_proof_copy.php');
        $migration->up();

        $items = collect($home->fresh()->allSections())->firstWhere('type', 'usp_proof')['data']['items'];
        $this->assertStringContainsString('19 windmills of Kinderdijk', $items[0]['proof']);
        $this->assertStringContainsString('limited to [CONFIRM: N] travelers', $items[1]['proof']);
        $this->assertSame('Our own hand-written proof: Texel, Terschelling and Vlieland.', $items[2]['proof'], 'admin text is never overwritten');
        $this->assertStringContainsString('timed entry for busy sights', $items[3]['proof']);

        $migration->up(); // idempotent
        $this->assertSame($items, collect($home->fresh()->allSections())->firstWhere('type', 'usp_proof')['data']['items']);
    }

    public function test_default_sections_include_the_proof_section_for_fresh_installs(): void
    {
        $types = collect(DefaultSections::home('x', []))->pluck('type')->all();
        $this->assertContains('usp_proof', $types);
        $this->assertSame(DB::table('tour_types')->count(), TourType::count());
    }
}
