<section class="relative isolate overflow-hidden py-20 text-center text-white sm:py-28" aria-labelledby="cb-{{ $sid }}">
    @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="2000" :height="1116" class="kenburns absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    <div class="absolute inset-0 -z-10 bg-brand-900/75"></div>
    <div class="container-site max-w-3xl" data-reveal="up">
        <h2 id="cb-{{ $sid }}" class="text-3xl font-medium sm:text-5xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
        @if ($d['text'])<p class="mt-4 text-lg text-white/90">{{ $d['text'] }}</p>@endif
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            @if ($d['button_label'])<x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button>@endif
            @if ($d['show_phone'] && setting('phone'))<x-button variant="outline" :href="'tel:'.preg_replace('/[^+\d]/', '', setting('phone'))">{{ trim($d['phone_label'].' '.setting('phone')) }}</x-button>@endif
        </div>
    </div>
</section>
