@if ($store->whatsapp)
<section class="wa-cta"><div><h3>{{ $section['title'] ?? 'محتاج مساعدة في الاختيار؟' }}</h3><p>{{ $section['subtitle'] ?? 'كلمنا على واتساب وبنساعدك فوراً' }}</p></div><a class="btn wa" href="{{ \App\Services\Messaging\WhatsApp::link($store->whatsapp) }}">{{ icon('chat', 18) }} تواصل عبر واتساب</a></section>
@endif
