<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Admin > Pages: create pages, edit their SEO, and build them from sections (each section has its own edit form). */
class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::orderByDesc('is_system')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ]);
        $slug = $this->uniqueSlug(Str::slug($data['slug'] ?? '' ?: $data['title']));

        $page = Page::create([
            'slug' => $slug, 'title' => $data['title'], 'h1' => $data['title'], 'is_system' => false, 'is_published' => false,
            'sections' => [
                $this->newSection('page_header'),
                $this->newSection('rich_text', ['html' => '<p>Write your page content here.</p>']),
            ],
        ]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Page created as a draft. Edit its sections, then click the green "Publish this page" button at the top.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', ['page' => $page, 'types' => Sections::all()]);
    }

    public function update(Request $request, Page $page)
    {
        $rules = [
            'title' => 'required|string|max:190',
            'h1' => 'nullable|string|max:190',
            'seo_title' => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:320',
            'version' => 'nullable|string|max:20',
        ];
        if (! $page->is_system) {
            $rules['slug'] = ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::notIn(Page::RESERVED), Rule::unique('pages', 'slug')->ignore($page->id)];
        }
        $data = $request->validate($rules, ['slug.not_in' => 'That address is used by the site itself. Choose another.', 'slug.regex' => 'Use lower-case letters, numbers and hyphens only.']);
        $data['noindex'] = $request->boolean('noindex');
        if ($page->is_system) {
            unset($data['slug']);
        }
        if (! in_array($page->slug, ['terms', 'privacy'])) {
            unset($data['version']);
        }
        $page->update($data);

        return back()->with('status', 'Page saved.');
    }

    /** Publish a draft page or take a live custom page back to draft. Built-in pages are always live. */
    public function togglePublish(Page $page)
    {
        abort_if($page->is_system, 404);
        $page->update(['is_published' => ! $page->is_published]);

        return back()->with('status', $page->is_published ? 'Page published. It is now live at '.$page->url() : 'Page unpublished. It is a draft again and hidden from visitors.');
    }

    public function destroy(Page $page)
    {
        if ($page->is_system) {
            return back()->withErrors(['delete' => 'Built-in pages cannot be deleted. Unpublish or hide their sections instead.']);
        }
        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', 'Page deleted.');
    }

    // ---- sections -------------------------------------------------------------------------------------------

    public function addSection(Request $request, Page $page)
    {
        $type = $request->validate(['type' => ['required', Rule::in(array_keys(Sections::all()))]])['type'];
        $sections = $page->allSections();
        $section = $this->newSection($type);
        $sections[] = $section;
        $page->update(['sections' => $sections]);

        return redirect()->route('admin.pages.sections.edit', [$page, $section['id']])->with('status', 'Section added. Fill it in and save.');
    }

    public function editSection(Page $page, string $section)
    {
        [$i, $s] = $this->find($page, $section);

        return view('admin.pages.section', [
            'page' => $page, 'section' => $s, 'def' => Sections::definition($s['type']),
            'data' => Sections::resolve($s['type'], (array) ($s['data'] ?? [])),
        ]);
    }

    public function updateSection(Request $request, Page $page, string $section)
    {
        [$i, $s] = $this->find($page, $section);
        $sections = $page->allSections();
        $sections[$i]['data'] = Sections::sanitize($s['type'], $request);
        $page->update(['sections' => $sections]);

        $redirect = redirect()->route('admin.pages.edit', $page)->with('status', 'Section saved.');
        if ($missing = Sections::missingAlt($s['type'], $sections[$i]['data'])) {
            $redirect->with('warning', 'Saved, but these photos have no description (alt text): '.implode(', ', array_slice($missing, 0, 6)).(count($missing) > 6 ? ' and more' : '').'. Alt text helps visitors using screen readers and helps search engines. Edit the section to add it.');
        }

        return $redirect;
    }

    public function moveSection(Page $page, string $section, string $dir)
    {
        [$i] = $this->find($page, $section);
        $sections = $page->allSections();
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if (isset($sections[$j])) {
            [$sections[$i], $sections[$j]] = [$sections[$j], $sections[$i]];
            $page->update(['sections' => $sections]);
        }

        return redirect()->to(route('admin.pages.edit', $page).'#s-'.$section);
    }

    public function toggleSection(Page $page, string $section)
    {
        [$i] = $this->find($page, $section);
        $sections = $page->allSections();
        $sections[$i]['visible'] = ! ($sections[$i]['visible'] ?? true);
        $page->update(['sections' => $sections]);

        return redirect()->to(route('admin.pages.edit', $page).'#s-'.$section);
    }

    public function destroySection(Page $page, string $section)
    {
        [$i] = $this->find($page, $section);
        $sections = $page->allSections();
        array_splice($sections, $i, 1);
        $page->update(['sections' => $sections]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Section deleted.');
    }

    // ---- helpers --------------------------------------------------------------------------------------------

    private function newSection(string $type, array $data = []): array
    {
        return ['id' => Page::newSectionId(), 'type' => $type, 'visible' => true, 'data' => $data + Sections::defaults($type)];
    }

    /** @return array{0:int,1:array} index and section */
    private function find(Page $page, string $id): array
    {
        foreach ($page->allSections() as $i => $s) {
            if ($s['id'] === $id) {
                return [$i, $s];
            }
        }
        abort(404);
    }

    private function uniqueSlug(string $slug): string
    {
        $slug = $slug ?: 'page';
        if (in_array($slug, Page::RESERVED, true)) {
            $slug .= '-page';
        }
        $base = $slug;
        for ($i = 2; Page::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
