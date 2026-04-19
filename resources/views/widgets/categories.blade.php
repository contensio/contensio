@if(! empty($config['title']))
<h3 class="widget-title">{{ $config['title'] }}</h3>
@endif
<ul class="widget-categories">
    @foreach($categories as $cat)
    @php $trans = $cat->translations->first(); $slug = $trans?->slug; @endphp
    @if($slug)
    <li class="widget-categories__item">
        <a href="{{ route('contensio.category', $slug) }}" class="widget-categories__link">
            {{ $trans->name ?? $cat->slug }}
            @if($config['show_count'])
            <span class="widget-categories__count">{{ $cat->contents_count }}</span>
            @endif
        </a>
    </li>
    @endif
    @endforeach
</ul>
