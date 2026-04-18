{{--
 | partials/post-card.blade.php — reusable horizontal post card for archive listings.
 |
 | Expected variables:
 |   $post  — Content model with eager-loaded translations and featuredImage
 |   $lang  — Language model (nullable)
 |
 | Usage:
 |   @foreach($posts as $post)
 |       @include('theme::partials.post-card', ['post' => $post])
 |   @endforeach
--}}
@php
    $trans = $post->translations->first();
    $slug  = $trans?->slug;
    $title = $trans?->title ?? 'Untitled';
    $url   = $slug ? route('contensio.post', $slug) : null;
@endphp

@if($url)
<article class="py-8 flex gap-6 group">

    {{-- Featured image --}}
    @if($post->featuredImage)
    <a href="{{ $url }}" class="shrink-0 hidden sm:block">
        <div class="w-28 h-20 rounded-lg overflow-hidden bg-gray-100">
            <img src="{{ Storage::disk($post->featuredImage->disk)->url($post->featuredImage->file_path) }}"
                 alt="{{ $title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        </div>
    </a>
    @endif

    <div class="flex-1 min-w-0">
        <p class="text-xs text-gray-400 mb-1.5">
            {{ $post->published_at?->format('M d, Y') }}
            @if($post->author)&middot; {{ $post->author->name }}@endif
        </p>
        <h2 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-snug">
            <a href="{{ $url }}">{{ $title }}</a>
        </h2>
        @if($trans?->excerpt)
        <p class="mt-1.5 text-sm text-gray-500 line-clamp-2">{{ $trans->excerpt }}</p>
        @endif
        <a href="{{ $url }}" class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
            Read more
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

</article>
@endif
