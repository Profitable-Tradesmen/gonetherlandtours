<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Package;
use App\Models\Page;
use App\Models\QuizStep;
use App\Models\Testimonial;
use App\Models\TourType;
use App\Support\Seo;

class PageController extends Controller
{
    private function seoFor(Page $page, array $extra = []): array
    {
        return Seo::make([
            'title' => $page->seo_title ?: $page->title.' | '.setting('brand_name', 'GoNetherlandTours'),
            'description' => $page->meta_description ?: setting('default_meta_description'),
            'image' => $page->social_image ?: null,
            'noindex' => $page->noindex,
        ] + $extra);
    }

    public function home()
    {
        $page = Page::bySlug('home');
        $faqs = Faq::active()->get();

        return view('pages.home', [
            'page' => $page,
            'quizSteps' => QuizStep::forQuiz(),
            'tourTypes' => TourType::active()->get(),
            'featured' => Package::published()->where('is_featured', true)->with(['tourType', 'images'])->orderBy('sort_order')->take(6)->get(),
            'testimonials' => Testimonial::active()->get(),
            'faqs' => $faqs,
            'seo' => $this->seoFor($page, ['jsonld' => $faqs->isNotEmpty() ? [Seo::faqPage($faqs->map->only('question', 'answer'))] : []]),
        ]);
    }

    public function about()
    {
        $page = Page::bySlug('about');

        return view('pages.content', ['page' => $page, 'seo' => $this->seoFor($page, ['breadcrumbs' => [['Home', url('/')], [$page->title, route('about')]]])]);
    }

    public function contact()
    {
        $page = Page::bySlug('contact');

        return view('pages.contact', ['page' => $page, 'seo' => $this->seoFor($page, ['breadcrumbs' => [['Home', url('/')], ['Contact', route('contact')]]])]);
    }

    public function legal(string $slug)
    {
        $page = Page::bySlug($slug);

        return view('pages.content', ['page' => $page, 'seo' => $this->seoFor($page, ['breadcrumbs' => [['Home', url('/')], [$page->title, url()->current()]]])]);
    }
}
