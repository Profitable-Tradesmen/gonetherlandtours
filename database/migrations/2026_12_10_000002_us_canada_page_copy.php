<?php

use App\Support\NorthAmericaCopy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Page copy written for US and Canadian travelers (see NorthAmericaCopy). Only text that is still the default wording changes. */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('pages')->whereNotNull('sections')->get(['id', 'slug', 'sections']) as $page) {
            $sections = json_decode((string) $page->sections, true);
            if (! is_array($sections)) {
                continue;
            }
            $sections = $this->walk($sections);
            if ($page->slug === 'home') {
                foreach ($sections as &$s) {
                    if (($s['type'] ?? '') === 'ticker' && is_array($s['data']['items'] ?? null)) {
                        $texts = array_column($s['data']['items'], 'text');
                        if (! in_array(NorthAmericaCopy::TICKER, $texts, true)) {
                            $s['data']['items'][] = ['text' => NorthAmericaCopy::TICKER];
                        }
                    }
                }
                unset($s);
            }
            DB::table('pages')->where('id', $page->id)->update(['sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
        $this->contentColumn();
    }

    /** The home page also keeps its default wording in the `content` JSON. */
    private function contentColumn(): void
    {
        foreach (DB::table('pages')->whereNotNull('content')->get(['id', 'content']) as $page) {
            $c = json_decode((string) $page->content, true);
            if (is_array($c)) {
                DB::table('pages')->where('id', $page->id)->update(['content' => json_encode($this->walk($c), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }
        }
    }

    private function walk(mixed $v, ?string $key = null): mixed
    {
        if (is_array($v)) {
            foreach ($v as $k => $x) {
                $v[$k] = $this->walk($x, is_string($k) ? $k : $key);
            }

            return $v;
        }
        if (! is_string($v) || in_array($key, ['id', 'type', 'icon', 'variant', 'style', 'align', 'tone', 'image', 'poster', 'url', 'link'], true)) {
            return $v;
        }

        return NorthAmericaCopy::apply($v);
    }
};
