{{--
 | Contact Info partial
 | Variables: $items (array), $locale
--}}
<div class="space-y-4">
    @foreach($items as $item)
    @php
        $label = $item['label'][$locale] ?? $item['label']['en'] ?? '';
        $value = $item['value'][$locale] ?? $item['value']['en'] ?? '';
        $icon  = $item['icon'] ?? 'bi-info-circle';
        $link  = $item['link'] ?? '';
    @endphp
    @if($value)
    <div class="flex items-start gap-3">
        <i class="bi {{ $icon }} text-xl mt-0.5 shrink-0" style="color: var(--theme-primary)"></i>
        <div>
            @if($label)
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-0.5">{{ $label }}</p>
            @endif
            @if($link)
            <a href="{{ $link }}" class="text-sm text-gray-700 hover:underline transition-colors">{{ $value }}</a>
            @else
            <p class="text-sm text-gray-700">{{ $value }}</p>
            @endif
        </div>
    </div>
    @endif
    @endforeach
</div>
