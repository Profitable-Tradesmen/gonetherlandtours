<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Keeps the public site on the brand palette (see docs/reference/DESIGN_SYSTEM.md). */
class PaletteTest extends TestCase
{
    /** Brand palette + its tints/shades + neutrals. Anything else is off-brand. */
    private const ALLOWED_HEX = ['437DA5', '2F6285', '1F4660', 'F2B743', 'D99A1F', 'A54225', '5A2D1A', '0C0D11', 'F6F1EA', 'E9DFD2', 'FFF', 'FFFFFF', '000', '000000'];

    /** @return array<int,string> files that make up the public site (admin screens are excluded). */
    private function publicFiles(): array
    {
        $files = [base_path('resources/css/app.css')];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path('resources/views'), \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $f) {
            $path = str_replace('\\', '/', $f->getPathname());
            if (str_ends_with($path, '.php') && ! str_contains($path, '/resources/views/admin/')) {
                $files[] = $path;
            }
        }
        foreach (glob(base_path('resources/js/*.js')) as $js) {
            $files[] = $js;
        }

        return $files;
    }

    public function test_public_pages_use_no_default_tailwind_colours(): void
    {
        $bad = [];
        foreach ($this->publicFiles() as $file) {
            $src = file_get_contents($file);
            if (preg_match_all('/\b(?:bg|text|border|ring|from|via|to|fill|stroke|divide|outline|shadow|decoration|accent)-(?:slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-\d{2,3}/', $src, $m)) {
                $bad[basename($file)] = array_unique($m[0]);
            }
        }
        $this->assertSame([], $bad, 'Use the brand tokens (accent, brand, rust, brown, ink, sand) instead of default Tailwind colours.');
    }

    public function test_public_files_use_only_palette_hex_values(): void
    {
        $bad = [];
        foreach ($this->publicFiles() as $file) {
            $src = file_get_contents($file);
            $src = preg_replace('/&#\d+;|&#x[0-9a-f]+;/i', '', $src);   // HTML entities such as &#10022;
            $src = preg_replace('/href\s*=\s*["\']#[\w-]*["\']|#main\b|#quote\b|url\(#[\w-]+\)|\bid\s*=\s*["\']#?[\w-]+["\']/i', '', $src); // in-page anchors
            if (preg_match_all('/(?<![\w&])#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})\b/', $src, $m)) {
                $off = array_values(array_unique(array_filter($m[1], fn ($h) => ! in_array(strtoupper($h), self::ALLOWED_HEX, true))));
                if ($off) {
                    $bad[basename($file)] = $off;
                }
            }
        }
        $this->assertSame([], $bad, 'Off-palette hex colours found. Use a token from resources/css/app.css (@theme).');
    }

    public function test_theme_tokens_match_the_mood_board_palette(): void
    {
        $css = file_get_contents(base_path('resources/css/app.css'));
        foreach (['--color-brand: #437DA5', '--color-accent: #F2B743', '--color-rust: #A54225', '--color-brown: #5A2D1A', '--color-ink: #0C0D11'] as $token) {
            $this->assertStringContainsString($token, $css);
        }
    }
}
