@extends('theme::layout')

@section('title', '404 — Page Not Found — ' . $site['name'])

@section('content')

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-24 text-center">
    <p class="text-6xl font-extrabold text-gray-200 mb-4">404</p>
    <h1 class="text-2xl font-bold text-gray-900 mb-3">Page not found</h1>
    <p class="text-gray-500 mb-8">The page you're looking for doesn't exist or has been moved.</p>
    <a href="{{ route('cms.home') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold
              px-5 py-2.5 rounded-lg transition-colors text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to home
    </a>
</div>

@endsection
