@props(['image' => null, 'alt' => '', 'width' => 1600, 'height' => 900, 'eyebrow' => null, 'heading', 'text' => null, 'crumbs' => null])
{{--
    Banner for every page except the homes: same look as the home hero (photo, golden-hour grade, white text on the left) with the main
    quiz on the right. Left column = content (breadcrumb, H1, text, extras in the slot), right column = <x-quiz> (same split as the home hero: 1fr | 1.1fr).
    Stacks on phones. Without an image it uses the home hero photo.
--}}
@php $photo = $image ?: 'images/hero-home.webp'; @endphp
<section class="hero-pad relative isolate flex items-center overflow-hidden bg-brand-900 text-white">
    <x-picture :src="$photo" :alt="$image ? $alt : ''" :width="$width" :height="$height" priority sizes="100vw" class="absolute inset-0 -z-30 h-full w-full object-cover" />
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent-600/75 via-rust/35 to-brand-900/45 mix-blend-soft-light"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-accent/30 via-transparent to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-ink/35 lg:bg-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 hidden bg-gradient-to-r from-ink/70 via-ink/35 to-transparent lg:block"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-b from-ink/45 to-transparent"></div>
    <div class="container-site grid w-full items-center gap-8 py-10 md:py-0 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:gap-x-12 lg:gap-y-8">
        <div class="min-w-0 [text-shadow:0_2px_3px_rgb(0_0_0/0.35),0_6px_28px_rgb(0_0_0/0.55)]">
            @if ($crumbs){{ $crumbs }}@endif
            @if ($eyebrow)<p class="hero-in label-caps text-accent">{{ $eyebrow }}</p>@endif
            <h1 style="--d:120ms" class="hero-in mt-3 text-4xl font-semibold sm:text-5xl xl:text-6xl">{!! $heading !!}</h1>
            @if ($text)<p style="--d:240ms" class="hero-in mt-4 max-w-xl text-base font-medium text-white sm:text-lg">{{ $text }}</p>@endif
            {{ $slot }}
        </div>
        <div style="--d:300ms" class="hero-in"><x-quiz :steps="\App\Support\SiteData::quizSteps()" /></div>
    </div>
</section>
