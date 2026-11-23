<section class="bg-brand-900 py-16 text-white sm:py-24" aria-labelledby="sb-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['text'] ?: null" :id="'sb-'.$sid" light />
        @if (count($d['items']))
        <dl class="grid grid-cols-2 gap-8 lg:grid-cols-{{ min(count($d['items']), 4) }}">
            @foreach ($d['items'] as $i => $it)
                <div class="text-center" data-reveal="up" style="--d:{{ $i * 100 }}ms">
                    <dt class="order-2 mt-2 text-sm font-semibold uppercase tracking-widest text-white/85">{{ $it['label'] }}</dt>
                    <dd class="font-display text-5xl font-medium text-accent sm:text-6xl">{{ $it['value'] }}</dd>
                </div>
            @endforeach
        </dl>
        @endif
        @if ($d['button_label'])<p class="mt-12 text-center"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
    </div>
</section>
