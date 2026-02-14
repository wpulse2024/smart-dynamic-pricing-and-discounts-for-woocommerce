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

async function request(method, path, body = undefined) {
  const url = path.startsWith('http') ? path : `${baseUrl()}/${path.replace(/^\//, '')}`;
  const options = { method, headers: headers() };
  if (body !== undefined && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
    options.body = JSON.stringify(body);
  }
  const res = await fetch(url, options);
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data?.message || res.statusText || 'Request failed');
    err.status = res.status;
    err.data = data;
    throw err;
  }
  return data?.data !== undefined ? data.data : data;
}

export const api = {
  get: (path) => request('GET', path),
  post: (path, body) => request('POST', path, body),
  put: (path, body) => request('PUT', path, body),
  patch: (path, body) => request('PATCH', path, body),
  delete: (path) => request('DELETE', path),
};
