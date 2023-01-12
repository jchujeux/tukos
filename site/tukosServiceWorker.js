importScripts('https://npmcdn.com/dexie/dist/dexie');
const addResourcesToCache = async (resources) => {
  const cache = await caches.open('v1');
  await cache.addAll(resources);
};

const putInCache = async (request, response) => {
  const cache = await caches.open('v1');
  await cache.put(request, response);
};

const cacheFirst = async ({ request, preloadResponsePromise, fallbackUrl }) => {
  // First try to get the resource from the cache
  const responseFromCache = await caches.match(request);
  if (responseFromCache) {
    return responseFromCache;
  }

  // Next try to use the preloaded response, if it's there
  const preloadResponse = await preloadResponsePromise;
  if (preloadResponse) {
    console.info('using preload response', preloadResponse);
    putInCache(request, preloadResponse.clone());
    return preloadResponse;
  }

  // Next try to get the resource from the network
  try {
    const responseFromNetwork = await fetch(request);
    // response may be used only once
    // we need to save clone to put one copy in cache
    // and serve second one
    putInCache(request, responseFromNetwork.clone());
    return responseFromNetwork;
  } catch (error) {
    const fallbackResponse = await caches.match(fallbackUrl);
    if (fallbackResponse) {
      return fallbackResponse;
    }
    // when even the fallback response is not available,
    // there is nothing we can do, but we must always
    // return a Response object
    return new Response('Network error happened', {
      status: 408,
      headers: { 'Content-Type': 'text/plain' },
    });
  }
};

const enableNavigationPreload = async () => {
  if (self.registration.navigationPreload) {
    // Enable navigation preloads!
    await self.registration.navigationPreload.enable();
  }
};

self.addEventListener('activate', (event) => {
  event.waitUntil(enableNavigationPreload());
});

self.addEventListener('install', (event) => {
  event.waitUntil(
    addResourcesToCache([
    ])
  );
});

self.addEventListener('fetch', (event) => {
  console.log('it is a fetch');
  if (event.request.method === "POST"){
	  console.log('it is a POST');
	  const db = new Dexie('tukos_Post-Cache');
	  db.version(1).stores({posts: 'key, response, timestamp'});
	  const data = event.request.clone().json().then(data => {
		  console.log("data: " + data);
		  return data;
	  });
	  event.respondWith(fetch(event.request.clone()).then(function(response){
			  return cachePut(event.request.clone(), response.clone(), db.posts);
		  }).catch(function(){
			  return cacheMatch(event.request.clone(), db.posts);
		  })
	  );
  }else{
	  event.respondWith(
	    cacheFirst({
	      request: event.request,
	      preloadResponsePromise: event.preloadResponse,
	      fallbackUrl: '/images/tukosswissknife.jpg',
	    })
  	);
 }
});
function serializeRequest(request) {
	  var serialized = {
		url: request.url,
		headers: serializeHeaders(request.headers),
		method: request.method,
		mode: request.mode,
		credentials: request.credentials,
		cache: request.cache,
		redirect: request.redirect,
		referrer: request.referrer
	  };
	
	  // Only if method is not `GET` or `HEAD` is the request allowed to have body.
	  if (request.method !== 'GET' && request.method !== 'HEAD') {
		return request.clone().text().then(function(body) {
		  serialized.body = body;
		  return Promise.resolve(serialized);
		});
	  }
	  return Promise.resolve(serialized);
}
 
/**
 * Serializes a Response into a plain JS object
 * 
 * @param response
 * @returns Promise
 */ 
function serializeResponse(response, removeData) {
	  var serialized = {
		headers: serializeHeaders(response.headers),
		status: response.status,
		statusText: response.statusText
	  };
	
	  return response.clone().json().then(function(bodyObject) {
		  if (removeData){
			  delete bodyObject.formContent.data;
		  }
		  serialized.body = JSON.stringify(bodyObject);
		  return Promise.resolve(serialized);
	  });
}
 
/**
 * Serializes headers into a plain JS object
 * 
 * @param headers
 * @returns object
 */ 
function serializeHeaders(headers) {
	var serialized = {};
	// `for(... of ...)` is ES6 notation but current browsers supporting SW, support this
	// notation as well and this is the only way of retrieving all the headers.
	for (var entry of headers.entries()) {
		serialized[entry[0]] = entry[1];
	}
	return serialized;
}
 
/**
 * Creates a Response from it's serialized version
 * 
 * @param data
 * @returns Promise
 */ 
function deserializeResponse(data) {
	return Promise.resolve(new Response(data.body, data));
}
 
/**
 * Saves the response for the given request eventually overriding the previous version
 * 
 * @param data
 * @returns Promise
 */
function cachePut(request, response, store) {
	const reqInfo = requestInfo(request);
	return response.clone().json().then(bodyObject => {
		console.log('body: ' + bodyObject);
		let entry;
		switch(reqInfo.action){
			case 'Edit/Tab/Tab': // create or update entry "{object}edit/tab" with the response from which the data part is deleted, &  if "{id}/changes" is present, we add it to the response as changedData 
				entry = {key: reqInfo.id + reqInfo.object + '/initial', response: {data: bodyObject.formContent.data, extendedIds: bodyObject.formContent.extendedIds, extras: bodyObject.formContent.extras}, timestamp: Date.now()};
				store.add(entry).catch(error => {store.update(entry.key, entry)});
	        	return serializeResponse(response.clone(), true).then(serializedResponse => {
					const entry = {key: reqInfo.object + '/Edit/Tab/Tab', response: serializedResponse, timestamp: Date.now()};
					store.add(entry).catch(error => {store.update(entry.key, entry)});
					return store.get(reqInfo.id + '/changes').then(changedData => {
						if (changedData){
							const bodyObject = JSON.parse(serializedResponse.body);
							bodyObject.formContent.changedData = changedData;
							serializedResponse.body = JSON.stringify(bodyObject);
							return deserializeResponse(serializedResponse);
						}else{
							return response.clone();
						}
					});
				});
	        case 'Edit/Tab/Save':
	        case 'Edit/Tab/Reset':
			case 'Edit/Tab/Edit' :
				entry = {key: reqInfo.id + reqInfo.object + '/initial', response: {data: bodyObject.data, extendedIds: bodyObject.extendedIds, extrax: bodyObject.extras}, timestamp: Date.now()};
				store.add(entry).catch(error => {store.update(entry.key, entry)});
				store.delete(reqInfo.id + '/changes');
				return response.clone();
			default:
				let key;
				getPostId(request.clone()).then(function(id){
					key = id;
					return serializeResponse(response.clone());}).then(function(serializedResponse) {
						var entry = {key: key, response: serializedResponse, timestamp: Date.now()};
						store.add(entry).catch(function(error){	store.update(entry.key, entry);});
					});
					return response.clone();
		}
	})
}
	
/**
 * Returns the cached response for the given request or an empty 503-response  for a cache miss.
 * 
 * @param request
 * @return Promise
 */
function cacheMatch(request, store) {
	const reqInfo = requestInfo(request);
	switch(reqInfo.action){
		case 'Edit/Tab/Tab':
			return store.get(reqInfo.object + '/Edit/Tab/Tab').then(tabData => {
				if (tabData){
					return store.get(reqInfo.id + reqInfo.object + '/initial').then(initialData => {
						const response = tabData.response, tabBodyObject = JSON.parse(response.body), initialBodyObject = initialData.response;
						tabBodyObject.formContent.data = initialBodyObject.data;
						tabBodyObject.extendedIds = initialBodyObject.extendedIds;
						tabBodyObject.extras = initialBodyObject.extras;
						return store.get(reqInfo.id + '/changes').then(changedData => {
							if (changedData){
								tabBodyObject.changedValues = changedData.body;
							}
							response.body = JSON.stringify(tabBodyObject);
							return deserializeResponse(response);
						});
					});
				}else{
					return new Response('', {status: 503, statusText: 'Service Unavailable'});
				}
			});
        case 'Edit/Tab/Save':
        case 'Edit/Tab/Reset':
		case 'Edit/Tab/Edit' :
			return new Response('', {status: 503, statusText: 'Service Unavailable'});
		default:
			return getPostId(request.clone()).then(function(id) {return store.get(id);}).then(function(data){
				if (data) {
					return deserializeResponse(data.response);
				} else {
					return new Response('', {status: 503, statusText: 'Service Unavailable'});
				}
			});
	}
}
function getPostId(request) {
	return serializeRequest(request.clone()).then(function(id){
		return JSON.stringify(id);
	});
}
// "https://localhost/tukos/index20.php/TukosApp/Dialogue/notes/Edit/Tab/Tab?id=48261"
function requestInfo(request){
	const req = request.clone(), urlObject = new URL(req.url), match = urlObject.pathname.match(/\/dialogue\/([^\/]+)\/(.*)/i);
	return {object: match[1], action: match[2], id: urlObject.searchParams.get('id') || 'new'};
}