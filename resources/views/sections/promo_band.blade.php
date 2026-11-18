<section class="relative isolate overflow-hidden bg-sand py-16 sm:py-24" aria-labelledby="pb-{{ $sid }}">
    <span class="absolute -left-24 top-1/3 -z-10 h-72 w-72 rounded-full bg-brand/10" aria-hidden="true"></span>
    <span class="absolute -right-20 -top-16 -z-10 h-80 w-80 rounded-full bg-accent/15" aria-hidden="true"></span>
    <div class="container-site grid items-center gap-8 lg:grid-cols-[1fr_auto] lg:gap-16">
        <div class="max-w-2xl" data-reveal="left">
            @if ($d['eyebrow'])<p class="label-caps inline-flex items-center gap-3 text-brand-700"><span class="h-px w-7 bg-brand-700" aria-hidden="true"></span><span class="h-2 w-2 rounded-full bg-rust" aria-hidden="true"></span>{{ $d['eyebrow'] }}</p>@endif
            <h2 id="pb-{{ $sid }}" class="mt-3 text-3xl font-medium sm:text-4xl lg:text-5xl">{!! emphasize($d['heading'], 'text-brand-700') !!}</h2>
            @if ($d['text'])<p class="mt-5 text-base leading-relaxed text-ink/80 sm:text-lg">{{ $d['text'] }}</p>@endif
            @if (count($d['points']))
                <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold text-brand-900">
                    @foreach ($d['points'] as $pt)<li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-brand-700" />{{ $pt['text'] }}</li>@endforeach
                </ul>
            @endif
        </div>
        @if ($d['button_label'])<div data-reveal="right"><x-button class="!px-8 !py-4" @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></div>@endif
    </div>
</section>
