@if (! empty($section['image']))
<a href="{{ $section['link'] ?? '#' }}" class="promo-banner"><img src="{{ asset('storage/'.$section['image']) }}" alt="{{ $section['title'] ?? '' }}" loading="lazy"></a>
@elseif (! empty($section['title']))
<a href="{{ $section['link'] ?? sroute('products') }}" class="promo-banner txt"><b>{{ $section['title'] }}</b><span>{{ $section['subtitle'] ?? '' }}</span></a>
@endif
