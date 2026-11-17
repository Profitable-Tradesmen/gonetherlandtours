@php
    $featured = \App\Models\Package::published()->where('is_featured', true)->with(['tourType', 'images'])->orderBy('sort_order')->take(6)->get();
    $types = \App\Models\TourType::active()->get();
@endphp
@if ($featured->isNotEmpty())
<section class="bg-sand py-16 sm:py-24" aria-labelledby="pk-{{ $sid }}" x-data="{ tab: 'all' }">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'pk-'.$sid" />
        <div class="mb-8 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-center" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'all'" class="btn w-full !px-3 !py-2 !text-[0.7rem] sm:w-auto sm:!px-6 sm:!text-[0.8rem]" :class="tab === 'all' ? 'btn-secondary' : 'btn-outline text-brand-700'" @click="tab = 'all'">{{ $d['all_label'] }}</button>
            @foreach ($types as $t)
                <button type="button" role="tab" :aria-selected="tab === '{{ $t->slug }}'" class="btn w-full !px-3 !py-2 !text-[0.7rem] sm:w-auto sm:!px-6 sm:!text-[0.8rem]" :class="tab === '{{ $t->slug }}' ? 'btn-secondary' : 'btn-outline text-brand-700'" @click="tab = '{{ $t->slug }}'">{{ $t->name }}</button>
            @endforeach
        </div>
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $p)
                <div x-show="tab === 'all' || tab === '{{ $p->tourType?->slug }}'" x-transition.opacity>@include('partials.package-card', ['package' => $p])</div>
            @endforeach
        </x-slider>
        @if ($d['button_label'])<p class="mt-10 text-center"><x-button variant="secondary" :href="route('packages.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
@endif
