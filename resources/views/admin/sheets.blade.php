@extends('admin.layout')
@section('title', 'Google Sheets')
@section('content')
<h1 class="font-display text-3xl">Google Sheets</h1>

<div class="mt-6 max-w-4xl space-y-6">
    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-wrap items-center gap-3">
            @if ($active)
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800"><span class="h-2 w-2 rounded-full bg-emerald-600"></span>On: every inquiry is added to your sheet</span>
            @elseif (filled($url) && $secretHint && ! $sendEnabled)
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Paused: connection saved, nothing is sent</span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-sand px-3 py-1 text-sm font-semibold text-ink/70"><span class="h-2 w-2 rounded-full bg-ink/40"></span>Off: add the web app URL and the secret key below</span>
            @endif
            <span class="text-sm text-ink/70">{{ $synced }} in the sheet &middot; {{ $failed }} failed &middot; {{ $skipped }} skipped @if ($pending) &middot; {{ $pending }} waiting @endif</span>
        </div>
        <p class="mt-3 text-sm text-ink/70">Every form on the site (the quiz, quote requests, questions and the contact form) uses the same pipeline, so once this is On they all add one row to your sheet, including the UTM parameters and ad click ids (GCLID, GBRAID, WBRAID) of the first and the last visit. Inquiries are always saved here first: if Google is unreachable, nothing is lost and the visitor never sees an error.</p>
    </section>

    <form method="POST" action="{{ route('admin.sheets.update') }}" class="space-y-5 rounded-xl bg-white p-5 shadow-sm sm:p-7" autocomplete="off"
          x-data="{ key: '', hasKey: {{ $secretHint ? 'true' : 'false' }}, copied: false,
                    generate() { const a = new Uint8Array(24); crypto.getRandomValues(a); this.key = Array.from(a, (b) => b.toString(36).padStart(2, '0')).join('').slice(0, 40); this.copied = false; },
                    copy() { if (navigator.clipboard) { navigator.clipboard.writeText(this.key).then(() => this.copied = true); } } }">
        @csrf @method('PUT')
        <h2 class="font-display text-xl">Connection</h2>
        <div>
            <label class="field-label" for="web_app_url">Web app URL</label>
            <input id="web_app_url" name="web_app_url" value="{{ old('web_app_url', $url) }}" class="field" placeholder="https://script.google.com/macros/s/.../exec" autocomplete="off" spellcheck="false">
            <p class="mt-1 text-xs text-ink/60">Step 6 of the instructions below gives you this URL.</p>
        </div>
        <div>
            <label class="field-label" for="secret_key">Secret key</label>
            <div class="flex flex-wrap gap-3">
                <input id="secret_key" name="secret_key" x-model="key" class="field min-w-0 flex-1" autocomplete="off" spellcheck="false" placeholder="{{ $secretHint ? '••••••••••••'.$secretHint : 'Click Generate, then copy it into the script' }}">
                <button type="button" class="btn btn-outline !text-ink" @click="generate()">Generate</button>
                <button type="button" class="btn btn-outline !text-ink" x-show="key" x-cloak @click="copy()"><span x-text="copied ? 'Copied' : 'Copy'"></span></button>
            </div>
            <p class="mt-1 text-xs text-ink/60">The same key must be pasted into the script (step 4). It stops anyone else from writing to your sheet. It is stored encrypted and never shown again, only its last 4 characters.@if ($secretHint) A key is saved (••••{{ $secretHint }}). Leave the box empty to keep it.@endif</p>
            @if ($secretHint)
                <label class="mt-2 inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_secret" value="1" class="h-4 w-4"> Remove the saved key</label>
            @endif
        </div>
        <div>
            <label class="inline-flex items-center gap-3 font-semibold"><input type="checkbox" name="enabled" value="1" class="h-5 w-5" @checked(old('enabled', $sendEnabled))> Send inquiries to Google Sheets</label>
            <p class="mt-1 text-sm text-ink/60">Untick to pause the sync without losing your connection.</p>
        </div>
        <x-button type="submit" variant="secondary">Save</x-button>
    </form>

    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
        <h2 class="font-display text-xl">Check and catch up</h2>
        <div class="mt-4 grid gap-6 sm:grid-cols-2">
            <form method="POST" action="{{ route('admin.sheets.test') }}">@csrf
                <button class="btn btn-outline w-full !text-ink">Send a test row</button>
                <p class="mt-2 text-sm text-ink/60">Adds one sample row (marked "Test row") to your sheet so you can see the connection works. It is not saved as an inquiry. Delete the row afterwards.</p>
            </form>
            <form method="POST" action="{{ route('admin.sheets.backfill') }}">@csrf
                <button class="btn btn-outline w-full !text-ink">Send inquiries that are not in the sheet</button>
                <p class="mt-2 text-sm text-ink/60">Adds the {{ $skipped + $failed }} inquiries that were received while this was off, or that failed. Rows already in the sheet are never duplicated.</p>
            </form>
        </div>
    </section>

    <details class="rounded-xl bg-white p-5 shadow-sm sm:p-7" @unless ($active) open @endunless>
        <summary class="cursor-pointer font-display text-xl">Step by step: set up the spreadsheet</summary>
        <ol class="mt-4 list-decimal space-y-4 pl-5 text-sm leading-relaxed">
            <li><strong>Create the sheet.</strong> Go to <a href="https://sheets.google.com" target="_blank" rel="noopener" class="font-semibold text-brand-700 underline">sheets.google.com</a>, sign in with the Google account that should own the data and click <strong>Blank spreadsheet</strong>. Name it, for example, "{{ $brand }} enquiries". Leave it empty: the script creates a tab called <strong>Inquiries</strong> and the header row for you.</li>
            <li><strong>Open Apps Script.</strong> In the sheet click <strong>Extensions &gt; Apps Script</strong>. A new tab opens with a file called Code.gs. Select everything in it and delete it.</li>
            <li><strong>Paste the script.</strong> Click <strong>Copy the script</strong> below and paste it into the empty Code.gs file.</li>
            <li><strong>Add the secret key.</strong> Above, click <strong>Generate</strong>, then <strong>Copy</strong>, and click <strong>Save</strong> on this page. In the script, replace <code>PASTE-YOUR-SECRET-KEY-HERE</code> (between the quotes, near the top) with that key. Click the disk icon (or press Ctrl+S) to save the script. The key in the script and the key on this page must be exactly the same.</li>
            <li><strong>Deploy it.</strong> In Apps Script click <strong>Deploy &gt; New deployment</strong>. Click the gear icon next to "Select type" and choose <strong>Web app</strong>. Set <strong>Execute as: Me</strong> and <strong>Who has access: Anyone</strong> (the secret key is what protects it). Click <strong>Deploy</strong>. Google asks you to authorize: click <strong>Authorize access</strong>, choose your account, then <strong>Advanced &gt; Go to (your project name) (unsafe) &gt; Allow</strong>. This warning is normal for your own scripts: only you wrote and own it.</li>
            <li><strong>Copy the web app URL.</strong> After deploying, copy the <strong>Web app</strong> URL (it ends in <code>/exec</code>) and paste it into <strong>Web app URL</strong> above. Make sure <strong>Send inquiries to Google Sheets</strong> is ticked and click <strong>Save</strong>.</li>
            <li><strong>Test it.</strong> Click <strong>Send a test row</strong>. Open your sheet: you should see a tab called <strong>Inquiries</strong> with the header row and one row marked "Test row". Delete that row. From now on every new inquiry adds a row within a few seconds.</li>
            <li><strong>Optional: catch up.</strong> Click <strong>Send inquiries that are not in the sheet</strong> to add the older inquiries. Repeat if it says more are waiting.</li>
        </ol>
        <h3 class="mt-6 font-display text-lg">Good to know</h3>
        <ul class="mt-2 list-disc space-y-2 pl-5 text-sm leading-relaxed">
            <li><strong>Columns:</strong> values are matched to columns by their header names, so you can re-order columns, hide them, or add your own columns on the right (for example for notes or formulas). Do not rename the headers.</li>
            <li><strong>First and last visit:</strong> the plain UTM columns show the visit the form was sent from; the columns starting with "First" show where the visitor came from the very first time.</li>
            <li><strong>Changing the script later:</strong> after editing it click <strong>Deploy &gt; Manage deployments &gt; the pencil &gt; Version: New version &gt; Deploy</strong>. The URL stays the same.</li>
            <li><strong>Privacy:</strong> the sheet contains personal data (names, emails, phone numbers). Share it only with people who need it, and remove people when they leave. The sheet stores no IP addresses.</li>
            <li><strong>Stopping:</strong> untick the box above and save. To cut the connection completely, remove the deployment in Apps Script (Deploy &gt; Manage deployments &gt; Archive).</li>
        </ul>
        <h3 class="mt-6 font-display text-lg">If something goes wrong</h3>
        <ul class="mt-2 list-disc space-y-2 pl-5 text-sm leading-relaxed">
            <li><strong>"Google did not return the expected answer":</strong> the deployment is not set to <em>Who has access: Anyone</em>. Redeploy it with that setting and paste the new URL.</li>
            <li><strong>"The secret key does not match":</strong> the key in the script differs from the one saved here. Generate a new key, paste it in both places and save both.</li>
            <li><strong>Nothing arrives but no error:</strong> check the web app URL ends in <code>/exec</code> (not <code>/dev</code>) and that "Send inquiries to Google Sheets" is ticked. Open an inquiry in Admin &gt; Inquiries to see its sheet status and the last error.</li>
            <li><strong>Failed rows:</strong> they are retried automatically a few times. After fixing the cause, use <strong>Send inquiries that are not in the sheet</strong>.</li>
        </ul>
    </details>

    <section class="rounded-xl bg-white p-5 shadow-sm sm:p-7" x-data="{ copied: false }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-display text-xl">The script to paste (step 3)</h2>
            <button type="button" class="btn btn-secondary !py-2" @click="navigator.clipboard.writeText($refs.code.textContent).then(() => copied = true)"><span x-text="copied ? 'Copied' : 'Copy the script'"></span></button>
        </div>
        <pre x-ref="code" class="mt-4 max-h-96 overflow-auto rounded-lg bg-ink p-4 text-xs leading-relaxed text-white/90">{{ $script }}</pre>
    </section>

    <details class="rounded-xl bg-white p-5 shadow-sm sm:p-7">
        <summary class="cursor-pointer font-display text-xl">The {{ count($headers) }} columns your sheet gets</summary>
        <p class="mt-3 text-sm text-ink/70">Created automatically in this order on an empty tab. New columns added in later versions are appended on the right.</p>
        <ol class="mt-3 columns-2 gap-6 text-sm sm:columns-3">
            @foreach ($headers as $h)<li class="py-0.5">{{ $h }}</li>@endforeach
        </ol>
    </details>
</div>
@endsection
