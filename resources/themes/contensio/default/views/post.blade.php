@extends('theme::layout')

@section('title', ($translation->meta_title ?: $translation->title) . ' — ' . $site['name'])
@section('meta_title', $translation->meta_title ?: $translation->title)

@if($translation->meta_description)
@section('meta_description', $translation->meta_description)
@elseif($translation->excerpt)
@section('meta_description', $translation->excerpt)
@endif

@section('og_type', 'article')

@if($content->featuredImage)
@section('og_image', Storage::disk($content->featuredImage->disk)->url($content->featuredImage->file_path))
@endif

@section('content')

<article class="theme-container px-4 sm:px-6 py-12">

    {{-- Back to blog --}}
    <a href="{{ route('contensio.blog') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 mb-8 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Blog
    </a>

    {{-- Featured image --}}
    @if($content->featuredImage)
    <div class="aspect-video rounded-2xl overflow-hidden mb-8 bg-gray-100">
        <img src="{{ Storage::disk($content->featuredImage->disk)->url($content->featuredImage->file_path) }}"
             alt="{{ $translation->title }}"
             class="w-full h-full object-cover">
    </div>
    @endif

    {{-- Meta --}}
    <div class="flex items-center gap-3 text-sm text-gray-400 mb-6">
        @if($content->author)
        <span class="font-medium text-gray-600">{{ $content->author->name }}</span>
        <span>&middot;</span>
        @endif
        <time datetime="{{ $content->published_at?->toDateString() }}">
            {{ $content->published_at?->format('M d, Y') }}
        </time>
        {!! \Contensio\Support\Hook::render('contensio/frontend/post-meta', $content, $translation) !!}
    </div>

    {{-- Title --}}
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-6">
        {{ $translation->title }}
    </h1>

    {!! \Contensio\Support\WidgetArea::render('post-after-title') !!}

    {{-- Excerpt --}}
    @if($translation->excerpt)
    <p class="text-lg text-gray-500 leading-relaxed mb-8 border-l-4 border-gray-200 pl-4">
        {{ $translation->excerpt }}
    </p>
    @endif

    {!! \Contensio\Support\Hook::render('contensio/frontend/post-before-content', $content, $translation) !!}

    {{-- Blocks --}}
    <div class="space-y-6 contensio-post-body">
        @foreach($content->blocks ?? [] as $block)
            @include('theme::partials.block', ['block' => $block, 'langId' => $lang?->id])
        @endforeach
    </div>

    {{-- Custom Fields --}}
    @if(! empty($fieldGroups) && $fieldGroups->isNotEmpty())
    @foreach($fieldGroups as $group)
    @php
        $langId = $lang?->id;
        $fieldsWithValues = $group->fields->filter(function ($field) use ($fieldValues, $langId) {
            $key   = $field->is_translatable ? $field->id . ':' . $langId : $field->id . ':_';
            $value = $fieldValues[$key] ?? null;
            return isset($value) && $value !== '' && $value !== '[]';
        });
    @endphp
    @if($fieldsWithValues->isNotEmpty())
    @foreach($fieldsWithValues->groupBy('section') as $sectionName => $sectionFields)
    <div class="mt-10 pt-8 border-t border-gray-200">
        @if($sectionName)
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">{{ $sectionName }}</h3>
        @endif
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($sectionFields as $field)
            @php
                $key      = $field->is_translatable ? $field->id . ':' . $langId : $field->id . ':_';
                $rawValue = $fieldValues[$key] ?? '';
                $fTrans   = $field->translations->firstWhere('language_id', $langId) ?? $field->translations->first();
                $label    = $fTrans?->label ?? $field->key;

                $displayValue = $rawValue;
                if (in_array($field->type, ['multi-select', 'checkbox']) && is_string($rawValue)) {
                    $decoded = json_decode($rawValue, true);
                    if (is_array($decoded)) {
                        $displayValue = implode(', ', $decoded);
                    }
                }
            @endphp
            <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                <dt class="text-xs font-medium text-gray-400 mb-1">{{ $label }}</dt>
                <dd class="text-sm font-medium text-gray-900 break-words">{{ $displayValue }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
    @endforeach
    @endif
    @endforeach
    @endif

    {!! \Contensio\Support\Hook::render('contensio/frontend/post-after-content', $content, $translation) !!}

</article>

{!! \Contensio\Support\WidgetArea::render('after-post') !!}

@include('contensio::frontend.partials.comments', [
    'content'         => $content,
    'comments'        => $comments,
    'commentsEnabled' => $commentsEnabled,
])

{!! \Contensio\Support\WidgetArea::render('after-comments') !!}

@endsection
