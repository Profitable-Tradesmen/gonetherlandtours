<?php

namespace App\Http\Controllers\Admin;

use App\Models\PhotoCredit;

class PhotoCreditController extends ResourceController
{
    protected function model(): string
    {
        return PhotoCredit::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Photo credit', 'plural' => 'Photo credits', 'route' => 'admin.photo-credits', 'sortable' => true,
            'help' => 'Listed on the public page /photo-credits. Creative Commons photos must stay credited while they are used on the site.',
            'columns' => ['Photo' => 'title', 'Author' => 'author', 'License' => 'license'],
            'fields' => [
                ['name' => 'title', 'label' => 'Photo title', 'type' => 'text', 'rules' => 'required|string|max:190'],
                ['name' => 'author', 'label' => 'Photographer', 'type' => 'text', 'rules' => 'required|string|max:190'],
                ['name' => 'license', 'label' => 'License (e.g. CC BY-SA 4.0)', 'type' => 'text', 'rules' => 'required|string|max:60'],
                ['name' => 'license_url', 'label' => 'License link', 'type' => 'text', 'rules' => 'nullable|url:http,https|max:255'],
                ['name' => 'source_url', 'label' => 'Link to the original photo', 'type' => 'text', 'rules' => 'nullable|url:http,https|max:500'],
                ['name' => 'file', 'label' => 'File on this site (e.g. images/places/delft.webp)', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            ],
        ];
    }
}
