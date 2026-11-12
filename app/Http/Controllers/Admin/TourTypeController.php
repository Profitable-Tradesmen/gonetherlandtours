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
            'help' => 'Tour types appear on the home page, in the menu and in the package filters.',
            'columns' => ['Name' => 'name', 'Slug' => 'slug'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'slug', 'label' => 'URL slug (leave blank to generate)', 'type' => 'text', 'rules' => 'nullable|string|max:120'],
                ['name' => 'icon', 'label' => 'Icon name (car, guide, coach)', 'type' => 'text', 'rules' => 'nullable|string|max:40'],
                ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rules' => 'nullable|string|max:500'],
                ['name' => 'image', 'label' => 'Image (JPG, PNG or WebP)', 'type' => 'image'],
                ['name' => 'is_active', 'label' => 'Active (visible on the site)', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function cannotDelete(Model $item): ?string
    {
        return $item->packages()->exists() ? 'This tour type still has packages. Move or delete them first.' : null;
    }
}
