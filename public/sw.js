const CACHE_NAME = 'smartlaundry-cache-disabled';

self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => caches.delete(cache))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request).catch(err => {
      console.warn('Network request failed:', event.request.url);
      return new Response(JSON.stringify({ error: 'Network request failed' }), {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      });
    })
  );
});
