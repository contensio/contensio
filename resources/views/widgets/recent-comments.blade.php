@if(! empty($config['title']))
<h3 class="widget-title">{{ $config['title'] }}</h3>
@endif
<ul class="widget-recent-comments">
    @foreach($comments as $comment)
    @php
        $trans = $comment->content?->translations?->first();
        $slug  = $trans?->slug;
    @endphp
    <li class="widget-recent-comments__item">
        <span class="widget-recent-comments__author">{{ $comment->author_name }}</span>
        @if($slug)
        on <a href="{{ route('contensio.post', $slug) }}" class="widget-recent-comments__post">
            {{ $trans->title }}
        </a>
        @endif
    </li>
    @endforeach
</ul>
