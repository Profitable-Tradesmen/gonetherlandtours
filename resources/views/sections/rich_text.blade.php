<section class="container-site py-12">
    <div class="prose-site max-w-3xl">{!! str_replace('[Company Name]', e(company_name()), clean_html($d['html'])) !!}</div>
    @if ($d['show_version'])<p class="mt-8 text-xs text-ink/60">Version {{ $page->version }}</p>@endif
    @if ($d['button_label'])<p class="mt-8"><x-button @click="$store.modal.show()">{{ $d['button_label'] }}</x-button></p>@endif
</section>
