// Offline-first service worker (per store). Strategy:
//  - static assets (css/js/fonts/images): cache-first
//  - HTML pages: network-first with cache fallback, then /offline
//  - POST (checkout, cart): never cached
const CACHE = 'store-{{ $version }}';
const OFFLINE = @json(sroute('offline'));
const PRECACHE = [OFFLINE, @json(asset('css/app.css')), @json(sroute('home'))];

self.addEventListener('install', e => { e.waitUntil(caches.open(CACHE).then(c => c.addAll(PRECACHE)).then(() => self.skipWaiting())); });
self.addEventListener('activate', e => { e.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(k => k.startsWith('store-') && k !== CACHE).map(k => caches.delete(k)))).then(() => self.clients.claim())); });

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  const isAsset = /\.(css|js|png|jpe?g|webp|svg|woff2?)$/.test(url.pathname) || url.pathname.includes('/storage/');
  if (isAsset) {
    e.respondWith(caches.match(req).then(hit => hit || fetch(req).then(res => { const copy = res.clone(); caches.open(CACHE).then(c => c.put(req, copy)); return res; })));
    return;
  }
  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).then(res => { if (res.ok && !url.pathname.includes('/checkout') && !url.pathname.includes('/cart')) { const copy = res.clone(); caches.open(CACHE).then(c => c.put(req, copy)); } return res; })
      .catch(() => caches.match(req).then(hit => hit || caches.match(OFFLINE))));
  }
});
