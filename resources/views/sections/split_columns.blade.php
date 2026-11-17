<section class="py-16 sm:py-24">
    <div class="container-site grid gap-10 lg:grid-cols-2">
        <div>
            @if ($d['left_image'])<x-picture :src="$d['left_image']" :alt="$d['left_image_alt']" :width="900" :height="1100" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-card" />@endif
            @if ($d['left_heading'])<h2 class="mt-8 text-3xl font-medium sm:text-4xl">{!! emphasize($d['left_heading'], 'text-rust') !!}</h2>@endif
            @if ($d['left_text'])<p class="mt-4 leading-relaxed text-ink/80">{{ $d['left_text'] }}</p>@endif
        </div>
        <div>
            @if ($d['right_image'])<x-picture :src="$d['right_image']" :alt="$d['right_image_alt']" :width="900" :height="1100" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-card" />@endif
            @if ($d['right_heading'])<h2 class="mt-8 text-3xl font-medium sm:text-4xl">{!! emphasize($d['right_heading'], 'text-rust') !!}</h2>@endif
            @if ($d['right_text'])<p class="mt-4 leading-relaxed text-ink/80">{{ $d['right_text'] }}</p>@endif
            @if (count($d['right_bullets']))
                <ul class="mt-5 space-y-2 text-sm font-semibold text-brand-700">
                    @foreach ($d['right_bullets'] as $b)
                        <li class="flex gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-rust" /> <span>{{ $b['text'] }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>
