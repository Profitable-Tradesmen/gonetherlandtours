@php $email = setting('email'); $phone = setting('phone'); $linkUrl = trim((string) text('topbar_link_url')); @endphp
<div class="hidden bg-ink text-white text-xs sm:block sm:text-sm">
    <div class="container-site flex flex-wrap items-center justify-between gap-x-6 gap-y-1 py-2">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
            @if ($email)<a href="mailto:{{ $email }}" class="inline-flex items-center gap-1.5 hover:text-accent"><x-icon name="mail" class="h-4 w-4" /><span class="hidden sm:inline">{{ $email }}</span><span class="sm:hidden">Email us</span></a>@endif
            @if ($phone)<a href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}" class="inline-flex items-center gap-1.5 font-semibold hover:text-accent"><x-icon name="phone" class="h-4 w-4" />{{ $phone }}</a>@endif
        </div>
        <div class="flex items-center gap-4">
            @if (text('topbar_link_label'))
                @if ($linkUrl !== '')
                    <a href="{{ str_starts_with($linkUrl, '/') ? url($linkUrl) : $linkUrl }}" class="hover:text-accent underline-offset-4 hover:underline">{{ text('topbar_link_label') }}</a>
                @else
                    <button type="button" @click="$store.modal.show()" class="hover:text-accent underline-offset-4 hover:underline">{{ text('topbar_link_label') }}</button>
                @endif
            @endif
            @if (text('topbar_button_label'))<button type="button" @click="$store.modal.show()" class="hidden border-2 border-white px-5 py-1.5 text-sm font-semibold text-white transition-colors hover:bg-white hover:text-ink sm:inline-block">{{ text('topbar_button_label') }}</button>@endif
        </div>
    </div>
</div>
