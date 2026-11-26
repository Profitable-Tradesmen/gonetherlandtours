@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
@php
    $pct = fn ($n) => $total ? round($n / $total * 100) : 0;
    $max = max(1, $series->max('n'));
    $pmax = max(1, $pipeline->max('n'));
    $card = 'rounded-2xl bg-white p-5 shadow-sm';
@endphp
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="font-display text-3xl">Dashboard</h1>
    <div class="inline-flex overflow-hidden rounded-full bg-white text-sm shadow-sm" role="group" aria-label="Period">
        @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days'] as $d => $label)
            <a href="{{ route('admin.dashboard', ['days' => $d]) }}" @class(['px-4 py-2 font-semibold', 'bg-brand-900 text-white' => $days === $d, 'text-ink hover:bg-sand' => $days !== $d]) @if ($days === $d) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </div>
</div>

@if ($stale)
    <div class="mt-5 rounded-xl border border-accent/60 bg-accent/10 px-4 py-3 text-sm" role="status">
        <p class="font-semibold">Needs your attention</p>
        <p class="mt-0.5">{{ $stale }} new {{ \Illuminate\Support\Str::plural('inquiry', $stale) }} {{ $stale === 1 ? 'has' : 'have' }} waited more than 24 hours. <a class="underline" href="{{ route('admin.enquiries.index', ['status' => 'new']) }}">Open {{ $stale === 1 ? 'it' : 'them' }}</a></p>
    </div>
@endif

<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="{{ $card }}"><p class="text-sm text-ink/70">Inquiries, last {{ $days }} days</p><p class="mt-2 font-display text-3xl text-brand-900">{{ $total }}</p><p class="mt-1 text-xs text-emerald-700">+{{ $new }} new</p></div>
    <a href="{{ route('admin.enquiries.index', ['status' => 'new']) }}" class="{{ $card }} hover:ring-2 hover:ring-accent"><p class="text-sm text-ink/70">New, not yet actioned</p><p class="mt-2 font-display text-3xl text-brand-900">{{ $newAll }}</p><p class="mt-1 text-xs text-ink/70">Open the inbox &rarr;</p></a>
    <div class="{{ $card }}"><p class="text-sm text-ink/70">From Google Ads</p><p class="mt-2 font-display text-3xl text-brand-900">{{ $ads }}</p><p class="mt-1 text-xs text-ink/70">{{ $pct($ads) }}% of inquiries</p></div>
    <div class="{{ $card }}"><p class="text-sm text-ink/70">Won</p><p class="mt-2 font-display text-3xl text-brand-900">{{ $won }}</p><p class="mt-1 text-xs text-ink/70">{{ $pct($won) }}% of inquiries in this period</p></div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
    <div class="{{ $card }} !p-6">
        <h2 class="font-display text-xl text-brand-900">Inquiries per day</h2>
        <p class="mt-1 text-right text-sm text-ink/70">max {{ $max }} / day</p>
        <div class="relative mt-1 flex h-44 items-end gap-px border-b border-sand-200" role="img" aria-label="Inquiries per day, highest day {{ $max }}">
            @foreach ([25, 50, 75] as $g)<span class="pointer-events-none absolute inset-x-0 border-t border-sand-200" style="bottom: {{ $g }}%"></span>@endforeach
            @foreach ($series as $s)
                <span class="relative flex-1 rounded-t bg-brand-900" style="height: {{ $s['n'] ? max(4, $s['n'] / $max * 100) : 0 }}%" title="{{ $s['date']->format('D j M') }}: {{ $s['n'] }}"></span>
            @endforeach
        </div>
        <div class="mt-3 flex justify-between text-sm text-ink/70"><span>{{ $series->first()['date']->format('D j M') }}</span><span>{{ $series->last()['date']->format('D j M') }}</span></div>
    </div>
    <div class="{{ $card }} !p-6">
        <h2 class="font-display text-xl text-brand-900">Pipeline</h2>
        <ul class="mt-3 space-y-3 text-sm">
            @foreach ($pipeline as $p)
                <li>
                    <div class="flex justify-between"><a class="underline" href="{{ route('admin.enquiries.index', ['status' => $p['key']]) }}">{{ $p['label'] }}</a><span>{{ $p['n'] }}</span></div>
                    <div class="mt-1 h-1.5 rounded-full bg-sand-200"><div class="h-1.5 rounded-full bg-brand-900" style="width: {{ $p['n'] ? max(3, $p['n'] / $pmax * 100) : 0 }}%"></div></div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([['Where they came from', 'Source / medium (latest touch)', $sources], ['Campaigns', 'utm_campaign', $campaigns], ['Forms', 'Which form was used', $forms], ['Popular journeys', 'Inquiries naming a package', $journeys]] as [$title, $sub, $rows])
        <div class="{{ $card }}">
            <h2 class="font-display text-lg text-brand-900">{{ $title }}</h2>
            <p class="text-xs text-rust">{{ $sub }}</p>
            @forelse ($rows as $label => $n)
                <div class="mt-3 text-sm"><div class="flex justify-between gap-2"><span class="truncate">{{ $label }}</span><span class="font-semibold">{{ $n }}</span></div><div class="mt-1 h-1.5 rounded-full bg-sand-200"><div class="h-1.5 rounded-full bg-accent" style="width: {{ $rows->max() ? $n / $rows->max() * 100 : 0 }}%"></div></div></div>
            @empty
                <p class="mt-3 text-sm text-ink/70">Nothing yet.</p>
            @endforelse
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
    <div class="min-w-0">
        <div class="flex items-end justify-between"><h2 class="font-display text-2xl text-brand-900">Recent inquiries</h2><a href="{{ route('admin.enquiries.index') }}" class="text-sm underline">View all</a></div>
        <div class="mt-3 overflow-x-auto rounded-2xl bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead><tr class="text-xs font-semibold text-brand-900"><th class="p-4">When</th><th class="p-4">Name</th><th class="p-4">Form</th><th class="p-4">Source</th><th class="p-4">Status</th></tr></thead>
                <tbody>@forelse ($recent as $e)
                    @php $t = (array) ($e->tracking_last ?: $e->tracking_first ?: []); $src = $t['utm_source'] ?? (! empty($t['referrer']) ? parse_url((string) $t['referrer'], PHP_URL_HOST) : null) ?: 'Direct'; @endphp
                    <tr class="border-t border-sand-200">
                        <td class="p-4 text-xs">{{ $e->created_at->diffForHumans() }}</td>
                        <td class="p-4"><a class="underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a><span class="block text-xs text-ink/70">{{ $e->email }}</span></td>
                        <td class="p-4 text-xs">{{ config('gnt.form_names.'.$e->form_name, $e->form_name) }}</td>
                        <td class="p-4 text-xs">{{ $src }}</td>
                        <td class="p-4"><span class="rounded-full bg-accent/25 px-2.5 py-1 text-xs font-semibold">{{ \App\Models\Enquiry::STATUSES[$e->status] ?? $e->status }}</span></td>
                    </tr>
                @empty<tr><td colspan="5" class="p-4 text-ink/70">No inquiries yet.</td></tr>@endforelse</tbody>
            </table>
        </div>
    </div>
    <div class="min-w-0">
        <h2 class="font-display text-2xl text-brand-900">Site health</h2>
        <ul class="mt-3 divide-y divide-sand-200 rounded-2xl bg-white text-sm shadow-sm">
            @foreach ($health as $h)
                <li class="flex gap-3 p-4">
                    <span @class(['mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold', 'bg-emerald-100 text-emerald-700' => $h['ok'], 'bg-accent/30 text-ink' => ! $h['ok']]) aria-hidden="true">{{ $h['ok'] ? '✓' : '!' }}</span>
                    <div><p @class(['font-semibold' => ! $h['ok']])>{{ $h['title'] }}<span class="sr-only"> {{ $h['ok'] ? '(done)' : '(needs attention)' }}</span></p>
                        @unless ($h['ok'])<p class="text-xs text-ink/70">{{ $h['fix'] }} @if ($h['url'])<a class="underline" href="{{ $h['url'] }}">Fix</a>@endif</p>@endunless</div>
                </li>
            @endforeach
        </ul>
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <x-button :href="route('admin.packages.create')" variant="outline" class="!py-2 !text-brand-900">New package</x-button>
            <x-button :href="route('admin.blog.index')" variant="outline" class="!py-2 !text-brand-900">Journal</x-button>
            <x-button :href="url('/')" variant="outline" class="!py-2 !text-brand-900">View site</x-button>
        </div>
    </div>
</div>
@endsection
