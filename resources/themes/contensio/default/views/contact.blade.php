@extends('theme::layout')

@section('title', apply_filters('contensio/frontend/page-title', __('contensio::frontend.contact.page_title') . ' — ' . $site['name']))

@section('content')

@php
    $locale     = app()->getLocale();
    $appearance = $settings['appearance'] ?? ['field_size' => 'normal', 'layout' => 'classic'];
    $layout     = $appearance['layout'] ?? 'classic';
    $fieldSize  = $appearance['field_size'] ?? 'normal';
    $sections   = $settings['sections'] ?? [];
    $gdpr       = $settings['gdpr'] ?? ['enabled' => false];
    $fileCfg    = $settings['file_uploads'] ?? ['enabled' => false];
    $antispam   = $settings['antispam'] ?? [];
    $recaptcha  = $antispam['recaptcha'] ?? [];
    $turnstile  = $antispam['turnstile'] ?? [];

    $inputSizeClass = match($fieldSize) {
        'small' => 'px-3 py-1.5 text-sm',
        'large' => 'px-4 py-3.5 text-lg',
        default => 'px-4 py-2.5 text-base',
    };
    $labelSizeClass = match($fieldSize) {
        'small' => 'text-xs',
        'large' => 'text-base',
        default => 'text-sm',
    };
    $btnSizeClass = match($fieldSize) {
        'small' => 'px-4 py-2 text-sm',
        'large' => 'px-8 py-4 text-lg',
        default => 'px-6 py-3 text-base',
    };
@endphp

{{-- reCAPTCHA script --}}
@if(($recaptcha['enabled'] ?? false) && !empty($recaptcha['site_key']))
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif

{{-- Turnstile script --}}
@if(($turnstile['enabled'] ?? false) && !empty($turnstile['site_key']))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

@php
    $outerClass = 'theme-container px-4 sm:px-6 py-12';
@endphp

<div class="{{ $outerClass }}">

    @if($layout === 'split')
    {{-- Split layout: info on left, form on right --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div class="space-y-8">
            @foreach($sections as $section)
                @php $sData = $section['data'] ?? []; @endphp
                @if($section['type'] === 'text')
                    @php $html = $sData[$locale] ?? $sData['en'] ?? ''; @endphp
                    @if($html)<div class="prose">{!! $html !!}</div>@endif
                @elseif($section['type'] === 'map' && !empty($sData['address']))
                    @php
                        $zoom   = (int)($sData['zoom'] ?? 14);
                        $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($sData['address']) . '&z=' . $zoom . '&output=embed';
                    @endphp
                    <div class="rounded-xl overflow-hidden border border-gray-200 aspect-video">
                        <iframe src="{{ $mapSrc }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @elseif($section['type'] === 'accordion' && !empty($sData['items']))
                    @include('theme::partials.contact-accordion', ['items' => $sData['items'], 'locale' => $locale])
                @elseif($section['type'] === 'contact_info' && !empty($sData['items']))
                    @include('theme::partials.contact-info', ['items' => $sData['items'], 'locale' => $locale])
                @elseif($section['type'] === 'hours' && !empty($sData['rows']))
                    @include('theme::partials.contact-hours', ['title' => $sData['title'][$locale] ?? $sData['title']['en'] ?? '', 'rows' => $sData['rows'], 'locale' => $locale])
                @elseif($section['type'] === 'social' && !empty($sData['links']))
                    @include('theme::partials.contact-social', ['links' => $sData['links']])
                @elseif($section['type'] === 'image' && !empty($sData['url']))
                    @include('theme::partials.contact-image', ['url' => $sData['url'], 'alt' => $sData['alt'][$locale] ?? $sData['alt']['en'] ?? '', 'caption' => $sData['caption'][$locale] ?? $sData['caption']['en'] ?? '', 'rounded' => $sData['rounded'] ?? true])
                @elseif($section['type'] === 'team' && !empty($sData['members']))
                    @include('theme::partials.contact-team', ['members' => $sData['members'], 'locale' => $locale])
                @elseif($section['type'] === 'cta' && !empty($sData['url']))
                    @include('theme::partials.contact-cta', ['label' => $sData['label'][$locale] ?? $sData['label']['en'] ?? '', 'url' => $sData['url'], 'style' => $sData['style'] ?? 'primary', 'align' => $sData['align'] ?? 'left', 'description' => $sData['description'][$locale] ?? $sData['description']['en'] ?? '', 'newTab' => $sData['new_tab'] ?? false])
                @endif
            @endforeach
        </div>
        <div>
            @include('theme::partials.contact-form', compact('fields', 'lang', 'settings', 'gdpr', 'fileCfg', 'antispam', 'recaptcha', 'turnstile', 'locale', 'inputSizeClass', 'labelSizeClass', 'btnSizeClass'))
        </div>
    </div>

    @else
    {{-- Classic, wide, card layouts: sections stacked, then form --}}
    @foreach($sections as $section)
        @php $sData = $section['data'] ?? []; @endphp
        @if($section['type'] === 'text')
            @php $html = $sData[$locale] ?? $sData['en'] ?? ''; @endphp
            @if($html)<div class="prose mb-8">{!! $html !!}</div>@endif
        @elseif($section['type'] === 'map' && !empty($sData['address']))
            @php
                $zoom   = (int)($sData['zoom'] ?? 14);
                $mapSrc = 'https://maps.google.com/maps?q=' . urlencode($sData['address']) . '&z=' . $zoom . '&output=embed';
            @endphp
            <div class="rounded-xl overflow-hidden border border-gray-200 aspect-video mb-8">
                <iframe src="{{ $mapSrc }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        @elseif($section['type'] === 'accordion' && !empty($sData['items']))
            @include('theme::partials.contact-accordion', ['items' => $sData['items'], 'locale' => $locale])
        @elseif($section['type'] === 'contact_info' && !empty($sData['items']))
            @include('theme::partials.contact-info', ['items' => $sData['items'], 'locale' => $locale])
        @elseif($section['type'] === 'hours' && !empty($sData['rows']))
            @include('theme::partials.contact-hours', ['title' => $sData['title'][$locale] ?? $sData['title']['en'] ?? '', 'rows' => $sData['rows'], 'locale' => $locale])
        @elseif($section['type'] === 'social' && !empty($sData['links']))
            @include('theme::partials.contact-social', ['links' => $sData['links']])
        @elseif($section['type'] === 'image' && !empty($sData['url']))
            @include('theme::partials.contact-image', ['url' => $sData['url'], 'alt' => $sData['alt'][$locale] ?? $sData['alt']['en'] ?? '', 'caption' => $sData['caption'][$locale] ?? $sData['caption']['en'] ?? '', 'rounded' => $sData['rounded'] ?? true])
        @elseif($section['type'] === 'team' && !empty($sData['members']))
            @include('theme::partials.contact-team', ['members' => $sData['members'], 'locale' => $locale])
        @elseif($section['type'] === 'cta' && !empty($sData['url']))
            @include('theme::partials.contact-cta', ['label' => $sData['label'][$locale] ?? $sData['label']['en'] ?? '', 'url' => $sData['url'], 'style' => $sData['style'] ?? 'primary', 'align' => $sData['align'] ?? 'left', 'description' => $sData['description'][$locale] ?? $sData['description']['en'] ?? '', 'newTab' => $sData['new_tab'] ?? false])
        @elseif($section['type'] === 'form')
            @include('theme::partials.contact-form', compact('fields', 'lang', 'settings', 'gdpr', 'fileCfg', 'antispam', 'recaptcha', 'turnstile', 'locale', 'inputSizeClass', 'labelSizeClass', 'btnSizeClass'))
        @endif
    @endforeach

    {{-- If no explicit form section exists, render the form at the end --}}
    @php $hasFormSection = collect($sections)->contains('type', 'form'); @endphp
    @if(!$hasFormSection)
        @include('theme::partials.contact-form', compact('fields', 'lang', 'settings', 'gdpr', 'fileCfg', 'antispam', 'recaptcha', 'turnstile', 'locale', 'inputSizeClass', 'labelSizeClass', 'btnSizeClass'))
    @endif
    @endif

</div>

@endsection
