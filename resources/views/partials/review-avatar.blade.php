{{-- Round traveler photo, or the first letter of the name on a gold circle. $size = Tailwind size + text classes, e.g. "h-12 w-12 text-lg". --}}
<span class="{{ $size ?? 'h-12 w-12 text-lg' }} inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-accent font-display text-ink {{ $ring ?? '' }}">
    @if (! empty($t->image))<x-picture :src="$t->image" :alt="$t->image_alt ?? ''" :width="240" :height="240" class="h-full w-full object-cover" sizes="140px" />@else{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($t->name, 0, 1)) }}@endif
</span>
