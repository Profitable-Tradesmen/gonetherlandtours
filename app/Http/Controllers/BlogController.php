<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Support\Seo;

class BlogController extends Controller
{
    public function index()
    {
        return view('pages.blog-index', [
            'posts' => BlogPost::published()->paginate(9),
            'seo' => Seo::make([
                'title' => 'Travel Journal: Netherlands Guides and Inspiration | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => 'Guides, hidden gems and travel inspiration for your Netherlands trip.',
                'canonical' => route('blog.index'),
                'breadcrumbs' => [['Home', url('/')], ['Blog', route('blog.index')]],
            ]),
        ]);
    }

    public function show(BlogPost $post)
    {
        abort_unless(BlogPost::published()->whereKey($post->id)->exists(), 404);

        $related = BlogPost::published()->whereKeyNot($post->id)->limit(3)->get();
        $description = $post->meta_description ?: $post->excerpt;

        return view('pages.blog-show', [
            'post' => $post,
            'related' => $related,
            'seo' => Seo::make([
                'title' => $post->seo_title ?: $post->title.' | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => $description,
                'image' => $post->image ?: null,
                'noindex' => $post->noindex,
                'type' => 'article',
                'breadcrumbs' => [['Home', url('/')], ['Blog', route('blog.index')], [$post->title, route('blog.show', $post)]],
                'jsonld' => [array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => $post->title,
                    'description' => $description,
                    'image' => $post->image ? asset($post->image) : null,
                    'datePublished' => $post->published_at?->toDateString(),
                    'dateModified' => $post->updated_at?->toDateString(),
                    'mainEntityOfPage' => route('blog.show', $post),
                    'publisher' => ['@type' => 'Organization', 'name' => setting('brand_name', 'GoNetherlandTours')],
                ])],
            ]),
        ]);
    }
}
