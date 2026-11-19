<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodeSnippet;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin > Header & Footer: custom code snippets. Administrators only (the area is not in AdminUser::EDITOR_AREAS). */
class SnippetController extends Controller
{
    /** Pages offered in the picker: path => label. Anything else can be typed in the "other addresses" box. */
    private function pageChoices(): array
    {
        $choices = [];
        foreach (Page::orderBy('title')->get() as $p) {
            if ($p->is_published || $p->is_system) {
                $choices[CodeSnippet::normalizePath($p->path())] = $p->title;
            }
        }
        $choices += ['/packages' => 'All packages (list)', '/packages/*' => 'Every package page', '/destinations' => 'Destinations (list)', '/blog' => 'Blog (list)', '/blog/*' => 'Every blog post', '/thank-you' => 'Thank you page'];
        asort($choices);

        return $choices;
    }

    public function index()
    {
        return view('admin.snippets.index', ['snippets' => CodeSnippet::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.snippets.form', ['snippet' => new CodeSnippet(['position' => 'head', 'scope' => 'all', 'is_active' => true, 'sort_order' => (int) CodeSnippet::max('sort_order') + 1]), 'choices' => $this->pageChoices()]);
    }

    public function edit(int $id)
    {
        return view('admin.snippets.form', ['snippet' => CodeSnippet::findOrFail($id), 'choices' => $this->pageChoices()]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new CodeSnippet);
    }

    public function update(Request $request, int $id)
    {
        return $this->save($request, CodeSnippet::findOrFail($id));
    }

    public function destroy(int $id)
    {
        CodeSnippet::findOrFail($id)->delete();

        return redirect()->route('admin.snippets.index')->with('status', 'Snippet deleted.');
    }

    public function toggle(int $id)
    {
        $s = CodeSnippet::findOrFail($id);
        $s->update(['is_active' => ! $s->is_active]);

        return back()->with('status', $s->is_active ? 'Snippet enabled.' : 'Snippet disabled.');
    }

    private function save(Request $request, CodeSnippet $snippet)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:200000',
            'position' => ['required', Rule::in(array_keys(CodeSnippet::POSITIONS))],
            'scope' => ['required', Rule::in(['all', 'only'])],
            'include_pages' => 'nullable|array',
            'include_pages.*' => 'string|max:255',
            'include_extra' => 'nullable|string|max:5000',
            'exclude_pages' => 'nullable|array',
            'exclude_pages.*' => 'string|max:255',
            'exclude_extra' => 'nullable|string|max:5000',
            'sort_order' => 'nullable|integer|min:0|max:100000',
        ]);

        $include = CodeSnippet::parsePaths(implode("\n", $data['include_pages'] ?? []).",\n".($data['include_extra'] ?? ''));
        $exclude = CodeSnippet::parsePaths(implode("\n", $data['exclude_pages'] ?? []).",\n".($data['exclude_extra'] ?? ''));

        if ($data['scope'] === 'only' && ! $include) {
            return back()->withInput()->withErrors(['include_pages' => 'Choose at least one page, or switch the scope to "Entire website".']);
        }

        // The code is stored exactly as entered (no sanitising: this is an administrators-only tool).
        $snippet->fill([
            'name' => trim($data['name']),
            'code' => $data['code'],
            'position' => $data['position'],
            'scope' => $data['scope'],
            'include_paths' => $include ? implode("\n", $include) : null,
            'exclude_paths' => $exclude ? implode("\n", $exclude) : null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ])->save();

        return redirect()->route('admin.snippets.index')->with('status', 'Snippet saved.');
    }
}
