@props(['eyebrow' => null, 'title', 'subtitle' => null, 'center' => true, 'tag' => 'h2', 'light' => false])
<div {{ $attributes->merge(['class' => ($center ? 'text-center mx-auto ' : '').'max-w-3xl mb-10']) }}>
    @if ($eyebrow)
        <p class="label-caps mb-3 {{ $light ? 'text-accent' : 'text-rust' }}">{{ $eyebrow }}</p>
    @endif
    <{{ $tag }} class="text-3xl sm:text-4xl lg:text-5xl font-medium {{ $light ? 'text-white' : 'text-ink' }}">{{ $title }}</{{ $tag }}>
    @if ($subtitle)
        <p class="mt-4 text-base sm:text-lg {{ $light ? 'text-white/85' : 'text-ink/75' }}">{{ $subtitle }}</p>
    @endif
</div>
