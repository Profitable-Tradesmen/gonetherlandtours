@props(['steps'])
{{--
    Hero quiz card. Steps + options come from the database (Admin > Quiz) and are rendered here
    server-side; Alpine (quiz() in resources/js/app.js) only handles progress and answers.
    Without JavaScript the <noscript> block shows the plain contact form instead.
--}}
<div id="quote" class="rounded-2xl border-4 border-accent bg-white p-5 shadow-card sm:p-7 text-ink">
    <h2 class="label-caps text-center text-base text-brown">Start Your Adventure</h2>

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
                    <select id="quiz-sel-{{ $idx }}" class="field mt-4" x-model="answers['{{ $step['key'] }}']">
                        <option value="">Please select</option>
                        @foreach ($step['options'] as $o)<option value="{{ $o['value'] }}">{{ $o['label'] }}</option>@endforeach
                    </select>
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
            <h3 id="quiz-q-contact" class="font-display text-xl sm:text-2xl">Where should we send your quote?</h3>
            <dl class="my-3 rounded-lg bg-sand p-3 text-xs leading-relaxed">
                @foreach ($steps as $step)
                    <div class="flex gap-2" x-show="answers['{{ $step['key'] }}']">
                        <dt class="font-semibold">{{ \Illuminate\Support\Str::limit($step['question'], 38) }}</dt>
                        <dd x-text="answers['{{ $step['key'] }}']"></dd>
                    </div>
                @endforeach
            </dl>
            <x-enquiry-form form="hero-quiz" idp="quiz" quiz-answers submit="Send My Quote Request" />
        </section>

        <div class="mt-4 flex items-center justify-between text-xs text-ink/70">
            <button type="button" class="underline hover:text-ink" x-show="i > 0" @click="back()">&larr; Back</button>
            <span x-show="i === 0"></span>
            <span class="inline-flex items-center gap-1"><x-icon name="lock" class="h-4 w-4" /> Takes less than 30 seconds</span>
        </div>
    </div>

    {{-- No-JS fallback. NOT a <noscript> (the form already contains a <noscript> and they cannot nest).
         Hidden by the .js class that the layout adds when JavaScript runs. --}}
    <div class="nojs-only">
        <h3 class="mt-4 font-display text-xl">Tell us about your trip</h3>
        <x-enquiry-form form="hero-quiz" idp="quiz-plain" extra submit="Send My Quote Request" />
    </div>
</div>
