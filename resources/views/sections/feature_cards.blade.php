@if (count($d['items']))
<section class="bg-brand-900 py-12 text-white sm:py-16" aria-label="What sets us apart">
    <div class="container-site">
        <x-slider class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($d['items'] as $c)
                <div class="rounded-2xl bg-white p-6 text-center text-ink shadow-card">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-brand-700"><x-icon :name="$c['icon'] ?: 'check'" class="h-7 w-7" /></span>
                    <h2 class="mt-3 font-sans text-base font-bold">{{ $c['title'] }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink/75">{{ $c['text'] }}</p>
                </div>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
