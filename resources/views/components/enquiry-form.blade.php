@props([
    'form' => 'contact',          // key from config('gnt.form_names'); stored as the form name
    'idp',                        // unique id prefix per form instance on the page
    'package' => null,            // App\Models\Package when the form belongs to a package
    'extra' => false,             // show time of year, group size, nights
    'quizAnswers' => false,       // inside the hero quiz: emit hidden quiz[...] inputs from Alpine state
    'submit' => 'Get My Free Quote',
    'note' => null,
    'variant' => 'default',       // default (contact/package) | quiz (short: contact details only) | modal (pop-up: full trip details)
])
{{--
    THE enquiry form. Every form on the site renders this component and posts to the same route
    (EnquiryController@store), so reCAPTCHA, consent, honeypot and validation are identical everywhere.
    Rules are documented in docs/reference/FORMS_AND_TRACKING.md and CLAUDE.md.
--}}
@php
    $countries = config('gnt.countries');
    $siteKey = recaptcha_site_key();
    $oldHere = old('_form_id') === $idp;
    // Dropdown options for the pop-up come from the quiz steps (Admin > Quiz) so both forms stay consistent.
    $opts = fn (string $key) => \App\Models\QuizStep::where('key', $key)->first()?->activeOptions()->get(['label', 'value']) ?? collect();
@endphp
<form method="POST" action="{{ route('enquiry.store') }}" x-data="enquiryForm" @submit.prevent="submit($event)"
      data-form-name="{{ $form }}" novalidate class="relative" aria-label="{{ config('gnt.form_names.'.$form, 'Enquiry') }} enquiry form">
    @csrf
    <input type="hidden" name="form_name" value="{{ $form }}">
    <input type="hidden" name="_form_id" value="{{ $idp }}">
    <input type="hidden" name="page_url" value="{{ request()->fullUrl() }}">
    @if ($package)
        <input type="hidden" name="package_id" value="{{ $package->id }}">
        <input type="hidden" name="package_name" value="{{ $package->title }}">
    @endif
    @if ($quizAnswers)
        <template x-for="(val, key) in answers" :key="key">
            <input type="hidden" :name="'quiz[' + key + ']'" :value="val">
        </template>
    @endif
    {{-- Honeypot: humans never see or fill this. --}}
    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="{{ $idp }}-hp">Leave this field empty</label>
        <input id="{{ $idp }}-hp" type="text" name="company_website" tabindex="-1" autocomplete="off">
    </div>

    <div x-show="success" x-cloak class="rounded-xl bg-emerald-50 p-6 text-center" role="status">
        <p class="font-display text-2xl text-emerald-900">Thank you!</p>
        <p class="mt-2 text-emerald-900">Your enquiry has been sent. Taking you to the next step...</p>
    </div>

    <div x-show="!success" class="grid gap-3 sm:grid-cols-2">
        <x-form-field name="first_name" label="First name" :idp="$idp" required autocomplete="given-name" />
        <x-form-field name="last_name" label="Last name" :idp="$idp" required autocomplete="family-name" />
        <x-form-field name="email" label="Email" type="email" :idp="$idp" required autocomplete="email" span />

        <div class="sm:col-span-2 grid grid-cols-[minmax(0,10.5rem)_1fr] gap-3">
            <x-form-field name="country_code" label="Country code" :idp="$idp" required>
                @php $selectedIso = $oldHere ? old('country_code', 'US') : 'US'; @endphp
                @php
                    $flagBase = asset('images/flags');
                    $selectedRow = collect($countries)->firstWhere(2, $selectedIso) ?? $countries[0];
                @endphp
                {{--
                    Country code with flags in the list. With JavaScript the custom listbox (countrySelect in app.js) is shown and
                    drives the real <select>, which is the value that is submitted. Without JavaScript only the native select shows.
                --}}
                <div class="relative" x-data="countrySelect(@js($selectedIso), @js(collect($countries)->map(fn ($c) => ['name' => $c[0], 'dial' => $c[1], 'iso' => $c[2]])->all()), @js($flagBase))" @click.outside="open && close(false)">
                    <select id="{{ $idp }}-country_code" name="country_code" class="field cc-native" required autocomplete="tel-country-code" x-model="iso">
                        @foreach ($countries as [$cName, $cDial, $cIso])
                            <option value="{{ $cIso }}" @selected($selectedIso === $cIso)>{{ $cIso }} (+{{ $cDial }})</option>
                        @endforeach
                    </select>

                    <button type="button" x-ref="btn" class="field cc-btn items-center gap-2 text-left" aria-haspopup="listbox"
                            :aria-expanded="open" aria-controls="{{ $idp }}-cc-list"
                            :aria-label="'Country code: ' + current.name + ' (+' + current.dial + ')'"
                            @click="toggle()" @keydown.arrow-down.prevent="show()" @keydown.arrow-up.prevent="show()">
                        <img :src="flag(iso)" src="{{ $flagBase }}/{{ strtolower($selectedIso) }}.svg" alt="" width="24" height="18" class="h-[18px] w-6 shrink-0 rounded-sm object-cover shadow-[0_0_0_1px_rgb(0_0_0/.15)]">
                        <span class="min-w-0 flex-1 truncate" x-text="current.iso + ' (+' + current.dial + ')'">{{ $selectedRow[2] }} (+{{ $selectedRow[1] }})</span>
                        <x-icon name="chevron" class="h-4 w-4 shrink-0 text-ink/60" />
                    </button>

                    <ul id="{{ $idp }}-cc-list" x-ref="list" x-show="open" x-cloak tabindex="-1" role="listbox" aria-label="Country code"
                        :aria-activedescendant="active >= 0 ? '{{ $idp }}-cc-' + list[active].iso : null"
                        @keydown="key($event)"
                        class="absolute left-0 top-full z-30 mt-1 max-h-64 w-72 max-w-[calc(100vw-3rem)] overflow-y-auto rounded-xl border border-sand-200 bg-white py-1 shadow-card focus:outline-none">
                        @foreach ($countries as $i => [$cName, $cDial, $cIso])
                            <li id="{{ $idp }}-cc-{{ $cIso }}" role="option" :aria-selected="iso === '{{ $cIso }}'"
                                @click="choose('{{ $cIso }}')" @mousemove="active = {{ $i }}"
                                :class="active === {{ $i }} ? 'bg-sand' : ''"
                                class="flex cursor-pointer items-center gap-3 px-3 py-2 text-sm">
                                <img src="{{ $flagBase }}/{{ strtolower($cIso) }}.svg" alt="" width="24" height="18" loading="lazy" class="h-[18px] w-6 shrink-0 rounded-sm object-cover shadow-[0_0_0_1px_rgb(0_0_0/.15)]">
                                <span class="flex-1">{{ $cName }}</span>
                                <span class="text-ink/60">+{{ $cDial }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-form-field>
            <x-form-field name="phone" label="Phone" type="tel" :idp="$idp" required autocomplete="tel-national" />
        </div>

        @if ($variant === 'modal')
            <x-form-field name="destination" label="Destination" :idp="$idp" span>
                <select id="{{ $idp }}-destination" name="destination" class="field">
                    <option value="">Select (optional)</option>
                    @foreach (\App\Models\Destination::active()->pluck('name') as $dName)<option>{{ $dName }}</option>@endforeach
                    <option>Not sure yet</option>
                </select>
            </x-form-field>
            <x-form-field name="group_size" label="Number of travellers" :idp="$idp">
                <select id="{{ $idp }}-group_size" name="group_size" class="field"><option value="">Select</option>@foreach ($opts('group_size') as $o)<option value="{{ $o->value }}">{{ $o->label }}</option>@endforeach</select>
            </x-form-field>
            <x-form-field name="travel_date" label="Preferred travel dates" type="date" :min="now()->toDateString()" :idp="$idp" hint="An approximate date is fine." />
            <x-form-field name="travel_style" label="What is your favourite way to see a new country?" :idp="$idp" span>
                <select id="{{ $idp }}-travel_style" name="travel_style" class="field"><option value="">Select</option>@foreach ($opts('travel_style') as $o)<option value="{{ $o->value }}">{{ $o->label }}</option>@endforeach</select>
            </x-form-field>
            <x-form-field name="nights" label="Number of nights" :idp="$idp">
                <select id="{{ $idp }}-nights" name="nights" class="field"><option value="">Select</option>@foreach ($opts('nights') as $o)<option value="{{ $o->value }}">{{ $o->label }}</option>@endforeach</select>
            </x-form-field>
            <x-form-field name="budget" label="Estimated budget (per person)" :idp="$idp" required>
                <select id="{{ $idp }}-budget" name="budget" class="field" required><option value="">Select</option>@foreach ($opts('budget') as $o)<option value="{{ $o->value }}">{{ $o->label }}</option>@endforeach</select>
            </x-form-field>
        @elseif ($extra && $variant === 'default')
            <x-form-field name="season" label="Time of year" :idp="$idp">
                <select id="{{ $idp }}-season" name="season" class="field">
                    <option value="">Select (optional)</option>
                    @foreach (['Spring', 'Summer', 'Autumn', 'Winter'] as $s)<option>{{ $s }}</option>@endforeach
                </select>
            </x-form-field>
            <x-form-field name="group_size" label="Group size" :idp="$idp">
                <select id="{{ $idp }}-group_size" name="group_size" class="field">
                    <option value="">Select (optional)</option>
                    @foreach (range(1, 9) as $n)<option>{{ $n }}</option>@endforeach
                    <option>10+</option>
                </select>
            </x-form-field>
            <x-form-field name="nights" label="Number of nights" :idp="$idp">
                <select id="{{ $idp }}-nights" name="nights" class="field">
                    <option value="">Select (optional)</option>
                    @foreach (range(1, 13) as $n)<option>{{ $n }}</option>@endforeach
                    <option>14+</option>
                </select>
            </x-form-field>
        @endif

        @if ($variant === 'default')
            <x-form-field name="travel_date" label="Preferred travel date (approx.)" type="date" :min="now()->toDateString()" :idp="$idp" :span="! $extra" hint="An approximate date is fine." />

            <x-form-field name="second_quote" label="Would you like a second quote from a partner?" :idp="$idp" span>
                <select id="{{ $idp }}-second_quote" name="second_quote" class="field">
                    @foreach (config('gnt.second_quote_options') as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                    @endforeach
                </select>
            </x-form-field>
        @endif

        <x-form-field name="special_request" label="Special request" :idp="$idp" span>
            <textarea id="{{ $idp }}-special_request" name="special_request" rows="3" class="field" maxlength="2000">{{ $oldHere ? old('special_request') : '' }}</textarea>
        </x-form-field>

        {{-- Required consent: unticked by default, enforced in browser and on the server. --}}
        <div class="sm:col-span-2">
            <div class="flex items-start gap-3">
                <input id="{{ $idp }}-consent" name="consent" type="checkbox" value="1" required
                       class="mt-1 h-5 w-5 shrink-0 rounded border-ink/40 accent-brand-700"
                       :aria-invalid="err('consent') ? 'true' : 'false'" aria-describedby="{{ $idp }}-consent-error">
                <label for="{{ $idp }}-consent" class="text-xs leading-relaxed text-ink/80">{!! consent_html() !!}</label>
            </div>
            <p id="{{ $idp }}-consent-error" class="field-error" x-show="err('consent')" x-text="err('consent')" role="alert">{{ $oldHere ? $errors->first('consent') : '' }}</p>
        </div>

        {{-- reCAPTCHA v2 checkbox (explicit render) + no-JavaScript fallback --}}
        <div class="sm:col-span-2">
            @if ($siteKey)
                <div x-ref="captcha" data-sitekey="{{ $siteKey }}" class="min-h-[78px]"></div>
                <noscript>
                    <iframe src="https://www.google.com/recaptcha/api/fallback?k={{ $siteKey }}" title="reCAPTCHA" width="302" height="422" style="border:0"></iframe>
                    <textarea name="g-recaptcha-response" class="field" placeholder="Paste the verification code from above"></textarea>
                </noscript>
            @else
                <p class="field-error">reCAPTCHA is not configured. Set the keys in Admin &gt; Settings.</p>
            @endif
            <p class="field-error" x-show="err('g-recaptcha-response')" x-text="err('g-recaptcha-response')" role="alert">{{ $oldHere ? $errors->first('g-recaptcha-response') : '' }}</p>
        </div>

        <div class="sm:col-span-2">
            <p x-show="message" x-text="message" class="field-error mb-2" role="alert">@if ($oldHere && $errors->any()) Please check the highlighted fields. @endif</p>
            <button type="submit" class="btn btn-primary btn-block" :disabled="sending" :aria-busy="sending">
                <span x-show="!sending">{{ $submit }}</span><span x-show="sending" x-cloak>Sending...</span>
            </button>
            @if ($note)<p class="mt-2 text-center text-xs text-ink/60">{{ $note }}</p>@endif
        </div>
    </div>
</form>
