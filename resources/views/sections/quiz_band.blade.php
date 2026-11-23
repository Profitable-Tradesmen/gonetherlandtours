<section id="quote-quiz" class="bg-sand py-16 sm:py-24" aria-labelledby="qb-{{ $sid }}">
    <div class="container-site">
        <x-section-heading data-reveal="up" :eyebrow="$d['eyebrow'] ?: null" :title="$d['heading']" :subtitle="$d['text'] ?: null" :id="'qb-'.$sid" />
        <div class="mx-auto max-w-xl" data-reveal="up"><x-quiz :steps="\App\Support\SiteData::quizSteps()" /></div>
    </div>
</section>
