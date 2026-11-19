<section class="bg-brown py-16 text-white sm:py-24 lg:py-32" aria-labelledby="vf-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-24 xl:gap-28">
        <div data-reveal="left" class="lg:pl-4">
            @if ($d['eyebrow'])<p class="label-caps text-accent">{{ $d['eyebrow'] }}</p>@endif
            <h2 id="vf-{{ $sid }}" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            @if ($d['text'])<p class="mt-4 text-white/85">{{ $d['text'] }}</p>@endif
        </div>
        <div class="relative overflow-hidden rounded-2xl shadow-card" x-data="{ playing: false }" data-reveal="zoom" style="--d:150ms">
            <x-video :name="$d['video_name'] ?: 'homepage_banner'" :poster="$d['poster'] ?: 'images/hero-video-poster.webp'" :alt="$d['poster_alt']" :background="false" class="aspect-video w-full object-cover" />
        </div>
    </div>
</section>
