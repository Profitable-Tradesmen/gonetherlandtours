<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizStep;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Quiz editor: steps and their options (add, remove, reorder, rename, toggle, icon). */
class QuizController extends Controller
{
    public const ICONS = ['city', 'tulip', 'spring', 'summer', 'autumn', 'winter', 'coach', 'car', 'guide', 'question', 'yes', 'no', 'money', 'check', 'search', 'map', 'heart', 'star', 'shield'];

    public function index()
    {
        return view('admin.quiz.index', ['steps' => QuizStep::withCount('options')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.quiz.form', ['step' => new QuizStep(['type' => 'tiles', 'is_active' => true]), 'icons' => self::ICONS]);
    }

    public function edit(QuizStep $step)
    {
        return view('admin.quiz.form', ['step' => $step->load('options'), 'icons' => self::ICONS]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new QuizStep);
    }

    public function update(Request $request, QuizStep $step)
    {
        return $this->save($request, $step);
    }

    public function destroy(QuizStep $step)
    {
        $step->delete();

        return redirect()->route('admin.quiz.index')->with('status', 'Step deleted.');
    }

    public function toggle(QuizStep $step)
    {
        $step->update(['is_active' => ! $step->is_active]);

        return back();
    }

    public function move(QuizStep $step, string $dir)
    {
        $steps = QuizStep::orderBy('sort_order')->orderBy('id')->get()->values();
        $i = $steps->search(fn ($s) => $s->id === $step->id);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if (isset($steps[$j])) {
            $order = $steps->pluck('id')->all();
            [$order[$i], $order[$j]] = [$order[$j], $order[$i]];
            foreach ($order as $k => $id) {
                QuizStep::whereKey($id)->update(['sort_order' => $k + 1]);
            }
        }

        return back();
    }

    private function save(Request $request, QuizStep $step)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'type' => 'required|in:tiles,select',
            'key' => [$step->exists ? 'nullable' : 'required', 'regex:/^[a-z][a-z0-9_]{1,40}$/', 'unique:quiz_steps,key'],
            'options' => 'required|array|min:2|max:30',
            'options.*.label' => 'required|string|max:120',
            'options.*.value' => 'nullable|string|max:120',
            'options.*.icon' => 'nullable|string|max:40',
            'options.*.active' => 'nullable',
        ], ['key.regex' => 'The key must be lowercase letters, numbers and underscores, e.g. group_size.']);

        if (! $step->exists) {
            $step->key = $data['key'];
            $step->sort_order = (QuizStep::max('sort_order') ?? 0) + 1;
        }
        $step->question = $data['question'];
        $step->type = $data['type'];
        $step->is_active = $request->boolean('is_active');
        $step->save();

        // Replace options in the submitted order.
        $step->options()->delete();
        foreach (array_values($data['options']) as $i => $o) {
            $step->options()->create([
                'label' => $o['label'],
                'value' => filled($o['value'] ?? null) ? $o['value'] : $o['label'],
                'icon' => $o['icon'] ?: null,
                'sort_order' => $i + 1,
                'is_active' => isset($o['active']),
            ]);
        }

        return redirect()->route('admin.quiz.index')->with('status', 'Quiz step saved.');
    }
}
