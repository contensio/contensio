{{--
    Renders a single content block.
    Variables: $block (array), $langId (int)

    Block structure:
        type         — block type key
        is_active    — bool
        data         — non-translatable field values
        translations — [langId => [fieldKey => value]]
--}}

@php
    $type   = $block['type']      ?? '';
    $active = $block['is_active'] ?? true;
    $data   = $block['data']      ?? [];
    $trans  = $block['translations'][$langId] ?? $block['translations'][array_key_first($block['translations'] ?? [])] ?? [];
@endphp

@if($active)

@switch($type)

{{-- ── Rich Text ──────────────────────────────────────────────────────── --}}
@case('richtext')
<div class="prose max-w-none">
    {!! $trans['content'] ?? '' !!}
</div>
@break

{{-- ── Heading ────────────────────────────────────────────────────────── --}}
@case('heading')
@php
    $level = $data['level'] ?? 'h2';
    $align = $data['alignment'] ?? 'left';
    $text  = $trans['text'] ?? '';
    $alignClass = match($align) {
        'center' => 'text-center',
        'right'  => 'text-right',
        default  => 'text-left',
    };
    $sizeClass = match($level) {
        'h2' => 'text-2xl sm:text-3xl font-bold text-gray-900',
        'h3' => 'text-xl sm:text-2xl font-bold text-gray-900',
        'h4' => 'text-lg sm:text-xl font-semibold text-gray-900',
        default => 'text-2xl font-bold text-gray-900',
    };
@endphp
<{{ $level }} class="{{ $sizeClass }} {{ $alignClass }}">{{ $text }}</{{ $level }}>
@break

{{-- ── Quote ──────────────────────────────────────────────────────────── --}}
@case('quote')
<blockquote class="border-l-4 border-blue-500 pl-5 py-1 my-6">
    <p class="text-lg text-gray-700 italic leading-relaxed">{{ $trans['quote'] ?? '' }}</p>
    @if(! empty($trans['author']))
    <footer class="mt-3 text-sm text-gray-500">
        — {{ $trans['author'] }}
        @if(! empty($trans['author_title']))
        <span class="text-gray-400">, {{ $trans['author_title'] }}</span>
        @endif
    </footer>
    @endif
</blockquote>
@break

{{-- ── Code ───────────────────────────────────────────────────────────── --}}
@case('code')
@php $lang = $data['language'] ?? 'plaintext'; @endphp
<div class="my-6">
    @if($lang !== 'plaintext')
    <div class="flex items-center justify-between bg-slate-700 rounded-t-lg px-4 py-1.5">
        <span class="text-xs font-mono text-slate-400">{{ strtoupper($lang) }}</span>
    </div>
    @endif
    <pre class="bg-slate-800 {{ $lang !== 'plaintext' ? 'rounded-b-lg rounded-tr-lg' : 'rounded-lg' }} p-4 overflow-x-auto text-sm text-slate-100"><code>{{ $data['code'] ?? '' }}</code></pre>
</div>
@break

{{-- ── Image ──────────────────────────────────────────────────────────── --}}
@case('image')
@php
    $url     = $data['url']      ?? '';
    $alt     = $trans['alt']     ?? '';
    $caption = $trans['caption'] ?? '';
    $width   = $data['width']    ?? 'normal';
    $widthClass = match($width) {
        'full'   => 'w-full',
        'wide'   => 'max-w-3xl mx-auto',
        'narrow' => 'max-w-sm mx-auto',
        default  => 'max-w-2xl mx-auto',
    };
@endphp
@if($url)
<figure class="{{ $widthClass }} my-6">
    <img src="{{ $url }}" alt="{{ $alt }}" class="w-full rounded-xl">
    @if($caption)
    <figcaption class="mt-2 text-center text-sm text-gray-400">{{ $caption }}</figcaption>
    @endif
</figure>
@endif
@break

{{-- ── Divider ────────────────────────────────────────────────────────── --}}
@case('divider')
<hr class="my-8 border-gray-200">
@break

{{-- ── Spacer ─────────────────────────────────────────────────────────── --}}
@case('spacer')
@php $height = $data['height'] ?? 'md'; @endphp
<div class="{{ match($height) { 'sm' => 'h-4', 'lg' => 'h-16', 'xl' => 'h-24', default => 'h-8' } }}"></div>
@break

{{-- ── Fallback ───────────────────────────────────────────────────────── --}}
@default
{{-- Unknown block type — render nothing on the frontend --}}
@endswitch

@endif
