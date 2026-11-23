<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Neighbouring sections must not share a background, otherwise two sections read as one block. */
class BackgroundRhythmTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /** @return array<int,string> background "token" of each top-level block inside <main> */
    private function backgrounds(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();
        $main = substr($html, strpos($html, '<main id="main">') + 16);
        $main = substr($main, 0, strpos($main, '</main>'));
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?><body>'.$main.'</body>');

        $out = [];
        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }
            preg_match('/\bbg-(white|sand|brown|brand-900)\b/', $node->getAttribute('class'), $m);
            $token = $m[1] ?? 'white';                                    // no background class = the white page
            $hasPhoto = $node->getElementsByTagName('img')->length + $node->getElementsByTagName('video')->length > 0
                && str_contains($node->ownerDocument->saveHTML($node), 'absolute inset-0');
            // a block whose background is a photo (hero, photo band, call-to-action) is never "the same" as its neighbour
            $out[] = ($hasPhoto && in_array($token, ['white', 'brown', 'brand-900'], true) && ! str_contains($node->getAttribute('class'), 'bg-sand')) ? 'photo' : $token;
        }

        return $out;
    }

    #[DataProvider('pages')]
    public function test_neighbouring_sections_have_different_backgrounds(string $url): void
    {
        $bg = $this->backgrounds($url);
        $this->assertGreaterThan(1, count($bg), $url);
        foreach ($bg as $i => $token) {
            if ($i > 0 && $token !== 'photo' && $token === $bg[$i - 1]) {
                $this->fail("$url: sections #$i and #".($i + 1).' both have a "'.$token.'" background: '.implode(' > ', $bg));
            }
        }
        $this->addToAssertionCount(1);
    }

    public static function pages(): array
    {
        return array_map(fn ($u) => [$u], [
            '/', '/about', '/faq', '/testimonials', '/contact', '/packages', '/packages/sample-tulip-season-self-drive',
            '/tours/self-drive', '/tours/coach-tours', '/destinations', '/destinations/zeeland-and-the-delta', '/blog',
        ]);
    }
}
