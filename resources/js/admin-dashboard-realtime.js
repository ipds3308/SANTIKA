import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const queueTable = document.getElementById('admin-queue-table');
const reviewsFragment = document.getElementById('admin-reviews-fragment');
const waitingRoom = document.getElementById('card-panggilan');
const newsTicker = document.getElementById('newsTicker');
const appKey = import.meta.env.VITE_REVERB_APP_KEY;

if (queueTable || reviewsFragment || waitingRoom || newsTicker) {
    const scheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '');
    const host = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
    const port = Number(import.meta.env.VITE_REVERB_PORT || (scheme === 'https' ? 443 : 80));
    let queueRefreshInProgress = false;
    let queueRefreshPending = false;

    async function replaceFragment(selector, requestHeader, requestLabel) {
        try {
            const response = await fetch(window.location.href, {
                headers: {
                    Accept: 'text/html',
                    [requestHeader]: '1',
                },
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(`${requestLabel} refresh failed with status ${response.status}`);
            }

            const html = await response.text();
            const documentFragment = new DOMParser().parseFromString(html, 'text/html');
            const updatedFragment = documentFragment.querySelector(selector);
            const currentFragment = document.querySelector(selector);

            if (!updatedFragment || !currentFragment) {
                throw new Error(`${requestLabel} refresh response did not contain the expected fragment`);
            }

            currentFragment.replaceWith(updatedFragment);

            if (selector === '#admin-queue-table') {
                window.initDropdownsFixedStrategy?.(updatedFragment.querySelector('#tabel-pendaftar-body'));
            }
        } catch (error) {
            console.error(`Gagal memperbarui ${requestLabel}:`, error);
        }
    }

    if (appKey) {
        const echo = new Echo({
            broadcaster: 'reverb',
            key: appKey,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        const adminChannel = echo.private('admin.dashboard');

        if (queueTable) {
            const hasOpenDropdown = () => document.querySelector('#tabel-pendaftar-body .dropdown-menu.show') !== null;

            async function refreshQueueTable() {
                if (hasOpenDropdown() || queueRefreshInProgress) {
                    queueRefreshPending = true;
                    return;
                }

                queueRefreshInProgress = true;
                await replaceFragment('#admin-queue-table', 'X-Queue-Fragment', 'data antrean');
                queueRefreshInProgress = false;

                if (queueRefreshPending && !hasOpenDropdown()) {
                    queueRefreshPending = false;
                    window.setTimeout(refreshQueueTable, 0);
                }
            }

            adminChannel.listen('.queue.updated', refreshQueueTable);

            document.addEventListener('hidden.bs.dropdown', () => {
                if (queueRefreshPending && !hasOpenDropdown()) {
                    queueRefreshPending = false;
                    void refreshQueueTable();
                }
            });

            window.setInterval(() => {
                if (document.visibilityState === 'visible') {
                    void refreshQueueTable();
                }
            }, 60_000);
        }

        if (reviewsFragment) {
            adminChannel.listen('.reviews.updated', () => {
                void replaceFragment('#admin-reviews-fragment', 'X-Reviews-Fragment', 'data ulasan');
            });
        }

        if (waitingRoom) {
            const waitingRoomChannel = echo.channel('waiting-room');
            let hasConnected = false;

            waitingRoomChannel.listen('.queue.updated', () => {
                window.ambilDataRuangTunggu?.();
            });

            echo.connector.pusher.connection.bind('connected', () => {
                if (hasConnected) {
                    window.ambilDataRuangTunggu?.();
                }

                hasConnected = true;
            });
        }

        if (newsTicker) {
            let refreshTimer;
            const newsChannel = echo.channel('news');

            newsChannel.listen('.news.updated', () => {
                window.clearTimeout(refreshTimer);
                refreshTimer = window.setTimeout(() => {
                    window.refreshNewsTicker?.();
                }, 150);
            });

            echo.connector.pusher.connection.bind('connected', () => {
                window.refreshNewsTicker?.();
            });
        }
    } else {
        console.error('Reverb tidak dikonfigurasi: VITE_REVERB_APP_KEY tidak tersedia.');
    }
}
