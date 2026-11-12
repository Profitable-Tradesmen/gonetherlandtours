<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;

class FaqController extends ResourceController
{
    protected function model(): string
    {
        return Faq::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'FAQ', 'plural' => 'Home page FAQs', 'route' => 'admin.faqs', 'sortable' => true,
            'help' => 'These appear on the home page and are also published as FAQ structured data for Google.',
            'columns' => ['Question' => 'question'],
            'fields' => [
                ['name' => 'question', 'label' => 'Question', 'type' => 'text', 'rules' => 'required|string|max:255'],
                ['name' => 'answer', 'label' => 'Answer (plain text)', 'type' => 'textarea', 'rules' => 'required|string|max:3000'],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'],
            ],
        ];
    }
}
