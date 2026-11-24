@props(['eyebrow' => null, 'title', 'subtitle' => null, 'center' => true, 'tag' => 'h2', 'light' => false])
<div {{ $attributes->merge(['class' => ($center ? 'text-center mx-auto ' : '').'max-w-3xl mb-10']) }}>
    @if ($eyebrow)
        <p class="label-caps {{ $light ? 'text-accent' : 'text-ink' }}">{{ $eyebrow }}</p>
        {{-- a short dark rule with a small diamond (gold on dark bands) under the small label --}}
        <span class="mb-4 mt-3 flex items-center gap-2 {{ $center ? 'justify-center' : '' }}" aria-hidden="true"><i class="h-0.5 w-9 {{ $light ? 'bg-accent' : 'bg-brand-900' }}"></i><i class="h-1.5 w-1.5 rotate-45 {{ $light ? 'bg-accent' : 'bg-rust' }}"></i><i class="h-0.5 w-9 {{ $light ? 'bg-accent' : 'bg-brand-900' }}"></i></span>
    @endif
    <{{ $tag }} class="text-3xl sm:text-4xl lg:text-5xl font-medium {{ $light ? 'text-white' : 'text-ink' }}">{!! emphasize($title, $light ? 'text-accent' : 'text-rust') !!}</{{ $tag }}>
    @if ($subtitle)
        <p class="mt-4 text-base sm:text-lg {{ $light ? 'text-white/85' : 'text-ink/75' }}">{{ $subtitle }}</p>
    @endif
</div>
