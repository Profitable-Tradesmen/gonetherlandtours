{{-- Bottom bar of a mega menu: a round star, a short tagline and up to two buttons. Expects $bar (tagline, packages, quote, optional all = [label, href]). --}}
<div class="mt-6 border-t border-white/15">
    <div class="container-site flex items-center justify-between gap-4 py-4">
        <p class="flex items-center gap-3 text-sm font-semibold"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/40 text-accent"><x-icon name="star" class="h-4 w-4" /></span>{{ $bar['tagline'] }}</p>
        <div class="flex shrink-0 gap-3">
            @if (! empty($bar['all']['label']))<a href="{{ $bar['all']['href'] }}" class="btn btn-outline !py-2.5 text-white" @click="dd = null">{{ $bar['all']['label'] }}</a>@endif
            @if (! empty($bar['packages']))<a href="{{ route('packages.index') }}" class="btn btn-outline !py-2.5 text-white" @click="dd = null">{{ $bar['packages'] }}</a>@endif
            @if (! empty($bar['quote']))<x-button class="!py-2.5" @click="dd = null; $store.modal.show()">{{ $bar['quote'] }}</x-button>@endif
        </div>
    </div>
</div>
