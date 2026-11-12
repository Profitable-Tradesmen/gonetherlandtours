<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\TourType;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        return view('admin.packages.index', ['packages' => Package::with(['tourType', 'destination'])->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.packages.form', $this->formData(new Package(['currency' => 'EUR', 'nights' => 7, 'is_published' => false])));
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', $this->formData($package->load('images')));
    }

    private function formData(Package $package): array
    {
        return ['package' => $package, 'tourTypes' => TourType::orderBy('sort_order')->get(), 'destinations' => Destination::orderBy('sort_order')->get()];
    }

    public function store(Request $request)
    {
        $package = new Package;
        $this->save($request, $package);

        return redirect()->route('admin.packages.edit', $package)->with('status', 'Package created. You can add gallery images below.');
    }

    public function update(Request $request, Package $package)
    {
        $this->save($request, $package);

        // Redirect by the (possibly changed) slug rather than back(), which would 404 after a slug edit.
        return redirect()->route('admin.packages.edit', $package)->with('status', 'Package saved.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')->with('status', 'Package deleted.');
    }

    public function duplicate(Package $package)
    {
        $copy = $package->replicate();
        $copy->title = $package->title.' (copy)';
        $copy->slug = $this->uniqueSlug(Str::slug($copy->title), null);
        $copy->is_published = false;
        $copy->is_featured = false;
        $copy->sort_order = (Package::max('sort_order') ?? 0) + 1;
        $copy->save();
        $copy->images()->sync($package->images->mapWithKeys(fn ($m) => [$m->id => ['sort_order' => $m->pivot->sort_order]])->all());

        return redirect()->route('admin.packages.edit', $copy)->with('status', 'Duplicated. It is unpublished until you publish it.');
    }

    public function toggle(Package $package, string $field)
    {
        abort_unless(in_array($field, ['is_published', 'is_featured']), 404);
        $package->update([$field => ! $package->{$field}]);

        return back();
    }

    public function move(Package $package, string $dir)
    {
        $all = Package::orderBy('sort_order')->orderBy('id')->get()->values();
        $i = $all->search(fn ($p) => $p->id === $package->id);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if (isset($all[$j])) {
            $order = $all->pluck('id')->all();
            [$order[$i], $order[$j]] = [$order[$j], $order[$i]];
            foreach ($order as $k => $id) {
                Package::whereKey($id)->update(['sort_order' => $k + 1]);
            }
        }

        return back();
    }

    private function lines(?string $text): array
    {
        return array_values(array_filter(array_map(fn ($l) => trim(strip_tags($l)), preg_split('/\R/', (string) $text))));
    }

    private function save(Request $request, Package $package): void
    {
        $data = $request->validate([
            'title' => 'required|string|max:190',
            'slug' => 'nullable|string|max:190',
            'tour_type_id' => 'nullable|exists:tour_types,id',
            'destination_id' => 'nullable|exists:destinations,id',
            'nights' => 'required|integer|min:1|max:60',
            'price_from' => 'nullable|numeric|min:0|max:1000000',
            'currency' => 'required|in:EUR,USD,GBP',
            'summary' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'highlights_text' => 'nullable|string|max:3000',
            'inclusions_text' => 'nullable|string|max:3000',
            'exclusions_text' => 'nullable|string|max:3000',
            'itinerary' => 'nullable|array|max:40',
            'itinerary.*.title' => 'nullable|string|max:190',
            'itinerary.*.body' => 'nullable|string|max:3000',
            'faqs' => 'nullable|array|max:30',
            'faqs.*.question' => 'nullable|string|max:255',
            'faqs.*.answer' => 'nullable|string|max:3000',
            'seo_title' => 'nullable|string|max:190',
            'meta_description' => 'nullable|string|max:320',
            'social_image' => 'nullable|string|max:255',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:8192',
            'new_alts' => 'nullable|array',
            'new_alts.*' => 'nullable|string|max:190',
            'img' => 'nullable|array',
            'img.*.alt' => 'required|string|max:190',
            'img.*.sort' => 'nullable|integer',
        ], ['img.*.alt.required' => 'Every gallery image needs alt text.']);

        // Each new upload must come with alt text.
        foreach ($request->file('new_images', []) as $i => $file) {
            if (blank($request->input("new_alts.$i"))) {
                back()->withErrors(["new_alts.$i" => 'Alt text is required for each uploaded image.'])->throwResponse();
            }
        }

        $package->fill([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug(Str::slug(($data['slug'] ?? null) ?: $data['title']), $package->id),
            'tour_type_id' => $data['tour_type_id'] ?? null,
            'destination_id' => $data['destination_id'] ?? null,
            'nights' => $data['nights'],
            'price_from' => $data['price_from'] ?? null,
            'currency' => $data['currency'],
            'summary' => $data['summary'] ?? null,
            'description' => clean_html($data['description'] ?? ''),
            'highlights' => $this->lines($data['highlights_text'] ?? ''),
            'inclusions' => $this->lines($data['inclusions_text'] ?? ''),
            'exclusions' => $this->lines($data['exclusions_text'] ?? ''),
            'itinerary' => collect($data['itinerary'] ?? [])->filter(fn ($d) => filled($d['title'] ?? null))->values()->all(),
            'faqs' => collect($data['faqs'] ?? [])->filter(fn ($f) => filled($f['question'] ?? null))->values()->all(),
            'seo_title' => $data['seo_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'social_image' => $data['social_image'] ?? null,
            'noindex' => $request->boolean('noindex'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'is_sample' => $request->boolean('is_sample'),
        ]);
        if (! $package->exists) {
            $package->sort_order = (Package::max('sort_order') ?? 0) + 1;
        }
        $package->save();

        // Existing gallery: alt text, order, removal
        foreach ($data['img'] ?? [] as $mediaId => $row) {
            if (isset($request->input('remove', [])[$mediaId])) {
                $package->images()->detach($mediaId);

                continue;
            }
            Media::whereKey($mediaId)->update(['alt' => trim(strip_tags($row['alt']))]);
            $package->images()->updateExistingPivot($mediaId, ['sort_order' => (int) ($row['sort'] ?? 0)]);
        }

        // New uploads (resized, converted to WebP)
        $max = (int) $package->images()->max('package_images.sort_order');
        foreach ($request->file('new_images', []) as $i => $file) {
            $media = app(ImageService::class)->store($file, trim(strip_tags($request->input("new_alts.$i"))));
            $package->images()->attach($media->id, ['sort_order' => ++$max]);
        }
    }

    private function uniqueSlug(string $slug, ?int $ignoreId): string
    {
        $slug = $slug ?: Str::random(6);
        $base = $slug;
        $i = 2;
        while (Package::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
