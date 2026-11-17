<section class="bg-sand py-16 sm:py-20" @if ($d['heading']) aria-labelledby="tc-{{ $sid }}" @endif>
    <div class="container-site max-w-3xl text-center" data-reveal="up">
        @if ($d['heading'])<h2 id="tc-{{ $sid }}" class="text-3xl font-medium sm:text-4xl">{!! emphasize($d['heading'], 'text-rust') !!}</h2>@endif
        @if ($d['text'])<p class="mt-4 text-ink/75">{{ $d['text'] }}</p>@endif
        @if (count($d['buttons']))
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @foreach ($d['buttons'] as $b)<x-cta-button variant="secondary" :label="$b['label']" :url="$b['url']" />@endforeach
            </div>
        @endif
    </div>
</section>
