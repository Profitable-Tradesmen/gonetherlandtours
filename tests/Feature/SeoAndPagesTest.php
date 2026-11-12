<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndPagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_sitemap_lists_indexable_pages_only(): void
    {
        $res = $this->get('/sitemap.xml')->assertOk();
        $this->assertStringContainsString('application/xml', $res->headers->get('Content-Type'));
        $xml = $res->getContent();
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString(url('/'), $xml);
        $this->assertStringContainsString(route('packages.index'), $xml);
        $this->assertStringContainsString(route('about'), $xml);
        $this->assertStringContainsString(route('destinations.show', 'tulips-and-countryside'), $xml);
        $this->assertStringNotContainsString('thank-you', $xml);
        $this->assertStringNotContainsString('/admin', $xml);
        // sample packages are noindex, so they are not in the sitemap
        $this->assertStringNotContainsString('/packages/sample-', $xml);
        $this->assertNotFalse(simplexml_load_string($xml), 'sitemap must be valid XML');
    }

    public function test_robots_blocks_admin_and_thank_you(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin', false)->assertSee('Disallow: /thank-you', false)->assertSee('Sitemap:', false);
    }

    public function test_public_pages_have_one_h1_title_description_and_canonical(): void
    {
        foreach (['/', '/packages', '/packages/sample-tulip-season-self-drive', '/destinations/zeeland-and-the-delta', '/about', '/contact', '/terms-and-conditions', '/privacy-policy'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html), "$url must have exactly one H1");
            $this->assertMatchesRegularExpression('/<title>[^<]{10,}<\/title>/', $html, $url);
            $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{20,}"/', $html, $url);
            $this->assertStringContainsString('<link rel="canonical"', $html, $url);
        }
    }

    public function test_home_has_faq_jsonld_and_travel_agency_schema(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('"@type":"TravelAgency"', $html);
    }

    public function test_package_page_has_trip_schema_and_breadcrumbs(): void
    {
        $html = $this->get('/packages/sample-tulip-season-self-drive')->getContent();
        $this->assertStringContainsString('"@type":"TouristTrip"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
    }

    public function test_thank_you_is_noindex_and_only_fires_conversion_after_a_submission(): void
    {
        $html = $this->get('/thank-you')->assertOk()->getContent();
        $this->assertStringContainsString('noindex', $html);
        $this->assertStringNotContainsString('enquiry_thank_you', $html);

        $html = $this->withSession(['enquiry' => ['id' => 5, 'name' => 'Jane', 'form' => 'contact']])->get('/thank-you')->getContent();
        $this->assertStringContainsString('enquiry_thank_you', $html);
    }

    public function test_gtm_snippet_only_when_id_is_set(): void
    {
        $this->assertStringNotContainsString('googletagmanager.com', $this->get('/')->getContent());
        \App\Models\Setting::put('gtm_id', 'GTM-ABC123');
        $this->assertStringContainsString('GTM-ABC123', $this->get('/')->getContent());
    }

    public function test_404_page_is_custom_and_noindex(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('We could not find that page')->assertSee('noindex', false);
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_packages_can_be_filtered(): void
    {
        $this->get('/packages?type=coach-tours')->assertOk()->assertSee('Coach Tour')->assertDontSee('Sample: Tulip Season Self Drive');
    }
}
