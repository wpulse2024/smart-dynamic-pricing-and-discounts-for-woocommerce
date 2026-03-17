/**
 * WP AJAX client for exclusion list (admin-ajax.php).
 * Uses wpulsePricingRules.ajaxUrl and wpulsePricingRules.exclusionNonce.
 */

const config = typeof window !== 'undefined' ? window.wpulsePricingRules || {} : {};
const ajaxUrl = config.ajaxUrl || '';
const nonce = config.exclusionNonce || '';

function formBody(data) {
  const params = new URLSearchParams();
  Object.entries(data).forEach(([k, v]) => {
    if (v == null || v === '') return;
    if (Array.isArray(v)) {
      v.forEach((item) => params.append(`${k}[]`, String(item)));
    } else {
      params.set(k, String(v));
    }
  });
  return params.toString();
}

async function post(action, data = {}) {
  const body = formBody({ action, nonce, ...data });
  const res = await fetch(ajaxUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body,
  });
  const json = await res.json().catch(() => ({}));
  if (!json.success) {
    const err = new Error(json?.data?.message || 'Request failed');
    err.data = json.data;
    throw err;
  }
  return json.data;
}

async function get(action, params = {}) {
  const qs = new URLSearchParams({ action, nonce, ...params });
  const res = await fetch(`${ajaxUrl}?${qs}`);
  const json = await res.json().catch(() => ({}));
  if (!json.success) {
    const err = new Error(json?.data?.message || 'Request failed');
    err.data = json.data;
    throw err;
  }
  return json.data;
}

export const exclusionApi = {
  getList: () => post('wpulse_get_exclusions'),
  add: (exclusionType, objectId) => post('wpulse_add_exclusion', { exclusion_type: exclusionType, object_id: objectId }),
  addMultiple: (exclusionType, objectIds) => post('wpulse_add_exclusion', { exclusion_type: exclusionType, object_ids: objectIds }),
  delete: (id) => post('wpulse_delete_exclusion', { id }),
  searchProducts: (search, perPage = 20) => get('wpulse_search_products', { search, per_page: perPage }),
  searchCategories: (search = '', perPage = 20) => get('wpulse_search_categories', { search, per_page: perPage }),
  searchTags: (search = '', perPage = 20) => get('wpulse_search_tags', { search, per_page: perPage }),
};
