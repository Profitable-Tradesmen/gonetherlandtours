<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    #[DataProvider('attacks')]
    public function test_dangerous_markup_is_neutralised(string $in): void
    {
        $out = strtolower(HtmlSanitizer::clean($in));
        foreach (['javascript:', 'data:', 'vbscript:', '<script', 'onerror', 'onclick', 'onmouseover', 'onload', '<iframe', '<img', '<svg', 'style=', '<style'] as $bad) {
            $this->assertStringNotContainsString($bad, $out, "$bad survived in: $out");
        }
    }

    public static function attacks(): array
    {
        return [
            'entity encoded javascript' => ['<a href="jav&#97;script:alert(1)">x</a>'],
            'hex entity with tab' => ['<a href="java&#x09;script:alert(1)">x</a>'],
            'numeric entity first letter' => ['<a href="&#106;avascript:alert(1)">x</a>'],
            'raw tab/newline' => ["<a href=\"java\tscr\nipt:alert(1)\">x</a>"],
            'mixed case' => ['<a href="JaVaScRiPt:alert(1)">x</a>'],
            'leading spaces' => ['<a href="   javascript:alert(1)">x</a>'],
            'data url' => ['<a href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">x</a>'],
            'vbscript' => ['<a href="vbscript:msgbox(1)">x</a>'],
            'zero width in scheme' => ["<a href=\"java\u{200B}script:alert(1)\">x</a>"],
            'script tag' => ['<p>hi</p><script>alert(1)</script>'],
            'img onerror' => ['<img src=x onerror=alert(1)>'],
            'event handler on allowed tag' => ['<p onclick="a()" onmouseover="b()">t</p>'],
            'svg onload' => ['<svg onload=alert(1)><circle/></svg>'],
            'style attribute and tag' => ['<p style="background:url(javascript:alert(1))">t</p><style>x{}</style>'],
            'iframe' => ['<iframe src="https://evil.test"></iframe>'],
            'nested broken html' => ['<div><a href="javascript:alert(1)"><b>x</div>'],
        ];
    }

    public function test_normal_rich_text_is_kept(): void
    {
        $in = '<h2>Title</h2><p>Hello <strong>bold</strong> and <em>italic</em><br>line</p><ul><li>One</li><li>Two</li></ul><blockquote>Quote</blockquote>';
        $this->assertSame($in, HtmlSanitizer::clean($in));
    }

    public function test_safe_links_are_kept_and_hardened(): void
    {
        $out = HtmlSanitizer::clean('<a href="https://example.com/x?a=1&amp;b=2" target="_blank" class="big">Go</a> <a href="/about">A</a> <a href="#top">T</a> <a href="mailto:a@b.test">M</a> <a href="tel:+1555">P</a>');
        $this->assertStringContainsString('href="https://example.com/x?a=1&amp;b=2"', $out);
        $this->assertStringContainsString('target="_blank"', $out);
        $this->assertStringContainsString('rel="noopener noreferrer"', $out);
        $this->assertStringNotContainsString('class=', $out);
        foreach (['href="/about"', 'href="#top"', 'href="mailto:a@b.test"', 'href="tel:+1555"'] as $keep) {
            $this->assertStringContainsString($keep, $out);
        }
    }

    public function test_text_special_characters_and_unicode_survive(): void
    {
        $out = HtmlSanitizer::clean('<p>Caf&eacute; &amp; Kinderdijk &mdash; [CONFIRM: price] 5 &lt; 6 &euro;50</p>');
        $this->assertStringContainsString('Café', $out);
        $this->assertStringContainsString('&amp; Kinderdijk', $out);
        $this->assertStringContainsString('[CONFIRM: price]', $out);
        $this->assertStringContainsString('5 &lt; 6', $out);
    }

    public function test_empty_and_plain_input(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean('   '));
        $this->assertSame('just text', HtmlSanitizer::clean('just text'));
    }

    public function test_unknown_tags_are_unwrapped_and_dangerous_ones_removed_with_content(): void
    {
        $this->assertSame('<p>keep me</p>', HtmlSanitizer::clean('<section><font color="red"><p>keep me</p></font></section>'));
        $this->assertSame('<p>a</p>', HtmlSanitizer::clean('<p>a</p><script>secret()</script>'));
    }
}
