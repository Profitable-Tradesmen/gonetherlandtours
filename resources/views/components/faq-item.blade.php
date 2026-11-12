@props(['question'])
{{-- Native <details>: works without JavaScript and is keyboard accessible. --}}
<details {{ $attributes->merge(['class' => 'group rounded-xl border border-sand-200 bg-white px-5 py-4']) }}>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-ink [&::-webkit-details-marker]:hidden">
        <span>{{ $question }}</span>
        <x-icon name="chevron" class="h-5 w-5 shrink-0 text-rust transition-transform group-open:rotate-180" />
    </summary>
    <div class="mt-3 text-ink/80 leading-relaxed">{{ $slot }}</div>
</details>
