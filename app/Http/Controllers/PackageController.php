<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TourType;
use App\Support\Seo;
use Illuminate\Http\Request;

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
        $filtered = $type || $destination;

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

    public function show(Package $package)
    {
        abort_unless($package->is_published, 404);
        $package->load(['tourType', 'destination', 'images']);

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
