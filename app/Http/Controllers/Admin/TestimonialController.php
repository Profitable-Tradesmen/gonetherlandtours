<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;

class TestimonialController extends ResourceController
{
    protected function model(): string
    {
        return Testimonial::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Testimonial', 'plural' => 'Testimonials', 'route' => 'admin.testimonials', 'sortable' => true,
            'help' => 'Only publish real reviews you have permission to use. Untick "Placeholder" once a review is real.',
            'columns' => ['Name' => 'name', 'Quote' => 'quote'],
            'fields' => [
                ['name' => 'name', 'label' => 'Traveller name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'location', 'label' => 'Location (e.g. Austin, Texas)', 'type' => 'text', 'rules' => 'nullable|string|max:120'],
                ['name' => 'trip', 'label' => 'Trip taken (e.g. 8-night coach tour, Tulip Season)', 'type' => 'text', 'rules' => 'nullable|string|max:120'],
                ['name' => 'image', 'label' => 'Traveler photo (optional; a round photo is shown instead of the initial)', 'type' => 'image'],
                ['name' => 'rating', 'label' => 'Star rating', 'type' => 'select', 'options' => ['5' => '5 stars', '4' => '4 stars', '3' => '3 stars', '2' => '2 stars', '1' => '1 star'], 'default' => '5', 'rules' => 'required|integer|between:1,5'],
                ['name' => 'quote', 'label' => 'Review', 'type' => 'textarea', 'rules' => 'required|string|max:1500'],
                ['name' => 'is_placeholder', 'label' => 'This is placeholder text (not a real review)', 'type' => 'checkbox'],
                ['name' => 'is_active', 'label' => 'Active (show on the home page)', 'type' => 'checkbox'],
            ],
        ];
    }
}
