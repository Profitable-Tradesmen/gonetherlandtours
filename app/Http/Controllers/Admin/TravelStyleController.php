<?php

namespace App\Http\Controllers\Admin;

use App\Models\TravelStyle;
use Illuminate\Database\Eloquent\Model;

class TravelStyleController extends ResourceController
{
    protected function model(): string
    {
        return TravelStyle::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Travel style', 'plural' => 'Travel styles', 'route' => 'admin.travel-styles', 'slug_from' => 'name', 'sortable' => true,
            'help' => 'Each active style appears in the Travel styles menu and has its own page at /travel-styles/slug.',
            'columns' => ['Name' => 'name', 'Slug' => 'slug', 'Tours' => 'tour_list'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'slug', 'label' => 'URL slug (leave blank to generate)', 'type' => 'text', 'rules' => 'nullable|string|max:120'],
                ['name' => 'summary', 'label' => 'Short summary (menu, cards and page banner)', 'type' => 'textarea', 'rules' => 'nullable|string|max:500'],
                ['name' => 'body', 'label' => 'Page text', 'type' => 'rich', 'rules' => 'nullable|string'],
                ['name' => 'highlights', 'label' => 'Highlights (one per line)', 'type' => 'textarea', 'rules' => 'nullable|string|max:2000'],
                ['name' => 'places', 'label' => 'Places (separate with commas)', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                ['name' => 'best_time', 'label' => 'Best time to go', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'tours', 'label' => 'Tours', 'type' => 'tours', 'help' => 'Tick the tours shown on this travel style page. A tour can belong to several travel styles.'],
                ['name' => 'image', 'label' => 'Image (JPG, PNG or WebP)', 'type' => 'image'],
                ['name' => 'seo_title', 'label' => 'SEO title', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'meta_description', 'label' => 'Meta description', 'type' => 'textarea', 'rules' => 'nullable|string|max:320'],
                ['name' => 'noindex', 'label' => 'Hide from search engines (noindex)', 'type' => 'checkbox'],
                ['name' => 'is_active', 'label' => 'Active (visible on the site)', 'type' => 'checkbox'],
            ],
        ];
    }

    /** Tours already chosen keep their place on the page; newly ticked ones are added after them. */
    protected function syncTours(Model $item, array $ids): void
    {
        $kept = array_values(array_intersect($item->tourIds(), $ids));
        $ordered = array_merge($kept, array_values(array_diff($ids, $kept)));
        $item->packages()->sync(collect($ordered)->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i + 1]])->all());
    }
}
