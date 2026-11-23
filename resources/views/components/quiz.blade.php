@props(['steps'])
{{--
    Hero quiz card. Steps + options come from the database (Admin > Quiz) and are rendered here
    server-side; Alpine (quiz() in resources/js/app.js) only handles progress and answers.
    Without JavaScript the <noscript> block shows the plain contact form instead.
--}}
<div id="quote" class="overflow-hidden rounded-2xl bg-white text-ink shadow-card">
    {{-- Navy header: title and the optional rating line. Body: the quiz. Navy footer: reassurances (all editable in Admin > Settings > Trust and reassurance). --}}
    <div class="bg-brand-900 px-5 py-5 text-center text-white sm:px-7 sm:py-6">
        <h2 class="font-display text-2xl font-medium leading-tight sm:text-3xl">{{ text('quiz_title') }}</h2>
        @if (text('quiz_rating_label'))<p class="mt-3 flex items-center justify-center gap-1.5 text-sm font-semibold"><span class="text-accent" aria-hidden="true">&#9733;</span>{{ text('quiz_rating_label') }}</p>@endif
    </div>
    <div class="p-5 pt-1 sm:p-7 sm:pt-2">

    <div x-data="quiz(@js(collect($steps)->pluck('key')->all()))" x-cloak>
        {{-- progress --}}
        <div class="mt-4" role="progressbar" aria-label="Quiz progress" :aria-valuenow="percent" aria-valuemin="0" aria-valuemax="100">
            <div class="h-1.5 overflow-hidden rounded-full bg-sand-200"><div class="h-full rounded-full bg-brand-700 transition-all" :style="'width:' + Math.max(percent, 6) + '%'"></div></div>
            <p class="mt-1 text-xs text-ink/60"><span x-text="i + 1"></span> of <span x-text="total"></span></p>
        </div>

        @foreach ($steps as $idx => $step)
            <section x-show="i === {{ $idx }}" @if ($idx > 0) x-cloak @endif class="mt-4" aria-labelledby="quiz-q-{{ $idx }}">
                <h3 id="quiz-q-{{ $idx }}" class="font-display text-xl leading-snug sm:text-2xl">{{ $step['question'] }}</h3>

                @if ($step['type'] === 'select')
                    <label for="quiz-sel-{{ $idx }}" class="sr-only">{{ $step['question'] }}</label>
                    {{-- No Next button: choosing an option moves on by itself. Arrow keys on a closed list only browse the options (Enter confirms). --}}
                    <select id="quiz-sel-{{ $idx }}" class="field mt-4" x-model="answers['{{ $step['key'] }}']"
                            @keydown="browsing = ['ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'].includes($event.key)"
                            @keydown.enter.prevent="next('{{ $step['key'] }}')"
                            @change="if (!browsing) next('{{ $step['key'] }}'); browsing = false">
                        <option value="">Please select</option>
                        @foreach ($step['options'] as $o)<option value="{{ $o['value'] }}">{{ $o['label'] }}</option>@endforeach
                    </select>
                @elseif ($step['type'] === 'date')
                    <label for="quiz-date-{{ $idx }}" class="sr-only">{{ $step['question'] }}</label>
                    <input id="quiz-date-{{ $idx }}" type="date" min="{{ now()->toDateString() }}" class="field mt-4"
                           x-model="answers['{{ $step['key'] }}']"
                           @change="if ($event.target.value >= $event.target.min) next('{{ $step['key'] }}')"
                           @keydown.enter.prevent="if ($event.target.value >= $event.target.min) next('{{ $step['key'] }}')">
                    <p class="mt-1 text-xs text-ink/60">An approximate date is fine. Choose a day and we will continue.</p>
                @elseif ($step['type'] === 'text')
                    <label for="quiz-txt-{{ $idx }}" class="sr-only">{{ $step['question'] }}</label>
                    <input id="quiz-txt-{{ $idx }}" type="text" maxlength="60" class="field mt-4" placeholder="For example: April 2027, or flexible"
                           x-model.trim="answers['{{ $step['key'] }}']" @keydown.enter.prevent="next('{{ $step['key'] }}')">
                    <button type="button" class="btn btn-primary btn-block mt-4" :disabled="!answers['{{ $step['key'] }}']" @click="next('{{ $step['key'] }}')">Next</button>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @foreach ($step['options'] as $o)
                            <button type="button" @click="pick('{{ $step['key'] }}', @js($o['value']))"
                                    :class="answers['{{ $step['key'] }}'] === @js($o['value']) ? 'border-brand-700 bg-brand/10' : 'border-sand-200'"
                                    class="flex min-h-[5.5rem] flex-col items-center justify-center gap-2 rounded-xl border-2 bg-white p-3 text-center text-sm font-semibold transition hover:border-brand-700 hover:bg-sand">
                                @if ($o['icon'])<x-icon :name="$o['icon']" class="h-7 w-7 text-brand-700" />@endif
                                <span>{{ $o['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        {{-- final step: contact details --}}
        <section x-show="onContact" x-cloak class="mt-4" x-ref="contactWrap" aria-labelledby="quiz-q-contact">
            <h3 id="quiz-q-contact" class="sr-only">Where should we send your quote?</h3>
            <x-enquiry-form form="hero-quiz" idp="quiz" variant="quiz" quiz-answers submit="Send My Quote Request" />
        </section>

        <div class="mt-4 flex items-center justify-between text-xs text-ink/70" x-show="i > 0" x-cloak>
            <button type="button" class="underline hover:text-ink" x-show="i > 0" @click="back()">&larr; Back</button>
        </div>
    </div>

    {{-- No-JS fallback. NOT a <noscript> (the form already contains a <noscript> and they cannot nest).
         Hidden by the .js class that the layout adds when JavaScript runs. --}}
    <div class="nojs-only">
        <h3 class="mt-4 font-display text-xl">Tell us about your trip</h3>
        <x-enquiry-form form="hero-quiz" idp="quiz-plain" variant="modal" submit="Send My Quote Request" />
    </div>
    </div>
    @php $trust = array_values(array_filter([[text('quiz_trust_1'), 'lock'], [text('quiz_trust_2'), 'check'], [text('quiz_trust_3'), 'phone']], fn ($t) => $t[0] !== '')); @endphp
    @if ($trust || text('quiz_trust_line') || text('quote_promise'))
        <div class="bg-brand-900 px-5 py-4 text-center text-white sm:px-7">
            @if ($trust)<ul class="flex flex-wrap items-center justify-center gap-x-5 gap-y-1.5 text-xs font-semibold">@foreach ($trust as [$label, $icon])<li class="inline-flex items-center gap-1.5"><x-icon :name="$icon" class="h-4 w-4 text-accent" />{{ $label }}</li>@endforeach</ul>@endif
            @if (text('quiz_trust_line'))<p class="mt-2 text-[0.7rem] font-medium text-white/75">{{ text('quiz_trust_line') }}</p>@endif
            @if (text('quote_promise'))<p class="mt-1 text-[0.7rem] font-medium text-white/75">{{ text('quote_promise') }}</p>@endif
        </div>
    @endif
</div>
