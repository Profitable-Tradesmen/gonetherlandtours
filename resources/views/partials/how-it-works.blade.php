{{-- Three planning steps, used on tour pages and the "Ways to travel" page. Optional $bg ('sand' or 'white'). --}}
<section class="{{ ($bg ?? 'sand') === 'white' ? 'bg-white' : 'bg-sand' }} py-14 sm:py-20" aria-labelledby="how-h">
    <div class="container-site">
        <x-section-heading eyebrow="From idea to itinerary" title="How *planning works*" id="how-h" />
        <ol class="grid gap-5 md:grid-cols-3">
            @foreach ([
                ['Share your ideas', 'Send your dates, group size and what you would love to see. It takes about a minute.'],
                ['Shape the route', 'A travel specialist replies with a suggested plan and a price in euros. Change anything until it feels right.'],
                ['Confirm and go', 'Your itinerary and price are confirmed in writing, with help available before and during your trip.'],
            ] as $n => [$title, $copy])
                <li class="rounded-2xl border border-ink/10 bg-white p-6 shadow-sm sm:p-7">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-900 font-display text-xl text-accent" aria-hidden="true">{{ $n + 1 }}</span>
                    <h3 class="mt-4 text-xl font-medium">{{ $title }}</h3>
                    <p class="mt-2 leading-relaxed text-ink/80">{{ $copy }}</p>
                </li>
            @endforeach
        </ol>
        <p class="mt-9 text-center"><x-button @click="$store.modal.show()">Start planning <x-icon name="arrow" class="h-4 w-4" /></x-button></p>
    </div>
</section>
