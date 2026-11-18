@push('head')
    @if ($d['image'])<link rel="preload" as="image" href="{{ asset($d['image']) }}" @if ($sets = static_srcset($d['image'])) imagesrcset="{{ $sets }}" imagesizes="100vw" @endif fetchpriority="high">@endif
@endpush
<section class="relative isolate overflow-hidden bg-brand-900 text-center text-white {{ ! empty($overlayHeader) ? 'pb-16 pt-28 sm:pb-24 sm:pt-36' : 'py-16 sm:py-24' }}">
    @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="1920" :height="1080" class="absolute inset-0 -z-20 h-full w-full object-cover" priority />@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/50 to-ink/40"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-ink/60 to-transparent"></div>
    <div class="container-site max-w-4xl">
        <nav aria-label="Breadcrumb" class="mb-4 text-xs text-white/70"><a href="{{ route('home') }}" class="hover:text-accent">Home</a> / <span>{{ $page->title }}</span></nav>
        @if ($d['eyebrow'])<p class="label-caps text-accent">{{ $d['eyebrow'] }}</p>@endif
        <h1 class="mt-4 text-4xl font-medium sm:text-6xl">{!! emphasize($d['heading'] ?: $page->h1, 'text-accent') !!}</h1>
        @if ($d['text'])<p class="mx-auto mt-5 max-w-2xl text-base text-white/90 sm:text-lg">{{ $d['text'] }}</p>@endif
        @if ($d['button_label'])<p class="mt-7"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif

        @if (count($d['seasons']))
        <div class="mx-auto mt-10 max-w-3xl rounded-2xl border-2 border-accent bg-white p-5 text-ink shadow-card">
            @if ($d['season_prompt'])<p class="text-sm font-semibold">{{ $d['season_prompt'] }}</p>@endif
            <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($d['seasons'] as $s)
                    <li><button type="button" @click="$store.modal.show()" class="flex h-full w-full flex-col items-center gap-1 rounded-xl border border-sand-200 p-3 text-center transition hover:border-accent hover:bg-sand">
                        <x-icon :name="$s['icon'] ?: 'check'" class="h-7 w-7 text-brand-700" />
                        <span class="text-sm font-bold">{{ $s['name'] }}</span>
                        <span class="text-xs text-ink/70">{{ $s['note'] }}</span>
                    </button></li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (count($d['badges']))
        <ul class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm font-semibold" aria-label="Why book with us">
            @foreach ($d['badges'] as $b)
                <li class="flex items-center gap-2"><x-icon :name="$b['icon'] ?: 'check'" class="h-5 w-5 shrink-0 text-accent" /> {{ $b['text'] }}</li>
            @endforeach
        </ul>
        @endif
    </div>
</section>
