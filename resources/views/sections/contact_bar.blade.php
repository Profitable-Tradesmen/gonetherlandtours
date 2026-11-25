@php $phone = setting('phone') ?: '+1 000-000-0000'; @endphp
{{-- Slim white bar under the hero: who to talk to, the phone number, and the quote button. Hidden on phones (the hero and sticky bar already carry the button). --}}
<section class="hidden border-b border-sand-200 bg-white py-3 sm:block" aria-label="Contact">
    <div class="container-site flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
        <p class="text-sm text-ink/80">{{ $d['text'] }} @if ($phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="ml-1 font-display text-lg font-medium text-brand-900 hover:text-rust">{{ $phone }}</a>@endif</p>
        @if ($d['button_label'])<button type="button" class="btn btn-primary !px-5 !py-2" @click="$store.modal.show()">{{ $d['button_label'] }}</button>@endif
    </div>
</section>
