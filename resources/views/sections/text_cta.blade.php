@php $white = ($d['background'] ?? 'sand') === 'white'; @endphp
<section class="{{ $white ? 'bg-white' : 'bg-sand' }} relative isolate overflow-hidden py-16 sm:py-24" @if ($d['heading']) aria-labelledby="tc-{{ $sid }}" @endif>
    @if (! empty($d['image']))
        <x-picture :src="$d['image']" :alt="''" :width="2000" :height="1116" sizes="100vw" class="absolute inset-0 -z-20 h-full w-full object-cover" />
        <span class="absolute inset-0 -z-10 {{ $white ? 'bg-white/85' : 'bg-sand/85' }}" aria-hidden="true"></span>
    @endif
    <div class="container-site max-w-3xl text-center" data-reveal="up">
        @if (! empty($d['eyebrow']))
            <p class="label-caps text-ink">{{ $d['eyebrow'] }}</p>
            <span class="mb-4 mt-3 flex items-center justify-center gap-2" aria-hidden="true"><i class="h-0.5 w-9 bg-brand-900"></i><i class="h-1.5 w-1.5 rotate-45 bg-rust"></i><i class="h-0.5 w-9 bg-brand-900"></i></span>
        @endif
        @if ($d['heading'])<h2 id="tc-{{ $sid }}" class="text-3xl font-medium sm:text-4xl">{!! emphasize($d['heading'], 'text-rust') !!}</h2>@endif
        @if ($d['text'])<p class="mt-4 text-ink/75">{{ $d['text'] }}</p>@endif
        @if (count($d['buttons']))
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @foreach ($d['buttons'] as $b)<x-cta-button :variant="(! empty($d['first_button_gold']) && $loop->first) ? 'primary' : 'secondary'" :label="$b['label']" :url="$b['url']" />@endforeach
            </div>
        @endif
    </div>
</section>
