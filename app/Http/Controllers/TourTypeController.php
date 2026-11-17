<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Package;
use App\Models\TourType;
use App\Support\Seo;

/** Public landing page for one tour type (Self Drive, Chauffeur Driven, Coach Tours) at /tours/{slug}. */
class TourTypeController extends Controller
{
    public function show(TourType $tourType)
    {
        abort_unless($tourType->is_active, 404);
        $faqs = Faq::active()->take(4)->get();
        $name = $tourType->name;

        return view('pages.tour-type-show', [
            'tourType' => $tourType,
            'packages' => Package::published()->where('tour_type_id', $tourType->id)->with(['tourType', 'images'])->orderBy('sort_order')->get(),
            'faqs' => $faqs,
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
