const CACHE_NAME = "timeclock-v14-manifest-icon-fix";

const URLS_TO_CACHE = [
  "/auditor-app/public/manifest.json",
  "/auditor-app/public/assets/css/app.css",
  "/auditor-app/public/assets/js/app.js",
  "/auditor-app/public/assets/img/icon-192.png",
  "/auditor-app/public/assets/img/icon-512.png",
  "/auditor-app/public/assets/img/timeclock-logo.png"
];

/*
 * Rotas dinâmicas que nunca podem ser obtidas
 * através do Cache Storage da aplicação.
 */
const NEVER_CACHE_PATHS = [
  "/auditor-app/public/export/pdf",
  "/auditor-app/public/report/monthly-preview",
  "/auditor-app/public/report/monthly-download"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async (cache) => {
      /*
       * Guarda somente os ficheiros estáticos conhecidos.
       * cache: reload impede reutilizar uma versão antiga
       * durante a instalação do novo service worker.
       */
      await Promise.all(
        URLS_TO_CACHE.map(async (url) => {
          try {
            const request = new Request(url, {
              cache: "reload"
            });

            await cache.add(request);
          } catch (error) {
            /*
             * Um ficheiro estático temporariamente indisponível
             * não deve impedir a instalação da aplicação inteira.
             */
            console.warn(
              "[TimeClock SW] Não foi possível guardar no cache:",
              url,
              error
            );
          }
        })
      );
    })
  );

  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys.map((key) => {
            /*
             * Apaga somente caches antigos do TimeClock.
             */
            if (
              key.startsWith("timeclock-") &&
              key !== CACHE_NAME
            ) {
              return caches.delete(key);
            }

            return Promise.resolve(false);
          })
        )
      )
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;

  /*
   * POST, PUT, DELETE e outros métodos
   * nunca devem passar pelo cache.
   */
  if (request.method !== "GET") {
    return;
  }

  const url = new URL(request.url);

  /*
   * O service worker só controla pedidos
   * feitos para o próprio domínio.
   */
  if (url.origin !== self.location.origin) {
    return;
  }

  const normalizedPath = url.pathname.replace(/\/+$/, "");

  const isNeverCachePath = NEVER_CACHE_PATHS.some(
    (path) => normalizedPath === path
  );

  const acceptsPdf =
    request.headers.get("accept")?.includes("application/pdf") === true;

  const isPdfFile =
    normalizedPath.toLowerCase().endsWith(".pdf");

  const isPdfRequest =
    isNeverCachePath ||
    acceptsPdf ||
    isPdfFile;

  /*
   * Navegações, relatórios e PDFs:
   * sempre diretamente da rede e sem cache.
   */
  if (request.mode === "navigate" || isPdfRequest) {
    event.respondWith(
      fetch(
        new Request(request, {
          cache: "no-store"
        })
      )
    );

    return;
  }

  /*
   * Apenas os ficheiros declarados em URLS_TO_CACHE
   * podem ser servidos pelo Cache Storage.
   */
  const isStaticAsset = URLS_TO_CACHE.includes(normalizedPath);

  if (!isStaticAsset) {
    return;
  }

  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }

      /*
       * Caso o ficheiro estático ainda não esteja no cache,
       * busca a versão atual na rede e guarda uma cópia.
       */
      return fetch(
        new Request(request, {
          cache: "reload"
        })
      ).then((networkResponse) => {
        if (
          !networkResponse ||
          networkResponse.status !== 200 ||
          networkResponse.type !== "basic"
        ) {
          return networkResponse;
        }

        const responseCopy = networkResponse.clone();

        caches
          .open(CACHE_NAME)
          .then((cache) => cache.put(request, responseCopy))
          .catch((error) => {
            console.warn(
              "[TimeClock SW] Falha ao atualizar ficheiro estático:",
              request.url,
              error
            );
          });

        return networkResponse;
      });
    })
  );
});