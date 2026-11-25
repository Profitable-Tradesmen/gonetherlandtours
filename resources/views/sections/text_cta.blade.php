@php
    $white = ($d['background'] ?? 'sand') === 'white';
    $navy = ! empty($d['image']) && ($d['overlay'] ?? '') === 'navy';   // photo under the same navy overlay as the numbers band, text in white
@endphp
<section class="{{ $navy ? 'bg-brand-900 text-white' : ($white ? 'bg-white' : 'bg-sand') }} relative isolate overflow-hidden py-16 sm:py-24" @if ($d['heading']) aria-labelledby="tc-{{ $sid }}" @endif>
    @if (! empty($d['image']))
        <x-picture :src="$d['image']" :alt="''" :width="2000" :height="1116" sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover" />
        @if ($navy)
            <span class="pointer-events-none absolute inset-0 -z-10 bg-brand-900/68" aria-hidden="true"></span>
            <span class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-brand-900/60 via-brand-900/20 to-brand-900/60" aria-hidden="true"></span>
        @else
            <span class="absolute inset-0 -z-10 {{ $white ? 'bg-white/85' : 'bg-sand/85' }}" aria-hidden="true"></span>
        @endif
    @endif
    <div class="container-site max-w-4xl text-center" data-reveal="up">
        @if (! empty($d['eyebrow']))
            <p class="label-caps {{ $navy ? 'text-accent' : 'text-ink' }}">{{ $d['eyebrow'] }}</p>
            <span class="mb-4 mt-3 flex items-center justify-center gap-2" aria-hidden="true"><i class="h-0.5 w-9 {{ $navy ? 'bg-accent' : 'bg-brand-900' }}"></i><i class="h-1.5 w-1.5 rotate-45 {{ $navy ? 'bg-accent' : 'bg-rust' }}"></i><i class="h-0.5 w-9 {{ $navy ? 'bg-accent' : 'bg-brand-900' }}"></i></span>
        @endif
        @if ($d['heading'])<h2 id="tc-{{ $sid }}" class="text-3xl font-medium sm:text-4xl">{!! emphasize($d['heading'], $navy ? 'text-accent' : 'text-rust') !!}</h2>@endif
        @if ($d['text'])<p class="mt-4 {{ $navy ? 'text-white/90' : 'text-ink/75' }}">{{ $d['text'] }}</p>@endif
        @if (count($d['buttons']))
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @foreach ($d['buttons'] as $b)<x-cta-button :variant="(! empty($d['first_button_gold']) && $loop->first) ? 'primary' : ($navy ? 'outline' : 'secondary')" :label="$b['label']" :url="$b['url']" class="{{ ($navy && ! (! empty($d['first_button_gold']) && $loop->first)) ? 'text-white' : '' }}" />@endforeach
            </div>
        @endif
    </div>
</section>
