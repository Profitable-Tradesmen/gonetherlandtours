<?php

namespace App\Http\Controllers;

use App\Models\TravelStyle;
use App\Support\Seo;

class TravelStyleController extends Controller
{
    /** /travel-styles: every active style as a photo card. */
    public function index()
    {
        $intro = 'Choose what you love most about the Netherlands and we will shape the route around it.';

        return view('pages.travel-styles-index', [
            'styles' => TravelStyle::active()->get(),
            'intro' => $intro,
            'seo' => Seo::make([
                'title' => 'Netherlands Travel Styles | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => $intro,
                'breadcrumbs' => [['Home', url('/')], ['Travel styles', route('travel-styles.index')]],
            ]),
        ]);
    }

    public function show(TravelStyle $travelStyle)
    {
        abort_unless($travelStyle->is_active, 404);

        return view('pages.travel-style-show', [
            'style' => $travelStyle,
            'packages' => $travelStyle->tours(),
            'others' => TravelStyle::active()->where('id', '!=', $travelStyle->id)->get(),
            'seo' => Seo::make([
                'title' => ($travelStyle->seo_title ?: $travelStyle->name.' Tours').' | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => $travelStyle->meta_description ?: $travelStyle->summary,
                'image' => $travelStyle->image,
                'noindex' => $travelStyle->noindex,
                'breadcrumbs' => [['Home', url('/')], ['Travel styles', route('travel-styles.index')], [$travelStyle->name, route('travel-styles.show', $travelStyle)]],
            ]),
        ]);
    }
}
