{{--
 | Contact accordion partial — collapsible items (FAQ etc.)
 | Variables: $items (array of {title:{locale:string}, content:{locale:string}}), $locale
--}}
<div class="divide-y divide-gray-200 border border-gray-200 rounded-xl mt-8 mb-8 overflow-hidden">
    @foreach($items as $item)
    @php
        $title   = $item['title'][$locale]   ?? $item['title']['en']   ?? '';
        $content = $item['content'][$locale] ?? $item['content']['en'] ?? '';
    @endphp
    @if($title || $content)
    <details class="group">
        <summary class="flex items-center justify-between gap-4 px-5 py-4 cursor-pointer list-none select-none
                        font-medium text-gray-900 hover:bg-gray-50 transition-colors">
            <span>{{ $title }}</span>
            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200 group-open:rotate-180"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
        <div class="px-5 pb-5 pt-1 text-gray-600 leading-relaxed">
            {!! nl2br(e($content)) !!}
        </div>
    </details>
    @endif
    @endforeach
</div>
