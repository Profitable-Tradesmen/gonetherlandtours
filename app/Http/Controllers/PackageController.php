<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TourType;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $type = TourType::where('slug', $request->query('type'))->first();
        $destination = Destination::where('slug', $request->query('destination'))->first();

        $packages = Package::published()->with(['tourType', 'images'])
            ->when($type, fn ($q) => $q->where('tour_type_id', $type->id))
            ->when($destination, fn ($q) => $q->where('destination_id', $destination->id))
            ->orderBy('sort_order')->get();

        $h1 = $type ? $type->name.' Tour Packages' : 'Netherlands Tour Packages';

        return view('pages.packages-index', [
            'packages' => $packages,
            'tourTypes' => TourType::active()->get(),
            'destinations' => Destination::active()->get(),
            'h1' => $h1,
            'seo' => Seo::make([
                'title' => $h1.' | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => 'Browse '.strtolower($h1).' and get a free, tailored quote.',
                // Filtered views would duplicate the main listing: canonical to the unfiltered page.
                'canonical' => route('packages.index'),
                'noindex' => false,
                'breadcrumbs' => [['Home', url('/')], ['Packages', route('packages.index')]],
            ]),
        ]);
    }

    /** AJAX endpoint for the "Search by location" popup: returns an HTML fragment of matching packages (no page reload). */
    public function search(Request $request)
    {
        $term = trim(Str::limit((string) $request->query('q', ''), 80, ''));
        $like = '%'.addcslashes($term, '%_\\').'%';

        $packages = $term === '' ? collect() : Package::published()->with(['tourType', 'destination', 'images'])
            ->where(fn ($w) => $w
                ->whereHas('destination', fn ($d) => $d->where('name', 'like', $like))
                ->orWhere('title', 'like', $like)
                ->orWhere('summary', 'like', $like))
            ->orderBy('sort_order')->limit(12)->get();

        return view('partials.package-search-results', ['packages' => $packages, 'term' => $term]);
    }

    public function show(Package $package)
    {
        abort_unless($package->is_published, 404);
        $package->load(['tourType', 'destination', 'images']);

        // Related: only other published packages in the same category (tour type). No filler from other categories.
        $related = $package->tour_type_id
            ? Package::published()->with(['tourType', 'destination', 'images'])->whereKeyNot($package->id)
                ->where('tour_type_id', $package->tour_type_id)->orderBy('sort_order')->limit(3)->get()
            : collect();

        $cover = $package->images->first();
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $package->title,
            'description' => $package->summary,
            'url' => route('packages.show', $package),
            'touristType' => $package->tourType?->name,
            'image' => $cover ? $cover->url() : null,
            'offers' => $package->price_from ? [
                '@type' => 'Offer', 'price' => (string) $package->price_from, 'priceCurrency' => $package->currency,
                'url' => route('packages.show', $package),
            ] : null,
            'provider' => ['@type' => 'TravelAgency', 'name' => setting('brand_name', 'GoNetherlandTours')],
        ]);

        return view('pages.package-show', [
            'package' => $package,
            'related' => $related,
            'seo' => Seo::make([
                'title' => $package->seo_title ?: $package->title.' | '.setting('brand_name', 'GoNetherlandTours'),
                'description' => $package->meta_description ?: $package->summary,
                'image' => $package->social_image ?: ($cover ? $cover->url() : null),
                'noindex' => $package->noindex,
                'type' => 'article',
                'breadcrumbs' => [['Home', url('/')], ['Packages', route('packages.index')], [$package->title, route('packages.show', $package)]],
                'jsonld' => array_filter([$schema, $package->faqs ? Seo::faqPage($package->faqs) : null]),
            ]),
        ]);
    }
}
