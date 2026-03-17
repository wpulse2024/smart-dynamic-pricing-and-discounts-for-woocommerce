/**
 * REST API client for WPulse Pricing Rules (uses wpulsePricingRules.restUrl + nonce).
 */
const config = window.wpulsePricingRules || {};

function baseUrl() {
  return (config.restUrl || '').replace(/\/$/, '');
}

function headers() {
  const h = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  };
  if (config.nonce) {
    h['X-WP-Nonce'] = config.nonce;
  }
  return h;
}

async function request(method, path, bodyOrParams = undefined) {
  let url = path.startsWith('http') ? path : `${baseUrl()}/${path.replace(/^\//, '')}`;
  const options = { method, headers: headers() };
  if (method === 'GET' && bodyOrParams != null && typeof bodyOrParams === 'object' && !Array.isArray(bodyOrParams)) {
    const params = new URLSearchParams();
    Object.entries(bodyOrParams).forEach(([k, v]) => { if (v != null && v !== '') params.set(k, String(v)); });
    const qs = params.toString();
    if (qs) url += (url.includes('?') ? '&' : '?') + qs;
  } else if (bodyOrParams !== undefined && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
    options.body = JSON.stringify(bodyOrParams);
  }
  const res = await fetch(url, options);
  const data = await res.json().catch((e) => { console.error('[wpulse] JSON parse error', e); return {}; });
  if (!res.ok) {
    const err = new Error(data?.message || res.statusText || 'Request failed');
    err.status = res.status;
    err.data = data;
    throw err;
  }
  return data?.data !== undefined ? data.data : data;
}

export const api = {
  get: (path, params) => request('GET', path, params),
  post: (path, body) => request('POST', path, body),
  put: (path, body) => request('PUT', path, body),
  patch: (path, body) => request('PATCH', path, body),
  delete: (path) => request('DELETE', path),
};
