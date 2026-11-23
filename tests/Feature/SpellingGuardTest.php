<?php

namespace Tests\Feature;

use App\Support\UsSpelling;
use Tests\TestCase;

/** The site targets US travelers: visitor-facing source files must not slip back to UK spelling. */
class SpellingGuardTest extends TestCase
{
    /** @return array<int,string> */
    private function visitorFacingFiles(): array
    {
        $files = [base_path('config/gnt.php'), base_path('app/Support/DefaultSections.php'), base_path('app/Support/Sections.php')];
        foreach ([['resources/views', 'php'], ['resources/js', 'js'], ['database/seeders', 'php']] as [$dir, $ext]) {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($dir), \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                $path = str_replace('\\', '/', $f->getPathname());
                if ($f->getExtension() === $ext && ! str_contains($path, 'resources/views/admin/') && ! str_contains($path, 'resources/views/emails/')) {
                    $files[] = $path;
                }
            }
        }

        return $files;
    }

    public function test_no_uk_spellings_in_visitor_facing_files(): void
    {
        $words = implode('|', array_keys(UsSpelling::WORDS));
        $bad = [];
        foreach ($this->visitorFacingFiles() as $file) {
            foreach (explode("\n", file_get_contents($file)) as $n => $line) {
                if (str_contains($line, 'UK spelling') || str_contains($line, 'UsSpelling')) {
                    continue; // documentation of the rule itself
                }
                if (preg_match('/\b('.$words.')\b/i', $line, $m)) {
                    $bad[] = basename($file).':'.($n + 1).' '.$m[1];
                }
            }
        }
        $this->assertSame([], $bad, 'UK spelling found; use US spelling (traveler, color, favorite, center...).');
    }

    public function test_visitor_facing_text_says_inquiry_not_enquiry(): void
    {
        foreach ($this->visitorFacingFiles() as $file) {
            $src = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression('/\b(your|send my|sending your|a new)\s+enquir/i', $src, basename($file));
        }
    }
}
