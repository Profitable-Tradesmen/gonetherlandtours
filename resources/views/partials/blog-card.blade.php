{{-- Blog post card (home page + /blog). Expects $post. --}}
@php $url = route('blog.show', $post); @endphp
<article class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card transition-transform duration-300 hover:-translate-y-1" data-reveal="up" style="--d:{{ isset($loop) ? ($loop->index % 3) * 120 : 0 }}ms">
    <a href="{{ $url }}" class="block overflow-hidden" tabindex="-1" aria-hidden="true">
        <x-picture :src="$post->image ?: 'images/feature_band.webp'" :alt="$post->image_alt ?? ''" :width="1600" :height="893" class="aspect-[3/2] w-full object-cover transition-transform duration-500 group-hover:scale-105" sizes="(min-width:768px) 50vw, 100vw" />
    </a>
    <div class="flex flex-1 flex-col p-6">
        @if ($post->label)<p class="label-caps text-rust">{{ $post->label }}</p>@endif
        <h3 class="mt-2 text-xl font-medium"><a href="{{ $url }}" class="hover:text-rust">{{ $post->title }}</a></h3>
        @if ($post->excerpt)<p class="mt-2 flex-1 text-sm text-ink/75">{{ $post->excerpt }}</p>@endif
        <p class="mt-3 text-sm font-bold"><a href="{{ $url }}" class="text-brand-700 underline underline-offset-4 hover:text-rust" aria-label="Read more: {{ $post->title }}">Read more</a></p>
    </div>
</article>
