<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
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
            'columns' => ['Name' => 'name', 'Slug' => 'slug'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'slug', 'label' => 'URL slug (leave blank to generate)', 'type' => 'text', 'rules' => 'nullable|string|max:120'],
                ['name' => 'h1', 'label' => 'Page heading (H1)', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rules' => 'nullable|string|max:500'],
                ['name' => 'body', 'label' => 'Page content', 'type' => 'rich', 'rules' => 'nullable|string'],
                ['name' => 'image', 'label' => 'Image (JPG, PNG or WebP)', 'type' => 'image'],
                ['name' => 'seo_title', 'label' => 'SEO title', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'meta_description', 'label' => 'Meta description', 'type' => 'textarea', 'rules' => 'nullable|string|max:320'],
                ['name' => 'noindex', 'label' => 'Hide from search engines (noindex)', 'type' => 'checkbox'],
                ['name' => 'is_active', 'label' => 'Active (visible on the site)', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function cannotDelete(Model $item): ?string
    {
        return $item->packages()->exists() ? 'This destination still has packages. Move or delete them first.' : null;
    }
}
