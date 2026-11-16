<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ImageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('id')->get()]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'h1' => 'nullable|string|max:190',
            'seo_title' => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:320',
            'body' => 'nullable|string',
            'version' => 'nullable|string|max:20',
        ]);
        $data['noindex'] = $request->boolean('noindex');
        if (array_key_exists('body', $data)) {
            $data['body'] = clean_html($data['body']);
        }
        if (! in_array($page->slug, ['terms', 'privacy'])) {
            unset($data['version']);
        }

        if ($page->slug === 'home') {
            $c = $request->validate([
                'c.hero_sub' => 'nullable|string|max:400', 'c.badges' => 'nullable|array|max:3', 'c.badges.*' => 'nullable|string|max:80',
                'c.why' => 'nullable|array|max:4', 'c.why.*.title' => 'nullable|string|max:100', 'c.why.*.text' => 'nullable|string|max:300',
                'c.steps' => 'nullable|array|max:3', 'c.steps.*.title' => 'nullable|string|max:100', 'c.steps.*.text' => 'nullable|string|max:300',
                'c.why_heading' => 'nullable|string|max:120', 'c.tours_heading' => 'nullable|string|max:120', 'c.packages_heading' => 'nullable|string|max:120',
                'c.how_heading' => 'nullable|string|max:120', 'c.testimonials_heading' => 'nullable|string|max:120', 'c.faq_heading' => 'nullable|string|max:120',
                'c.cta_heading' => 'nullable|string|max:150', 'c.cta_text' => 'nullable|string|max:300',
                'c.show_gallery' => 'nullable|boolean',
            ])['c'] ?? [];
            // A hidden "0" input is always sent, so an unticked box saves false (never silently reverts to the "shown" default).
            if (array_key_exists('show_gallery', $c)) {
                $c['show_gallery'] = (bool) $c['show_gallery'];
            }
            $content = array_replace_recursive($page->content ?? [], $c);
            if ($request->has('gallery')) {
                $content['gallery'] = $this->buildGallery($request);
            }
            $data['content'] = $content;
        }

        $page->update($data);

        return back()->with('status', 'Page saved.');
    }

    /** Validate and store the home page gallery section (heading + a replaceable list of photos). */
    private function buildGallery(Request $request): array
    {
        $g = $request->validate([
            'gallery.eyebrow' => 'nullable|string|max:80',
            'gallery.title' => 'nullable|string|max:150',
            'gallery.items' => 'nullable|array|max:24',
            'gallery.items.*.caption' => 'nullable|string|max:80',
            'gallery.items.*.alt' => 'nullable|string|max:190',
            // Existing photos are kept by path; only site images or uploaded media paths are accepted.
            'gallery.items.*.image' => ['nullable', 'string', 'max:255', 'regex:#^(images|storage)/[A-Za-z0-9_./-]+$#'],
            'gallery.items.*.image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ])['gallery'] ?? [];

        $items = [];
        foreach (array_values($g['items'] ?? []) as $i => $it) {
            $caption = trim((string) ($it['caption'] ?? ''));
            $alt = trim((string) ($it['alt'] ?? '')) ?: $caption;
            $image = $it['image'] ?? null;
            if ($file = $request->file("gallery.items.$i.image_file")) {
                $image = 'storage/'.app(ImageService::class)->store($file, $alt)->path.'-960.webp';
            }
            if (! $image) {
                continue; // a row without a photo is dropped
            }
            $items[] = ['caption' => $caption, 'alt' => $alt, 'image' => $image];
        }

        return ['eyebrow' => trim((string) ($g['eyebrow'] ?? '')), 'title' => trim((string) ($g['title'] ?? '')) ?: 'A glimpse of the *real Netherlands*', 'items' => $items];
    }
}
