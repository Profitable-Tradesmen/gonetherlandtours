@php $faqs = \App\Models\Faq::active()->when((int) $d['limit'] > 0, fn ($q) => $q->take((int) $d['limit']))->get(); @endphp
<section class="py-16 sm:py-24" @if ($d['heading']) aria-labelledby="fq-{{ $sid }}" @endif>
    <div class="container-site max-w-3xl">
        @if ($d['heading'])<x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'fq-'.$sid" />@endif
        @if ($faqs->isNotEmpty())
            <div class="space-y-3" data-reveal="up">
                @foreach ($faqs as $f)<x-faq-item :question="$f->question" :name="'faq-'.$sid">{{ $f->answer }}</x-faq-item>@endforeach
            </div>
        @else
            <p class="text-center">No questions have been published yet.</p>
        @endif
        @if ($d['help_heading'])
            <div class="mt-10 rounded-2xl bg-sand p-6 text-center">
                <p class="font-display text-2xl">{{ $d['help_heading'] }}</p>
                @if ($d['help_text'])<p class="mt-1 text-sm text-ink/70">{{ $d['help_text'] }}</p>@endif
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    @if ($d['help_button_label'])<x-button @click="$store.modal.show()">{{ $d['help_button_label'] }}</x-button>@endif
                    @if ($d['help_link_label'])<x-cta-button variant="secondary" :label="$d['help_link_label']" :url="$d['help_link_url'] ?: '/contact'" />@endif
                </div>
            </div>
        @endif
    </div>
</section>
