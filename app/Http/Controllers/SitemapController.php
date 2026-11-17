<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $urls = [[url('/'), now()], [route('packages.index'), now()], [route('blog.index'), now()]];

        // Every published, indexable page (system pages and custom pages made in Admin > Pages). FAQ/testimonials come from here too.
        foreach (Page::where('noindex', false)->where('is_published', true)->where('slug', '!=', 'home')->get() as $p) {
            $urls[] = [$p->url(), $p->updated_at];
        }
        foreach (\App\Models\TourType::active()->where('noindex', false)->get() as $t) {
            $urls[] = [route('tour-types.show', $t), $t->updated_at];
        }
        foreach (Destination::active()->where('noindex', false)->get() as $d) {
            $urls[] = [route('destinations.show', $d), $d->updated_at];
        }
        foreach (Package::published()->where('noindex', false)->get() as $p) {
            $urls[] = [route('packages.show', $p), $p->updated_at];
        }

        foreach (BlogPost::published()->where('noindex', false)->get() as $b) {
            $urls[] = [route('blog.show', $b), $b->updated_at];
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $body = config('gnt.site_noindex')
            ? "User-agent: *\nDisallow: /\n"   // preview/staging: keep everything out of search engines
            : "User-agent: *\nDisallow: /admin\nDisallow: /thank-you\nAllow: /\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
