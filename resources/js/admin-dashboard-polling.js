const POLL_INTERVAL_MS = 15_000;

const queueTable = document.getElementById('admin-queue-table');
const reviewsFragment = document.getElementById('admin-reviews-fragment');
const waitingRoom = document.getElementById('card-panggilan');
const newsTicker = document.getElementById('newsTicker');

function pollWhileVisible(callback) {
    let requestInProgress = false;

    async function poll() {
        if (document.visibilityState !== 'visible' || requestInProgress) {
            return;
        }

        requestInProgress = true;

        try {
            await callback();
        } catch (error) {
            console.error('Gagal memperbarui data otomatis:', error);
        } finally {
            requestInProgress = false;
        }
    }

    window.setInterval(poll, POLL_INTERVAL_MS);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            void poll();
        }
    });
}

async function replaceFragment(selector, requestHeader, requestLabel) {
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
}

if (queueTable) {
    let queueRefreshPending = false;

    async function refreshQueueTable() {
        const hasOpenDropdown = document.querySelector('#tabel-pendaftar-body .dropdown-menu.show') !== null;

        if (hasOpenDropdown) {
            queueRefreshPending = true;
            return;
        }

        queueRefreshPending = false;
        await replaceFragment('#admin-queue-table', 'X-Queue-Fragment', 'data antrean');
    }

    pollWhileVisible(refreshQueueTable);

    document.addEventListener('hidden.bs.dropdown', () => {
        if (queueRefreshPending) {
            void refreshQueueTable().catch((error) => {
                console.error('Gagal memperbarui data antrean:', error);
            });
        }
    });
}

if (reviewsFragment) {
    pollWhileVisible(() => replaceFragment('#admin-reviews-fragment', 'X-Reviews-Fragment', 'data ulasan'));
}

if (waitingRoom) {
    pollWhileVisible(() => window.ambilDataRuangTunggu?.());
}

if (newsTicker) {
    pollWhileVisible(() => window.refreshNewsTicker?.());
}
