{{--
    Renders a menu tree as <ul><li> markup.
    Variables:
        $items  — array returned by theme_menu($location)
        $class  — optional class for the root <ul>
        $depth  — internal, tracks recursion depth
--}}

@php
    $items  = $items ?? [];
    $class  = $class ?? '';
    $depth  = $depth ?? 0;
@endphp

@if(! empty($items))
<ul class="{{ $class }} {{ $depth > 0 ? 'submenu' : '' }}">
    @foreach($items as $item)
    <li class="menu-item {{ $item['active'] ? 'is-active' : '' }} {{ !empty($item['children']) ? 'has-children' : '' }}">
        <a href="{{ $item['url'] }}"
           target="{{ $item['target'] }}"
           @if($item['target'] === '_blank') rel="noopener" @endif
           class="menu-link {{ $item['active'] ? 'is-active' : '' }}">
            @if(! empty($item['icon']))
            <i class="bi {{ $item['icon'] }}"></i>
            @endif
            <span>{{ $item['label'] }}</span>
        </a>

        @if(! empty($item['children']))
            @include('theme::partials.menu', [
                'items' => $item['children'],
                'depth' => $depth + 1,
                'class' => '',
            ])
        @endif
    </li>
    @endforeach
</ul>
@endif
