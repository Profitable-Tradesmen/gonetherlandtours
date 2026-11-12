<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
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
            ])['c'] ?? [];
            $data['content'] = array_replace_recursive($page->content ?? [], $c);
        }

        $page->update($data);

        return back()->with('status', 'Page saved.');
    }
}
