import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel Reverb (WebSockets).
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        // Global real-time event distribution
        window.Echo.channel('portal-notifications')
            .listen('.po.status.updated', (e) => {
                console.log('[RealTime PO Update]', e);
                window.dispatchEvent(new CustomEvent('fts:po-updated', { detail: e }));
            })
            .listen('.attendance.checked.in', (e) => {
                console.log('[RealTime Attendance CheckIn]', e);
                window.dispatchEvent(new CustomEvent('fts:attendance-checkin', { detail: e }));
            })
            .listen('.payment.dispatched', (e) => {
                console.log('[RealTime Payment]', e);
                window.dispatchEvent(new CustomEvent('fts:payment-dispatched', { detail: e }));
            })
            .listen('.memo.acknowledged', (e) => {
                console.log('[RealTime Memo Acknowledgment]', e);
                window.dispatchEvent(new CustomEvent('fts:memo-acknowledged', { detail: e }));
            });
    } catch (err) {
        console.warn('RealTime Reverb client connection error:', err);
    }
}
