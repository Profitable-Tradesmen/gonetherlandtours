{{-- Five stars, $rating of them filled (gold). $class sets the size. Screen readers get the rating as text. --}}
@php $rating = max(1, min(5, (int) ($rating ?? 5))); @endphp
<span class="inline-flex items-center gap-0.5" role="img" aria-label="Rated {{ $rating }} out of 5">
    @for ($s = 1; $s <= 5; $s++)<x-icon name="star" :class="($class ?? 'h-5 w-5').' '.($s <= $rating ? 'fill-current text-accent' : 'text-current opacity-25')" />@endfor
</span>
