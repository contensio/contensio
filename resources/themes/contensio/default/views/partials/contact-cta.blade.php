{{--
 | CTA Button partial
 | Variables: $label, $url, $style, $align, $description, $newTab
--}}
@php
$alignClass = match($align) {
    'center' => 'text-center',
    'right'  => 'text-right',
    default  => 'text-left',
};
$btnClass = match($style) {
    'secondary' => 'theme-btn-secondary inline-flex items-center gap-2 px-6 py-3 rounded-lg font-semibold text-base transition-colors',
    'outline'   => 'inline-flex items-center gap-2 px-6 py-3 rounded-lg font-semibold text-base border-2 border-current transition-colors',
    default     => 'theme-btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-lg font-semibold text-base transition-colors',
};
@endphp
<div class="{{ $alignClass }}">
    @if($description)
    <p class="text-sm text-gray-500 mb-3">{{ $description }}</p>
    @endif
    @if($url && $label)
    <a href="{{ $url }}"
       @if($newTab) target="_blank" rel="noopener" @endif
       class="{{ $btnClass }}">
        {{ $label }}
    </a>
    @endif
</div>
