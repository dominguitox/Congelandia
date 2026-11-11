/* Variables sustituidas por PwaController a partir del manifiesto de Vite. */
const CACHE = __CACHE_NAME__;
const ASSETS = __ASSETS__;
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(ASSETS)));
});
self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(keys
        .filter(key => key.startsWith('congelandia-shell-') && key !== CACHE)
        .map(key => caches.delete(key)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin || event.request.method !== 'GET') return;
    // Nunca guardar páginas autenticadas, API, imágenes de clientes o CSRF.
    if (ASSETS.includes(url.pathname)) {
        event.respondWith(caches.open(CACHE).then(async cache => (await cache.match(url.pathname)) || fetch(event.request)));
        return;
    }
    if (event.request.mode === 'navigate' && ['/pos', '/inventario'].includes(url.pathname.replace(/\/$/, ''))) {
        const fallback = () => caches.open(CACHE).then(cache => cache.match('/offline'));
        event.respondWith(fetch(event.request).then(response => response.status >= 500 ? fallback() : response).catch(fallback));
    }
});
