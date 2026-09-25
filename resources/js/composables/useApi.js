import axios from 'axios';

// Get CSRF token and base URL from document meta
const getMeta = (name) => {
    const el = document.querySelector(`meta[name="${name}"]`);
    return el ? el.getAttribute('content') : '';
};

const csrfToken = getMeta('csrf-token') || getMeta('token');
const baseUrl = getMeta('base-url') || '';

const api = axios.create({
    baseURL: baseUrl,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
    },
});

export function useApi() {
    return {
        api,
        baseUrl,
        csrfToken,
        get: (url, config) => api.get(url, config),
        post: (url, data, config) => api.post(url, data, config),
        put: (url, data, config) => api.put(url, data, config),
        delete: (url, config) => api.delete(url, config),
    };
}
