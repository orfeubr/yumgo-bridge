const CACHE_VERSION = 'yumgo-v3';
const IMAGE_CACHE = 'yumgo-images-v3';
const STATIC_CACHE = 'yumgo-static-v3';
const API_CACHE = 'yumgo-api-v3';

// Cache de recursos estáticos essenciais
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  'https://cdn.tailwindcss.com',
  'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
  'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap'
];

// Tempo de expiração do cache (em milissegundos)
const CACHE_EXPIRATION = {
  images: 30 * 24 * 60 * 60 * 1000,  // 30 dias para imagens
  static: 7 * 24 * 60 * 60 * 1000,   // 7 dias para estáticos
  api: 5 * 60 * 1000                  // 5 minutos para API
};

// Instalar Service Worker e cachear recursos essenciais
self.addEventListener('install', event => {
  console.log('🚀 Service Worker instalando...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('✅ Cache estático aberto');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => self.skipWaiting())
  );
});

// Ativar e limpar caches antigos
self.addEventListener('activate', event => {
  console.log('🔄 Service Worker ativando...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== STATIC_CACHE &&
              cacheName !== IMAGE_CACHE &&
              cacheName !== API_CACHE) {
            console.log('🗑️ Removendo cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Verificar se o cache expirou
function isCacheExpired(response, maxAge) {
  if (!response) return true;

  const cachedTime = response.headers.get('sw-cached-time');
  if (!cachedTime) return true;

  const age = Date.now() - parseInt(cachedTime);
  return age > maxAge;
}

// Adicionar timestamp ao cache
function addTimestampToResponse(response) {
  const clonedResponse = response.clone();
  const headers = new Headers(clonedResponse.headers);
  headers.set('sw-cached-time', Date.now().toString());

  return clonedResponse.blob().then(blob => {
    return new Response(blob, {
      status: clonedResponse.status,
      statusText: clonedResponse.statusText,
      headers: headers
    });
  });
}

// Estratégia: Cache First com expiração (para imagens)
function cacheFirstStrategy(request, cacheName, maxAge) {
  return caches.open(cacheName).then(cache => {
    return cache.match(request).then(cachedResponse => {
      // Se tem cache E não expirou, retornar
      if (cachedResponse && !isCacheExpired(cachedResponse, maxAge)) {
        console.log('📦 Cache hit:', request.url.substring(0, 100));
        return cachedResponse;
      }

      // Buscar da rede e cachear
      return fetch(request).then(networkResponse => {
        if (networkResponse && networkResponse.status === 200) {
          return addTimestampToResponse(networkResponse).then(timestampedResponse => {
            cache.put(request, timestampedResponse.clone());
            console.log('💾 Imagem cacheada:', request.url.substring(0, 100));
            return timestampedResponse;
          });
        }
        return networkResponse;
      }).catch(error => {
        console.log('⚠️ Erro na rede, usando cache antigo se disponível');
        return cachedResponse || Promise.reject(error);
      });
    });
  });
}

// Estratégia: Network First com fallback (para APIs)
function networkFirstStrategy(request, cacheName, maxAge) {
  return fetch(request)
    .then(response => {
      if (response && response.status === 200 && request.method === 'GET') {
        return addTimestampToResponse(response).then(timestampedResponse => {
          caches.open(cacheName).then(cache => {
            cache.put(request, timestampedResponse.clone());
          });
          return timestampedResponse;
        });
      }
      return response;
    })
    .catch(() => {
      return caches.match(request).then(cachedResponse => {
        if (cachedResponse && !isCacheExpired(cachedResponse, maxAge)) {
          console.log('📡 API offline, usando cache');
          return cachedResponse;
        }
        return new Response(JSON.stringify({ error: 'Offline', cached: false }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' }
        });
      });
    });
}

// Interceptar requisições
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  const request = event.request;

  // Ignorar requisições não-GET (POST, PUT, DELETE)
  if (request.method !== 'GET') {
    return;
  }

  // 1. IMAGENS - Cache First (30 dias)
  if (request.destination === 'image' ||
      url.pathname.includes('/storage/') ||
      url.pathname.includes('/tenancy/') ||
      url.pathname.includes('/images/') ||
      url.pathname.includes('/logos/') ||
      /\.(jpg|jpeg|png|gif|webp|svg|ico)$/i.test(url.pathname)) {

    event.respondWith(cacheFirstStrategy(request, IMAGE_CACHE, CACHE_EXPIRATION.images));
    return;
  }

  // 2. ASSETS ESTÁTICOS - Cache First (7 dias)
  if (/\.(js|css|woff|woff2|ttf|eot)$/i.test(url.pathname) ||
      url.hostname.includes('cdn.')) {

    event.respondWith(cacheFirstStrategy(request, STATIC_CACHE, CACHE_EXPIRATION.static));
    return;
  }

  // 3. APIs PÚBLICAS - Network First com cache de 5 min
  if (url.pathname.includes('/api/v1/products') ||
      url.pathname.includes('/api/v1/categories') ||
      url.pathname.includes('/api/v1/settings')) {

    event.respondWith(networkFirstStrategy(request, API_CACHE, CACHE_EXPIRATION.api));
    return;
  }

  // 4. HTML e outras requisições - Network First sem cache
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});

// Mensagem do service worker
self.addEventListener('message', event => {
  if (event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }

  if (event.data.action === 'clearCache') {
    event.waitUntil(
      caches.keys().then(cacheNames => {
        return Promise.all(cacheNames.map(name => caches.delete(name)));
      })
    );
  }
});

console.log('✅ Service Worker carregado - Cache de imagens ativado!');
