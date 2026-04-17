<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title><![CDATA[{{ $site['name'] }}]]></title>
        <link>{{ $site['url'] }}</link>
        <description><![CDATA[{{ $site['tagline'] }}]]></description>
        <atom:link href="{{ url('/feed') }}" rel="self" type="application/rss+xml"/>
        <generator>Contensio</generator>
        <lastBuildDate>{{ now()->toRfc2822() }}</lastBuildDate>

        @foreach($posts as $post)
        @php
            $translation = $post->translations->first();
            $title       = $translation?->title ?? 'Untitled';
            $slug        = $translation?->slug ?? '';
            $excerpt     = $translation?->excerpt
                ? strip_tags($translation->excerpt)
                : \Illuminate\Support\Str::limit(strip_tags((string) ($translation?->body ?? '')), 300);
            $link        = $slug ? route('contensio.post', $slug) : $site['url'];
            $pubDate     = $post->published_at?->toRfc2822() ?? $post->created_at->toRfc2822();
        @endphp
        <item>
            <title><![CDATA[{{ $title }}]]></title>
            <link>{{ $link }}</link>
            <guid isPermaLink="true">{{ $link }}</guid>
            <description><![CDATA[{{ $excerpt }}]]></description>
            <pubDate>{{ $pubDate }}</pubDate>
            @if($post->author)
            <dc:creator><![CDATA[{{ $post->author->name }}]]></dc:creator>
            @endif
        </item>
        @endforeach

    </channel>
</rss>
