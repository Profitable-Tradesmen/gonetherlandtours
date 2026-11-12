@php $email = setting('email'); $phone = setting('phone'); @endphp
<div class="bg-ink text-white text-xs sm:text-sm">
    <div class="container-site flex flex-wrap items-center justify-between gap-x-6 gap-y-1 py-2">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
            @if ($email)<a href="mailto:{{ $email }}" class="inline-flex items-center gap-1.5 hover:text-accent"><x-icon name="mail" class="h-4 w-4" /><span class="hidden sm:inline">{{ $email }}</span><span class="sm:hidden">Email us</span></a>@endif
            @if ($phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="inline-flex items-center gap-1.5 font-semibold hover:text-accent"><x-icon name="phone" class="h-4 w-4" />{{ $phone }}</a>@endif
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}#quote" class="hover:text-accent underline-offset-4 hover:underline">Get Instant Quote</a>
            <button type="button" @click="$store.modal.show()" class="hidden rounded-full bg-accent px-4 py-1.5 text-[0.7rem] font-bold uppercase tracking-wider text-ink hover:bg-accent-600 sm:inline-block">Start Your Adventure</button>
        </div>
    </div>
</div>
