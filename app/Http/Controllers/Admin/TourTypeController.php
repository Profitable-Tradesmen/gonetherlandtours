<?php

namespace App\Http\Controllers\Admin;

use App\Models\TourType;
use Illuminate\Database\Eloquent\Model;

class TourTypeController extends ResourceController
{
    protected function model(): string
    {
        return TourType::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Tour type', 'plural' => 'Tour types', 'route' => 'admin.tour-types', 'slug_from' => 'name', 'sortable' => true,
            'help' => 'Each active tour type gets its own landing page at /tours/slug, and appears on the home page, in the menu and in the package filters.',
            'columns' => ['Name' => 'name', 'Slug' => 'slug'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'slug', 'label' => 'URL slug (leave blank to generate)', 'type' => 'text', 'rules' => 'nullable|string|max:120'],
                ['name' => 'h1', 'label' => 'Page heading (H1)', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'icon', 'label' => 'Icon name (car, guide, coach)', 'type' => 'text', 'rules' => 'nullable|string|max:40'],
                ['name' => 'style_note', 'label' => 'Travel style note (shown on package and tour type pages, e.g. "Private: just your own party")', 'type' => 'text', 'rules' => 'nullable|string|max:160'],
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
        return $item->packages()->exists() ? 'This tour type still has packages. Move or delete them first.' : null;
    }
}
