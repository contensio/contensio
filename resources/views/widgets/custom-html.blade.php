@if(! empty($config['title']))
<h3 class="widget-title">{{ $config['title'] }}</h3>
@endif
{!! $config['content'] !!}
