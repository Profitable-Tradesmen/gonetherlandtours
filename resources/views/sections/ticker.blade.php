@if (count($d['items']))
<div class="overflow-hidden bg-brown py-3 text-white" role="region" aria-label="Why travel with us">
    <div class="marquee flex w-max gap-10">
        @foreach (range(1, 2) as $copy)
            @foreach ($d['items'] as $item)
                <span class="label-caps whitespace-nowrap" @if ($copy === 2) aria-hidden="true" @endif>{{ $item['text'] }} <span class="text-accent">&#10022;</span></span>
            @endforeach
        @endforeach
    </div>
</div>
@endif
