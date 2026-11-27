<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use App\Models\Place;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PlaceController extends ResourceController
{
    protected function model(): string
    {
        return Place::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Place', 'plural' => 'Places', 'route' => 'admin.places', 'sortable' => true,
            'help' => 'Towns and sights shown on destination, tour type and tour pages. A tour shows a place when its route (Admin > Packages) uses exactly this name. Photos need a credit in Admin > Photo credits unless they are your own.',
            'columns' => ['Name' => 'name', 'Destination' => 'region_name'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name (as written in tour routes)', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'destination_id', 'label' => 'Destination', 'type' => 'select', 'options' => ['' => '(none)'] + Destination::orderBy('sort_order')->pluck('name', 'id')->all(), 'rules' => 'nullable|integer|exists:destinations,id'],
                ['name' => 'note', 'label' => 'Short note', 'type' => 'textarea', 'rules' => 'nullable|string|max:500'],
                ['name' => 'lat', 'label' => 'Latitude (for the tour map, e.g. 52.3702)', 'type' => 'text', 'rules' => 'nullable|numeric|between:50,54'],
                ['name' => 'lon', 'label' => 'Longitude (for the tour map, e.g. 4.8952)', 'type' => 'text', 'rules' => 'nullable|numeric|between:3,8'],
                ['name' => 'image', 'label' => 'Photo (JPG, PNG or WebP)', 'type' => 'image'],
                ['name' => 'is_active', 'label' => 'Active (visible on the site)', 'type' => 'checkbox'],
            ],
        ];
    }

    protected function extraRules(Model $item): array
    {
        return ['name' => ['required', 'string', 'max:120', 'unique:places,name'.($item->exists ? ','.$item->id : '')]];
    }

    protected function prepare(array $data, Model $item, Request $request): array
    {
        foreach (['destination_id', 'lat', 'lon'] as $k) {
            $data[$k] = filled($data[$k] ?? null) ? $data[$k] : null;
        }

        return $data;
    }
}
