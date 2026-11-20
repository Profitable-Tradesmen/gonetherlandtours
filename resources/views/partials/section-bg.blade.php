@php
    // A section background chosen in the admin: a photo, an animated GIF or a short video, under a dark tint (or nothing = the section's own color).
    // Expects $bg (path), optional $poster / $posterAlt (photo for phones and while a video loads) and $tint ('light' | 'medium' | 'strong').
    $bg = (string) ($bg ?? '');
    $ext = strtolower(pathinfo($bg, PATHINFO_EXTENSION));
    $tintClass = ['light' => 'bg-brand-900/30', 'medium' => 'bg-brand-900/45', 'strong' => 'bg-brand-900/65'][$tint ?? 'medium'] ?? 'bg-brand-900/45';
@endphp
@if ($bg !== '')
    @if (! empty($poster))<x-picture :src="$poster" :alt="$posterAlt ?? ''" :width="1600" :height="900" class="absolute inset-0 -z-30 h-full w-full object-cover" sizes="100vw" />@endif
    @if (in_array($ext, ['mp4', 'webm'], true))
        {{-- loads after the page, on tablets and desktops only (initBgVideo in app.js); phones, Data Saver and reduced motion keep the photo --}}
        <video muted loop playsinline aria-hidden="true" preload="none" data-bg-video class="absolute inset-0 -z-20 h-full w-full object-cover"><source data-src="{{ asset($bg) }}" type="video/{{ $ext }}"></video>
    @elseif ($ext === 'gif')
        <img src="{{ asset($bg) }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 -z-20 h-full w-full object-cover">
    @else
        <x-picture :src="$bg" alt="" :width="1600" :height="900" class="absolute inset-0 -z-20 h-full w-full object-cover" sizes="100vw" />
    @endif
    @if (in_array($ext, ['mp4', 'webm'], true))
        {{-- video backgrounds get the same layered shading as the home hero (darker at the bottom and the left) so text stays readable over moving footage --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink/80 via-ink/40 to-ink/30"></div>
        <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-ink/60 to-transparent"></div>
        <div class="absolute inset-0 -z-10 hidden bg-gradient-to-r from-ink/60 via-ink/20 to-transparent lg:block"></div>
    @else
        <div class="absolute inset-0 -z-10 {{ $tintClass }}"></div>
    @endif
@endif
