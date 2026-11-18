@props(['eyebrow' => null, 'title', 'subtitle' => null, 'center' => true, 'tag' => 'h2', 'light' => false])
<div {{ $attributes->merge(['class' => ($center ? 'text-center mx-auto ' : '').'max-w-3xl mb-10']) }}>
    @if ($eyebrow)
        {{-- small label with a short line and a dot in front of it --}}
        <p class="label-caps mb-3 inline-flex items-center gap-3 {{ $light ? 'text-accent' : 'text-brand-700' }}"><span class="h-px w-7 {{ $light ? 'bg-accent' : 'bg-brand-700' }}" aria-hidden="true"></span><span class="h-2 w-2 rounded-full bg-rust" aria-hidden="true"></span>{{ $eyebrow }}</p>
    @endif
    <{{ $tag }} class="text-3xl sm:text-4xl lg:text-5xl font-medium {{ $light ? 'text-white' : 'text-ink' }}">{!! emphasize($title, $light ? 'text-accent' : 'text-brand-700') !!}</{{ $tag }}>
    @if ($subtitle)
        <p class="mt-4 text-base sm:text-lg {{ $light ? 'text-white/85' : 'text-ink/75' }}">{{ $subtitle }}</p>
    @endif
</div>
