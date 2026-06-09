import './bootstrap';

function updateRedDot(unreadCount) {
    if (typeof window.updateNotificationBadge === 'function') {
        window.updateNotificationBadge(unreadCount);
        return;
    }

    const bell = document.getElementById('navBell');
    if (!bell) return;

    let badge = document.getElementById('navBellBadge');
    if (unreadCount > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'nav-bell-badge';
            badge.id = 'navBellBadge';
            bell.appendChild(badge);
        }
        badge.textContent = unreadCount;
    } else if (badge) {
        badge.remove();
    }
}

function updatePoolDom(poolData) {
    if (!poolData || !poolData.match_id) return;

    document.querySelectorAll(`[data-pool-match-id="${poolData.match_id}"]`).forEach((root) => {
        const barA = root.querySelector('[data-pool-bar-a]');
        const barB = root.querySelector('[data-pool-bar-b]');
        const total = root.querySelector('[data-pool-total]');
        const split = root.querySelector('[data-pool-split]');
        const bettors = root.querySelector('[data-pool-bettors]');
        const state = root.querySelector('[data-pool-state]');

        if (barA) barA.style.width = `${poolData.percent_a}%`;
        if (barB) barB.style.width = `${poolData.percent_b}%`;
        if (total) total.textContent = new Intl.NumberFormat('en-US').format(poolData.total_pool || 0) + ' pts';
        if (split) split.textContent = `${poolData.percent_a}% / ${poolData.percent_b}%`;
        if (bettors) bettors.textContent = `${poolData.bettor_count || 0} bettors`;
        if (state) {
            state.textContent = String(poolData.market_state || 'open').replace('_', ' ');
            state.className = `app-status app-status--${poolData.market_state || 'open'}`;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) {
        return;
    }

    const userId = window.currentUserId;
    if (userId) {
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('UserNotificationUpdated', (event) => {
                updateRedDot(event.unreadCount || 0);

                if (typeof window.fetchNotifications === 'function') {
                    window.fetchNotifications();
                }
            });
    }

    const matchIds = Array.from(document.querySelectorAll('[data-pool-match-id]'))
        .map((el) => el.getAttribute('data-pool-match-id'))
        .filter(Boolean);

    Array.from(new Set(matchIds)).forEach((matchId) => {
        window.Echo.channel(`match.${matchId}`)
            .listen('PoolUpdated', (event) => {
                updatePoolDom(event.poolData);
            });
    });
});
