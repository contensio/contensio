{{--
 | Social Links partial
 | Variables: $links (assoc array platform => url)
--}}
@php
$socialIcons = [
    'twitter'   => 'bi-twitter-x',
    'linkedin'  => 'bi-linkedin',
    'facebook'  => 'bi-facebook',
    'instagram' => 'bi-instagram',
    'youtube'   => 'bi-youtube',
    'github'    => 'bi-github',
    'tiktok'    => 'bi-tiktok',
    'pinterest' => 'bi-pinterest',
    'whatsapp'  => 'bi-whatsapp',
];
$socialLabels = [
    'twitter' => 'Twitter', 'linkedin' => 'LinkedIn', 'facebook' => 'Facebook',
    'instagram' => 'Instagram', 'youtube' => 'YouTube', 'github' => 'GitHub',
    'tiktok' => 'TikTok', 'pinterest' => 'Pinterest', 'whatsapp' => 'WhatsApp',
];
@endphp
<div class="flex flex-wrap gap-3">
    @foreach($links as $platform => $url)
    @if($url)
    <a href="{{ $url }}" target="_blank" rel="noopener"
       aria-label="{{ $socialLabels[$platform] ?? $platform }}"
       class="flex items-center justify-center w-10 h-10 rounded-xl border border-gray-200 text-gray-500
              hover:border-gray-400 hover:text-gray-700 transition-colors">
        <i class="bi {{ $socialIcons[$platform] ?? 'bi-link-45deg' }} text-lg"></i>
    </a>
    @endif
    @endforeach
</div>
