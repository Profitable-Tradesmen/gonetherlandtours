@php
    $left = ($d['image_side'] ?? 'right') === 'left';
    $paras = array_values(array_filter(preg_split('/\R{2,}/', trim((string) $d['text'])), fn ($p) => trim($p) !== ''));
@endphp
<section class="{{ ($d['background'] ?? 'sand') === 'white' ? 'bg-white' : 'bg-sand' }} py-16 sm:py-24 lg:py-28" aria-labelledby="ti-{{ $sid }}">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2 lg:gap-20">
        <div class="{{ $left ? 'lg:order-2' : '' }}" data-reveal="{{ $left ? 'right' : 'left' }}">
            <h2 id="ti-{{ $sid }}" class="text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-rust') !!}</h2>
            @if ($d['quote'])<p class="mt-5 text-lg font-medium leading-snug text-brand-700">{{ $d['quote'] }}</p>@endif
            <div class="mt-6 space-y-4 leading-relaxed text-ink/80">
                @foreach ($paras as $p)<p>{{ $p }}</p>@endforeach
            </div>
            @if ($d['button_label'])<p class="mt-8"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
        </div>
        @if ($d['image'])
            <div class="{{ $left ? 'lg:order-1' : '' }}" data-reveal="{{ $left ? 'left' : 'right' }}">
                <x-picture :src="$d['image']" :alt="$d['image_alt']" :width="900" :height="1100" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-card lg:aspect-[5/4]" sizes="(min-width:1024px) 50vw, 100vw" />
            </div>
        @endif
    </div>
</section>
