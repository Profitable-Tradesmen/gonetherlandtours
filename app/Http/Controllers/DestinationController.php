<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Support\Seo;

class DestinationController extends Controller
{
    /** /destinations: every active destination as a photo card. */
    public function index()
    {
        $destinations = Destination::active()->withCount(['packages' => fn ($q) => $q->published()])->get();

        return view('pages.destinations-index', [
            'destinations' => $destinations,
            'seo' => Seo::make([
                'title' => text('destinations_heading').' | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => text('destinations_intro'),
                'breadcrumbs' => [['Home', url('/')], [text('destinations_heading'), route('destinations.index')]],
            ]),
        ]);
    }

    public function show(Destination $destination)
    {
        abort_unless($destination->is_active, 404);

        return view('pages.destination-show', [
            'destination' => $destination,
            'packages' => Package::published()->where('destination_id', $destination->id)->with(['tourType', 'destination', 'images'])->orderBy('sort_order')->get(),
            'seo' => Seo::make([
                'title' => $destination->seo_title ?: $destination->name.' Tours | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => $destination->meta_description ?: $destination->summary,
                'image' => $destination->image,
                'noindex' => $destination->noindex,
                'breadcrumbs' => [['Home', url('/')], [$destination->name, route('destinations.show', $destination)]],
            ]),
        ]);
    }
}
