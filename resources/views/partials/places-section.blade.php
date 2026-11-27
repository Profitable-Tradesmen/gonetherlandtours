{{-- A page section listing places. Expects $places, $title; optional $eyebrow, $intro, $bg ('sand' or 'white'), $id. --}}
@if ($places->isNotEmpty())
<section class="{{ ($bg ?? 'white') === 'sand' ? 'bg-sand' : 'bg-white' }} py-14 sm:py-20" aria-labelledby="{{ $id ?? 'places-h' }}">
    <div class="container-site">
        <x-section-heading :eyebrow="$eyebrow ?? 'Places to see'" :title="$title" :subtitle="$intro ?? null" :id="$id ?? 'places-h'" />
        @include('partials.places-grid')
    </div>
</section>
@endif
