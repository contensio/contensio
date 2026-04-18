{{--
 | Image partial
 | Variables: $url, $alt, $caption, $rounded
--}}
<figure class="my-0">
    <img src="{{ $url }}"
         alt="{{ $alt }}"
         class="w-full object-cover {{ $rounded ? 'rounded-xl' : '' }}">
    @if($caption)
    <figcaption class="mt-2 text-sm text-gray-400 text-center">{{ $caption }}</figcaption>
    @endif
</figure>
