@props(['eyebrow' => null, 'title', 'subtitle' => null, 'center' => true, 'tag' => 'h2', 'light' => false])
<div {{ $attributes->merge(['class' => ($center ? 'text-center mx-auto ' : '').'max-w-3xl mb-10']) }}>
    @if ($eyebrow)
        <p class="label-caps {{ $light ? 'text-accent' : 'text-ink' }}">{{ $eyebrow }}</p>
        {{-- three short bars under the small label (brand blue, rust, brand blue) --}}
        <span class="mb-4 mt-2 flex gap-1.5 {{ $center ? 'justify-center' : '' }}" aria-hidden="true"><i class="h-[3px] w-7 {{ $light ? 'bg-white/70' : 'bg-brand-700' }}"></i><i class="h-[3px] w-7 {{ $light ? 'bg-accent' : 'bg-rust' }}"></i><i class="h-[3px] w-7 {{ $light ? 'bg-white/70' : 'bg-brand-700' }}"></i></span>
    @endif
    <{{ $tag }} class="text-3xl sm:text-4xl lg:text-5xl font-medium {{ $light ? 'text-white' : 'text-ink' }}">{!! emphasize($title, $light ? 'text-accent' : 'text-rust') !!}</{{ $tag }}>
    @if ($subtitle)
        <p class="mt-4 text-base sm:text-lg {{ $light ? 'text-white/85' : 'text-ink/75' }}">{{ $subtitle }}</p>
    @endif
</div>
