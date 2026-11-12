<?php

namespace App\Support;

/**
 * Builds the per-page SEO array consumed by layouts/app.blade.php:
 * title, description, canonical, image, noindex, og type and JSON-LD blocks.
 */
class Seo
{
    public static function make(array $o = []): array
    {
        $brand = setting('brand_name', 'GoNetherlandTours');
        $img = $o['image'] ?? setting('default_social_image') ?: 'images/og-default.webp';

        $page = [
            'title' => $o['title'] ?? setting('default_seo_title', $brand),
            'description' => $o['description'] ?? setting('default_meta_description', ''),
            'canonical' => $o['canonical'] ?? url()->current(),
            'image' => str_starts_with((string) $img, 'http') ? $img : asset($img),
            // SITE_NOINDEX=true (preview/staging) forces noindex on every page.
            'noindex' => config('gnt.site_noindex') || (bool) ($o['noindex'] ?? false),
            'type' => $o['type'] ?? 'website',
            'jsonld' => [self::organization()],
        ];

        if (! empty($o['breadcrumbs'])) {
            $page['jsonld'][] = self::breadcrumbs($o['breadcrumbs']);
        }
        foreach ($o['jsonld'] ?? [] as $block) {
            $page['jsonld'][] = $block;
        }

        return $page;
    }

    public static function organization(): array
    {
        $same = array_values(array_filter([setting('social_facebook'), setting('social_instagram'), setting('social_youtube')]));
        $org = [
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            'name' => setting('brand_name', 'GoNetherlandTours'),
            'url' => url('/'),
            'logo' => asset(setting('logo') ?: 'images/logo.webp'),
            'telephone' => setting('phone'),
            'email' => setting('email'),
            'address' => setting('address') ? ['@type' => 'PostalAddress', 'streetAddress' => setting('address')] : null,
            'sameAs' => $same ?: null,
        ];

        return array_filter($org, fn ($v) => $v !== null && $v !== '');
    }

    /** @param array<int,array{0:string,1:string}> $crumbs [[name, url], ...] */
    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn ($c, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1],
            ])->all(),
        ];
    }

    public static function faqPage($faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f['answer'])],
            ])->values()->all(),
        ];
    }
}
