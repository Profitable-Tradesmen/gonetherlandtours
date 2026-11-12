@props(['title' => 'Start your adventure'])
{{-- Controlled by Alpine.store('modal'). Focus is trapped while open; Escape closes. --}}
<div x-data x-show="$store.modal.open" x-cloak x-transition.opacity
     @keydown.escape.window="$store.modal.hide()"
     class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center"
     role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="absolute inset-0 bg-ink/70" @click="$store.modal.hide()"></div>
    <div x-show="$store.modal.open" x-trap.noscroll="$store.modal.open"
         class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white p-5 shadow-2xl sm:max-w-xl sm:rounded-2xl sm:p-7">
        <button type="button" @click="$store.modal.hide()" class="absolute right-3 top-3 rounded-full p-2 text-ink hover:bg-sand" aria-label="Close">
            <x-icon name="close" />
        </button>
        <h2 id="modal-title" class="pr-10 text-2xl font-medium sm:text-3xl">{{ $title }}</h2>
        {{ $slot }}
    </div>
</div>
