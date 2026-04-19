@if(! empty($config['title']))
<h3 class="widget-title">{{ $config['title'] }}</h3>
@endif
@php
    $range = max(1, $max - $min);
@endphp
<div class="widget-tag-cloud">
    @foreach($tags as $tag)
    @php
        $trans = $tag->translations->first();
        $slug  = $trans?->slug;
        // Scale font size between 0.85rem and 1.4rem based on usage
        $ratio    = $range > 0 ? ($tag->contents_count - $min) / $range : 0;
        $fontSize = round(0.85 + ($ratio * 0.55), 2);
    @endphp
    @if($slug)
    <a href="{{ route('contensio.tag', $slug) }}"
       class="widget-tag-cloud__tag"
       style="font-size: {{ $fontSize }}rem;">
        {{ $trans->name ?? $tag->slug }}
    </a>
    @endif
    @endforeach
</div>
