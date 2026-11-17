<section class="relative isolate overflow-hidden py-24 text-white sm:py-32" aria-labelledby="fb-{{ $sid }}">
    @if ($d['image'])<x-picture :src="$d['image']" :alt="$d['image_alt']" :width="1800" :height="900" class="kenburns absolute inset-0 -z-20 h-full w-full object-cover" />@endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink/80 via-ink/50 to-transparent"></div>
    <div class="container-site">
        <div class="max-w-xl" data-reveal="left">
            @if ($d['eyebrow'])<p class="label-caps text-accent">{{ $d['eyebrow'] }}</p>@endif
            <h2 id="fb-{{ $sid }}" class="mt-3 text-4xl font-medium sm:text-5xl lg:text-6xl">{!! emphasize($d['heading'], 'text-accent') !!}</h2>
            <ul class="mt-6 space-y-2 font-semibold">
                @foreach ($d['bullets'] as $li)
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-5 w-5 text-accent" /> {{ $li['text'] }}</li>
                @endforeach
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                @if ($d['primary_label'])<x-button @click="$store.modal.show()">{{ $d['primary_label'] }}</x-button>@endif
                @if ($d['secondary_label'])<x-cta-button variant="outline" :label="$d['secondary_label']" :url="$d['secondary_url']" class="text-white" />@endif
            </div>
        </div>
    </div>
</section>
