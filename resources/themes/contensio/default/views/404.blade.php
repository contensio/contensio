@extends('theme::layout')

@section('title', '404 — Page Not Found — ' . $site['name'])

@section('content')

<div class="theme-container py-24 text-center">
    <p class="text-8xl font-extrabold text-gray-100 mb-4 leading-none">404</p>
    <h1 class="text-3xl font-bold text-gray-900 mb-4">Page not found</h1>
    <p class="text-gray-500 mb-10">The page you're looking for doesn't exist or has been moved.</p>
    <a href="{{ route('contensio.home') }}"
       class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white font-semibold
              px-6 py-3 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to home
    </a>
</div>

@endsection
