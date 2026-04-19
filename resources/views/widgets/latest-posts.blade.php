@if(! empty($config['title']))
<h3 class="widget-title">{{ $config['title'] }}</h3>
@endif
<ul class="widget-latest-posts">
    @foreach($posts as $post)
    @php
        $trans = $post->translations->first();
        $slug  = $trans?->slug;
    @endphp
    @if($slug)
    <li class="widget-latest-posts__item">
        @if($config['show_image'] && $post->featuredImage)
        <a href="{{ route('contensio.post', $slug) }}" class="widget-latest-posts__thumb">
            <img src="{{ Storage::disk($post->featuredImage->disk)->url($post->featuredImage->file_path) }}"
                 alt="{{ $trans->title }}" loading="lazy">
        </a>
        @endif
        <div class="widget-latest-posts__body">
            <a href="{{ route('contensio.post', $slug) }}" class="widget-latest-posts__title">
                {{ $trans->title }}
            </a>
            @if($config['show_date'])
            <time class="widget-latest-posts__date">{{ $post->published_at?->format('M d, Y') }}</time>
            @endif
            @if($config['show_excerpt'] && $trans->excerpt)
            <p class="widget-latest-posts__excerpt">{{ $trans->excerpt }}</p>
            @endif
        </div>
    </li>
    @endif
    @endforeach
</ul>
