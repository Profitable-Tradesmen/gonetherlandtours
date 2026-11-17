@if (count($d['items']))
<section class="py-16 sm:py-24" aria-labelledby="tm-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'tm-'.$sid" />
        <x-slider class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($d['items'] as $i => $m)
                <figure class="rounded-2xl bg-sand p-6 text-center" data-reveal="up" style="--d:{{ $i % 3 * 120 }}ms">
                    @if ($m['image'])
                        <x-picture :src="$m['image']" :alt="$m['image_alt'] ?: $m['name']" :width="224" :height="224" class="mx-auto h-28 w-28 rounded-full object-cover" />
                    @else
                        <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-brand-900 font-display text-3xl text-accent" aria-hidden="true">{{ $i + 1 }}</div>
                    @endif
                    <figcaption class="mt-4">
                        <p class="font-display text-2xl">{{ $m['name'] }}</p>
                        @if ($m['role'])<p class="label-caps mt-1 text-xs text-rust">{{ $m['role'] }}</p>@endif
                        @if ($m['bio'])<p class="mt-3 text-sm leading-relaxed text-ink/75">{{ $m['bio'] }}</p>@endif
                    </figcaption>
                </figure>
            @endforeach
        </x-slider>
    </div>
</section>
@endif
