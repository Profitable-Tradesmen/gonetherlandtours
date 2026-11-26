<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use App\Models\Package;
use Illuminate\Database\Eloquent\Model;

class DestinationController extends ResourceController
{
    protected function model(): string
    {
        return Destination::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Destination', 'plural' => 'Destinations', 'route' => 'admin.destinations', 'slug_from' => 'name', 'sortable' => true,
            'help' => 'Each active destination gets its own landing page at /destinations/slug.',
            'columns' => ['Name' => 'name', 'Slug' => 'slug', 'Tours' => 'tour_list'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'slug', 'label' => 'URL slug (leave blank to generate)', 'type' => 'text', 'rules' => 'nullable|string|max:120'],
                ['name' => 'h1', 'label' => 'Page heading (H1)', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rules' => 'nullable|string|max:500'],
                ['name' => 'places', 'label' => 'Places listed on the destination card (separate with commas)', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                ['name' => 'tours', 'label' => 'Tours', 'type' => 'tours', 'show_destination' => true, 'help' => 'Tick the tours in this destination. A tour has one destination, so ticking a tour here moves it from its current one. A tour you untick has no destination until you pick one.'],
                ['name' => 'body', 'label' => 'Page content', 'type' => 'rich', 'rules' => 'nullable|string'],
                ['name' => 'image', 'label' => 'Image (JPG, PNG or WebP)', 'type' => 'image'],
                ['name' => 'seo_title', 'label' => 'SEO title', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'meta_description', 'label' => 'Meta description', 'type' => 'textarea', 'rules' => 'nullable|string|max:320'],
                ['name' => 'noindex', 'label' => 'Hide from search engines (noindex)', 'type' => 'checkbox'],
                ['name' => 'is_active', 'label' => 'Active (visible on the site)', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function syncTours(Model $item, array $ids): void
    {
        Package::where('destination_id', $item->id)->whereNotIn('id', $ids)->update(['destination_id' => null]);
        Package::whereIn('id', $ids)->update(['destination_id' => $item->id]);
    }

    protected function cannotDelete(Model $item): ?string
    {
        return $item->packages()->exists() ? 'This destination still has packages. Move or delete them first.' : null;
    }
}
