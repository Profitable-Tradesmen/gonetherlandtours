@php $posts = \App\Models\BlogPost::published()->limit(max(1, (int) $d['count']))->get(); $wide = $posts->count() >= 3; @endphp
@if ($posts->isNotEmpty())
<section class="bg-sand py-16 sm:py-24" aria-labelledby="bl-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="($d['intro'] ?? '') ?: null" :id="'bl-'.$sid" />
        <x-slider class="grid gap-6 {{ $wide ? 'md:grid-cols-3' : 'mx-auto max-w-4xl md:grid-cols-2' }}">
            @foreach ($posts as $post)
                @include('partials.blog-card', ['post' => $post])
            @endforeach
        </x-slider>
        @if ($d['button_label'])<p class="mt-10 text-center"><x-button variant="secondary" :href="route('blog.index')">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
@endif
