{{-- Three photo cards that send a visitor onwards (used on the thank-you and "page not found" screens). --}}
<section class="bg-sand py-14 sm:py-20" aria-labelledby="explore-h">
    <div class="container-site">
        <x-section-heading eyebrow="Keep exploring" :title="$title ?? 'Where to *next?*'" id="explore-h" />
        <ul class="grid gap-5 sm:grid-cols-3">
            @foreach ([
                ['Ways to travel', 'Self drive, chauffeur driven or small-group coach.', route('ways-to-travel'), 'images/tour-self-drive-aerial.webp'],
                ['Destinations', 'Cities, bulb fields and the Zeeland coast.', route('destinations.index'), 'images/places/kinderdijk.webp'],
                ['Travel journal', 'Guides and ideas for planning your trip.', route('blog.index'), 'images/places/leiden.webp'],
            ] as [$name, $copy, $url, $photo])
                <li>
                    <a href="{{ $url }}" class="group relative isolate flex h-64 flex-col justify-end overflow-hidden rounded-2xl bg-brand-900 p-6 text-white shadow-card">
                        <x-picture :src="$photo" alt="" :width="1600" :height="1067" sizes="(min-width:640px) 33vw, 100vw" class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        <span class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/90 via-ink/45 to-transparent" aria-hidden="true"></span>
                        <span class="font-display text-2xl font-medium">{{ $name }}</span>
                        <span class="mt-1 text-sm text-white/90">{{ $copy }}</span>
                        <span class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-accent">Explore <x-icon name="arrow" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
