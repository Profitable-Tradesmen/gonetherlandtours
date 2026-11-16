<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BlogController extends ResourceController
{
    protected function model(): string
    {
        return BlogPost::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Blog post', 'plural' => 'Blog', 'route' => 'admin.blog', 'slug_from' => 'title', 'sortable' => false,
            'help' => 'Published posts appear on the Blog page, and the two newest show in the "Guides and inspiration" section of the home page. Posts dated in the future stay hidden until that day.',
            'columns' => ['Title' => 'title', 'Date' => 'published_at', 'Status' => 'status_label'],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:190'],
                ['name' => 'slug', 'label' => 'URL slug (leave blank to generate)', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'label', 'label' => 'Card label (e.g. Guide 01, Travel tips)', 'type' => 'text', 'rules' => 'nullable|string|max:60'],
                ['name' => 'excerpt', 'label' => 'Short summary (shown on cards)', 'type' => 'textarea', 'rules' => 'nullable|string|max:400'],
                ['name' => 'body', 'label' => 'Article content', 'type' => 'rich', 'rules' => 'nullable|string'],
                ['name' => 'image', 'label' => 'Cover image (JPG, PNG or WebP)', 'type' => 'image'],
                ['name' => 'published_at', 'label' => 'Publish date (blank = today when published)', 'type' => 'date', 'rules' => 'nullable|date'],
                ['name' => 'seo_title', 'label' => 'SEO title', 'type' => 'text', 'rules' => 'nullable|string|max:190'],
                ['name' => 'meta_description', 'label' => 'Meta description', 'type' => 'textarea', 'rules' => 'nullable|string|max:320'],
                ['name' => 'noindex', 'label' => 'Hide from search engines (noindex)', 'type' => 'checkbox'],
                ['name' => 'is_published', 'label' => 'Published (visible on the site)', 'type' => 'checkbox'],
            ],
        ];
    }

    public function index()
    {
        $items = BlogPost::orderByDesc('published_at')->orderByDesc('id')->get();

        return view('admin.resource.index', ['cfg' => $this->config(), 'items' => $items]);
    }

    protected function prepare(array $data, Model $item, Request $request): array
    {
        // Publishing without a date means "today".
        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now()->toDateString();
        }

        return $data;
    }
}
