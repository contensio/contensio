{{--
 | Business Hours partial
 | Variables: $title (string), $rows (array), $locale
--}}
<div>
    @if($title)
    <h3 class="text-sm font-bold text-gray-800 mb-3">{{ $title }}</h3>
    @endif
    <table class="w-full text-sm border-collapse">
        <tbody>
            @foreach($rows as $row)
            @php
                $day   = $row['day'][$locale]   ?? $row['day']['en']   ?? '';
                $hours = $row['hours'][$locale] ?? $row['hours']['en'] ?? '';
            @endphp
            @if($day || $hours)
            <tr class="border-b border-gray-100 last:border-0">
                <td class="py-2 pr-6 font-medium text-gray-700 whitespace-nowrap">{{ $day }}</td>
                <td class="py-2 text-gray-500">{{ $hours }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
