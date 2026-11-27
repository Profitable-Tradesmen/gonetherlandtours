<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Package;
use App\Models\Page;
use App\Models\TourType;
use App\Support\Seo;

/** Public landing page for one tour type (Self Drive, Chauffeur Driven, Coach Tours) at /tours/{slug}. */
class TourTypeController extends Controller
{
    /** /ways-to-travel: the three tour types side by side. */
    public function compare()
    {
        $intro = 'Drive yourself, ride with a private driver-guide or join a small group. Here is how they compare.';
        // Answers per tour type slug. A tour type without answers gets empty cells.
        $rows = [
            'Who drives' => ['self-drive' => 'You do, in your own rental car', 'chauffeur-driven' => 'A private driver-guide', 'coach-tours' => 'A professional coach driver'],
            'Who you travel with' => ['self-drive' => 'Just your own party', 'chauffeur-driven' => 'Just your own party', 'coach-tours' => 'A small group and a guide'],
            'Daily pace' => ['self-drive' => 'Set by you', 'chauffeur-driven' => 'Flexible, agreed with your driver-guide', 'coach-tours' => 'A set plan with free time'],
            'Guiding' => ['self-drive' => 'Route notes and daily suggestions', 'chauffeur-driven' => 'Local stories at each stop', 'coach-tours' => 'A guide with the group'],
            'Parking and navigation' => ['self-drive' => 'Yours, with our tips', 'chauffeur-driven' => 'Handled for you', 'coach-tours' => 'Handled for you'],
            'Suits' => ['self-drive' => 'Independent travelers who like to linger', 'chauffeur-driven' => 'Comfort, families and special trips', 'coach-tours' => 'First-time visitors and solo travelers'],
        ];

        return view('pages.ways-to-travel', [
            'types' => \App\Support\SiteData::tourTypes(),
            'rows' => $rows,
            'intro' => $intro,
            'seo' => Seo::make([
                'title' => 'Ways to Travel the Netherlands | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => $intro,
                'breadcrumbs' => [['Home', url('/')], ['Ways to travel', route('ways-to-travel')]],
            ]),
        ]);
    }

    public function show(TourType $tourType)
    {
        abort_unless($tourType->is_active, 404);
        $name = $tourType->name;
        // The companion page (Admin > Pages > "<tour type> page") holds the sections under the hero; without it the older fixed layout is used.
        $page = Page::where('slug', 'tour-'.$tourType->slug)->where('is_system', true)->first();
        $sections = $page ? $page->visibleSections() : [];
        $faqSection = collect($sections)->firstWhere('type', 'faq');
        $faqs = $sections
            ? ($faqSection ? Faq::active()->when((int) $faqSection['d']['limit'] > 0, fn ($q) => $q->take((int) $faqSection['d']['limit']))->get() : collect())
            : Faq::active()->take(4)->get();

        return view('pages.tour-type-show', [
            'tourType' => $tourType,
            'packages' => Package::published()->where('tour_type_id', $tourType->id)->with(['tourType', 'destination', 'images'])->orderBy('sort_order')->get(),
            'faqs' => $faqs,
            'sections' => $sections,
            'seo' => Seo::make([
                'title' => $tourType->seo_title ?: $name.' Netherlands Tours | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => $tourType->meta_description ?: $tourType->summary,
                'image' => $tourType->image,
                'noindex' => $tourType->noindex,
                'breadcrumbs' => [['Home', url('/')], ['Packages', route('packages.index')], [$name, route('tour-types.show', $tourType)]],
                'jsonld' => $faqs->isNotEmpty() ? [Seo::faqPage($faqs->map->only('question', 'answer'))] : [],
            ]),
        ]);
    }
}
