@extends('admin.layout')
@section('title', 'GoHighLevel')
@section('content')
<h1 class="font-display text-3xl">GoHighLevel integration</h1>

<div class="mt-6 max-w-4xl space-y-6">
    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-wrap items-center gap-3">
            @if ($active)
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>On: inquiries are sent to GoHighLevel</span>
            @elseif (filled($locationId) && $tokenHint && ! $sendEnabled)
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Paused: credentials saved, nothing is sent</span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-sand px-3 py-1 text-sm font-semibold text-ink/70"><span class="h-2 w-2 rounded-full bg-ink/40"></span>Off: not connected yet</span>
            @endif
            <span class="text-sm text-ink/70">Credentials saved here (the token is stored encrypted).</span>
        </div>
        <p class="mt-3 text-sm text-ink/70">Inquiries are always saved here first. If GoHighLevel is off or fails, nothing is lost and the inquiry is never blocked.</p>
    </section>

    <form method="POST" action="{{ route('admin.ghl.update') }}" class="space-y-5 rounded-xl bg-white p-5 shadow-sm sm:p-7" autocomplete="off">
        @csrf @method('PUT')
        <h2 class="font-display text-xl">Connection details</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="field-label" for="location_id">Location ID</label>
                <input id="location_id" name="location_id" value="{{ old('location_id', $locationId) }}" class="field" autocomplete="off" spellcheck="false">
                <p class="mt-1 text-xs text-ink/60">GoHighLevel &gt; Settings &gt; Business Profile (or the Location ID in the sub-account URL).</p>
            </div>
            <div>
                <label class="field-label" for="api_token">API token (Private Integration token)</label>
                <input id="api_token" type="password" name="api_token" class="field" autocomplete="new-password" spellcheck="false" placeholder="{{ $tokenHint ? '••••••••'.$tokenHint : 'pit-xxxxxxxx' }}">
                @if ($tokenHint)
                    <p class="mt-1 text-xs text-ink/60">A token is saved (••••••••{{ $tokenHint }}){{ $fromEnv ? ' from .env' : '' }}. Leave blank to keep it, or paste a new one to replace it.</p>
                    @unless ($fromEnv)
                        <label class="mt-2 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_token" value="1" class="h-4 w-4"> Remove the saved token</label>
                    @endunless
                @else
                    <p class="mt-1 text-xs text-ink/60">Paste the Private Integration token. It is stored encrypted and never shown again.</p>
                @endif
            </div>
        </div>
        <div>
            <label class="inline-flex items-center gap-3 font-semibold"><input type="checkbox" name="enabled" value="1" class="h-5 w-5" @checked(old('enabled', $sendEnabled))> Send inquiries to GoHighLevel</label>
            <p class="mt-1 text-sm text-ink/60">Untick to pause the sync without losing your credentials.</p>
        </div>
        <x-button type="submit" variant="secondary">Save</x-button>
    </form>

    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
        <h2 class="font-display text-xl">Check and prepare</h2>
        <div class="mt-4 grid gap-6 sm:grid-cols-2">
            <form method="POST" action="{{ route('admin.ghl.test') }}">@csrf
                <button class="btn btn-outline w-full !text-ink">Test connection</button>
                <p class="mt-2 text-sm text-ink/60">Checks the token and Location ID and that the {{ $fieldCount }} custom fields exist. Creates nothing and sends no contact.</p>
            </form>
            <form method="POST" action="{{ route('admin.ghl.setup') }}">@csrf
                <button class="btn btn-outline w-full !text-ink">Create missing custom fields</button>
                <p class="mt-2 text-sm text-ink/60">Adds the {{ $fieldCount }} fields inquiries use (quiz answers, consent, tracking). Safe to repeat. Do not rename them in GoHighLevel.</p>
            </form>
        </div>
    </section>

    <details class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
        <summary class="cursor-pointer font-display text-xl">How to get the Location ID and token</summary>
        <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm">
            <li>In GoHighLevel open the sub-account, then <strong>Settings &gt; Private Integrations &gt; Create new integration</strong>. Name it "{{ setting('brand_name', 'GoNetherlandTours') }} website".</li>
            <li>Tick these scopes: <code>contacts.readonly</code>, <code>contacts.write</code>, <code>locations/customFields.readonly</code>, <code>locations/customFields.write</code>. Optional: <code>locations.readonly</code> (only shows the sub-account name in the test).</li>
            <li>Create it and copy the token (it is shown once). Paste it in the API token box above.</li>
            <li>The <strong>Location ID</strong> is under <strong>Settings &gt; Business Profile</strong>, or in the sub-account URL after <code>/location/</code>.</li>
            <li>Click <strong>Save</strong>, then <strong>Test connection</strong>, then <strong>Create missing custom fields</strong>.</li>
        </ol>
    </details>
</div>
@endsection
