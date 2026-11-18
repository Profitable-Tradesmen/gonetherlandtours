<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Support\Seo;

/** Renders any page of the site from its sections (all content is edited in Admin > Pages). */
class PageController extends Controller
{
    public function home()
    {
        return $this->render(Page::bySlug('home'));
    }

    public function about()
    {
        return $this->render(Page::bySlug('about'));
    }

    public function contact()
    {
        return $this->render(Page::bySlug('contact'));
    }

    public function faq()
    {
        return $this->render(Page::bySlug('faq'));
    }

    public function testimonials()
    {
        return $this->render(Page::bySlug('testimonials'));
    }

    public function legal(string $slug)
    {
        return $this->render(Page::bySlug($slug));
    }

    /** Custom pages created in Admin > Pages live at /{slug}. System pages have their own fixed URLs. */
    public function custom(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_system', false)->firstOrFail();

        return $this->render($page);
    }

    private function render(Page $page)
    {
        // Drafts are hidden from visitors but previewable by logged-in admins (Admin > Pages > View page).
        $draft = ! $page->is_published && $page->slug !== 'home';
        abort_if($draft && ! \Illuminate\Support\Facades\Auth::guard('admin')->check(), 404);
        $brand = setting('brand_name', 'GoNetherlandTours');

        $jsonld = [];
        foreach ($page->visibleSections() as $s) {
            if ($s['type'] === 'faq') {
                $faqs = Faq::active()->when((int) $s['d']['limit'] > 0, fn ($q) => $q->take((int) $s['d']['limit']))->get();
                if ($faqs->isNotEmpty()) {
                    $jsonld[] = Seo::faqPage($faqs->map->only('question', 'answer'));
                }
                break; // one FAQPage block per page
            }
        }

        $firstType = $page->visibleSections()[0]['type'] ?? null;

        return view('pages.show', [
            'page' => $page,
            'overlayHeader' => false,   // solid navy header on every page (reference design); heroes start below it
            'draft' => $draft,
            'sections' => $page->visibleSections(),
            'seo' => Seo::make([
                'title' => $page->seo_title ?: $page->title.' | '.$brand,
                'description' => $page->meta_description ?: setting('default_meta_description'),
                'image' => $page->social_image ?: null,
                'noindex' => $page->noindex || $draft,
                'jsonld' => $jsonld,
                'canonical' => $page->url(),
                'breadcrumbs' => $page->slug === 'home' ? [] : [['Home', url('/')], [$page->title, $page->url()]],
            ]),
        ]);
    }
}
