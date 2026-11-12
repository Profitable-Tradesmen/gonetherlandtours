<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Generic admin CRUD driven by a field list. Subclasses only describe the model and fields.
 * Field types: text, textarea, rich, number, checkbox, select, image, password.
 */
abstract class ResourceController extends Controller
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    /**
     * Keys: title, plural, route (e.g. admin.faqs), fields[], columns[label=>attribute], sortable(bool), slug_from(?string)
     */
    abstract protected function config(): array;

    protected function order(): string
    {
        return $this->config()['sortable'] ?? true ? 'sort_order' : 'id';
    }

    public function index()
    {
        $items = ($this->model())::orderBy($this->order())->get();

        return view('admin.resource.index', ['cfg' => $this->config(), 'items' => $items]);
    }

    public function create()
    {
        return view('admin.resource.form', ['cfg' => $this->config(), 'item' => new ($this->model())]);
    }

    public function edit($id)
    {
        return view('admin.resource.form', ['cfg' => $this->config(), 'item' => ($this->model())::findOrFail($id)]);
    }

    public function store(Request $request)
    {
        $item = new ($this->model());
        $this->save($request, $item);

        return redirect()->route($this->config()['route'].'.index')->with('status', 'Saved.');
    }

    public function update(Request $request, $id)
    {
        $this->save($request, ($this->model())::findOrFail($id));

        return redirect()->route($this->config()['route'].'.index')->with('status', 'Saved.');
    }

    public function destroy($id)
    {
        $item = ($this->model())::findOrFail($id);
        if ($error = $this->cannotDelete($item)) {
            return back()->withErrors(['delete' => $error]);
        }
        $item->delete();

        return back()->with('status', 'Deleted.');
    }

    /** Move an item up/down in sort order. */
    public function move($id, string $dir)
    {
        $model = $this->model();
        $items = $model::orderBy('sort_order')->orderBy('id')->get()->values();
        $i = $items->search(fn ($x) => $x->id == $id);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($i !== false && isset($items[$j])) {
            $items->splice($i, 1, [$items[$j]]);
            $items->splice($j, 1, [($model::find($id))]);
            $items->values()->each(fn ($m, $k) => $m->update(['sort_order' => $k + 1]));
        }

        return back();
    }

    protected function cannotDelete(Model $item): ?string
    {
        return null;
    }

    /** Hook for subclasses to adjust validated data before saving. */
    protected function prepare(array $data, Model $item, Request $request): array
    {
        return $data;
    }

    protected function extraRules(Model $item): array
    {
        return [];
    }

    private function save(Request $request, Model $item): void
    {
        $cfg = $this->config();
        $rules = [];
        foreach ($cfg['fields'] as $f) {
            $rules[$f['name']] = $f['rules'] ?? 'nullable';
            if ($f['type'] === 'image') {
                $rules[$f['name'].'_file'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'];
                $rules[$f['name'].'_alt'] = ['required_with:'.$f['name'].'_file', 'nullable', 'string', 'max:190'];
                unset($rules[$f['name']]);
            }
        }
        $rules = array_merge($rules, $this->extraRules($item));
        $data = $request->validate($rules);

        foreach ($cfg['fields'] as $f) {
            $n = $f['name'];
            if ($f['type'] === 'checkbox') {
                $data[$n] = $request->boolean($n);
            } elseif ($f['type'] === 'rich') {
                $data[$n] = clean_html($data[$n] ?? '');
            } elseif ($f['type'] === 'image') {
                unset($data[$n.'_file']);
                if ($request->hasFile($n.'_file')) {
                    $media = app(ImageService::class)->store($request->file($n.'_file'), $request->input($n.'_alt'));
                    $data[$n] = 'storage/'.$media->path.'-960.webp';
                }
                // The alt text is stored in the model's <name>_alt column (e.g. image_alt).
                $data[$n.'_alt'] = $request->input($n.'_alt') ?: $item->{$n.'_alt'};
            } elseif ($f['type'] === 'password') {
                if (blank($data[$n] ?? null)) {
                    unset($data[$n]);
                }
            }
        }

        if (! empty($cfg['slug_from'])) {
            $data['slug'] = $this->uniqueSlug($item, Str::slug($request->input('slug') ?: $request->input($cfg['slug_from'])));
        }
        if (! $item->exists && ($cfg['sortable'] ?? true)) {
            $data['sort_order'] = (($this->model())::max('sort_order') ?? 0) + 1;
        }
        $item->fill($this->prepare($data, $item, $request))->save();
    }

    private function uniqueSlug(Model $item, string $slug): string
    {
        $slug = $slug ?: Str::random(6);
        $base = $slug;
        $i = 2;
        while (($this->model())::where('slug', $slug)->when($item->exists, fn ($q) => $q->where('id', '!=', $item->id))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
