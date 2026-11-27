@php $faqs = \App\Models\Faq::active()->when((int) $d['limit'] > 0, fn ($q) => $q->take((int) $d['limit']))->get(); @endphp
<section class="py-16 sm:py-24" @if ($d['heading']) aria-labelledby="fq-{{ $sid }}" @endif>
    @php $side = (bool) $d['help_heading']; @endphp
    <div class="container-site {{ $side ? '' : 'max-w-3xl' }}">
        @if ($d['heading'])<x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :id="'fq-'.$sid" />@endif
        <div class="{{ $side ? 'grid items-start gap-8 lg:grid-cols-[minmax(0,1.9fr)_minmax(0,1fr)] lg:gap-12' : '' }}">
        <div class="min-w-0">
        @if ($faqs->isNotEmpty())
            <div class="space-y-3" data-reveal="up">
                @foreach ($faqs as $f)<x-faq-item :question="$f->question" :name="'faq-'.$sid">{{ $f->answer }}</x-faq-item>@endforeach
            </div>
        @else
            <p class="text-center">No questions have been published yet.</p>
        @endif
        </div>
        @if ($side)
            <aside class="rounded-2xl border-t-[3px] border-accent bg-brand-900 p-7 text-white shadow-card lg:sticky lg:top-28" aria-label="{{ $d['help_heading'] }}">
                <p class="font-display text-2xl">{{ $d['help_heading'] }}</p>
                @if ($d['help_text'])<p class="mt-3 text-sm leading-relaxed text-white/85">{{ $d['help_text'] }}</p>@endif
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($d['help_button_label'])<x-button @click="$store.modal.show()">{{ $d['help_button_label'] }}</x-button>@endif
                    @if ($d['help_link_label'])<x-cta-button variant="outline" class="text-white" :label="$d['help_link_label']" :url="$d['help_link_url'] ?: '/contact'" />@endif
                </div>
                <p class="label-caps mt-7 border-t border-white/20 pt-5 text-[0.65rem] font-bold text-accent">Helpful pages</p>
                <ul class="mt-2 text-sm">
                    @foreach ([['Ways to travel', route('ways-to-travel')], ['Tour packages', route('packages.index')], ['Travel journal', route('blog.index')]] as [$name, $url])
                        <li><a href="{{ $url }}" class="inline-flex min-h-[2.75rem] items-center gap-2 font-semibold hover:text-accent">{{ $name }} <x-icon name="arrow" class="h-4 w-4 text-accent" /></a></li>
                    @endforeach
                </ul>
            </aside>
        @endif
        </div>
    </div>
</section>
