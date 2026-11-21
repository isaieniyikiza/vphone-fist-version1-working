<?php
declare(strict_types=1);

session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /virtual-phone/user/user_phone_dashboard.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Icon Rewards · Virtual Phone</title>
    <link rel="stylesheet" href="/virtual-phone/user/settings/theme.css">
    <script defer src="/virtual-phone/user/settings/theme.js"></script>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; padding: 18px; background: var(--vphone-page); color: var(--vphone-text); font: 14px/1.5 Inter, "Segoe UI", Arial, sans-serif; }
        button { font: inherit; }
        .rewards-app { width: min(760px, 100%); min-height: calc(100vh - 36px); margin: 0 auto; overflow: hidden; border: 1px solid var(--vphone-border); border-radius: 26px; background: var(--vphone-page); box-shadow: var(--vphone-shadow); }
        .topbar { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-bottom: 1px solid var(--vphone-border); background: var(--vphone-surface); }
        .close-button { display: grid; width: 38px; height: 38px; flex: none; place-items: center; border: 0; border-radius: 12px; background: var(--vphone-accent-soft); color: var(--vphone-accent-strong); cursor: pointer; }
        .brand { display: grid; width: 42px; height: 42px; flex: none; place-items: center; border-radius: 14px; background: linear-gradient(140deg, #f8bf56, #e87934); color: #fff; font-size: 19px; }
        .brand img { display: block; width: 100%; height: 100%; border-radius: inherit; }
        .topbar h1 { margin: 0; font-size: 17px; }
        .topbar p { margin: 2px 0 0; color: var(--vphone-muted); font-size: 11px; }
        main { padding: 18px; }
        .balance-card { display: flex; align-items: center; justify-content: space-between; gap: 15px; padding: 20px; border-radius: 20px; background: linear-gradient(125deg, #24285b, #5d4cb2 62%, #9873d5); color: #fff; box-shadow: 0 12px 30px #4e438b33; }
        .balance-card h2 { margin: 0; font-size: 13px; font-weight: 600; opacity: .84; }
        .balance-value { display: flex; align-items: center; gap: 9px; margin-top: 3px; font-size: clamp(30px, 8vw, 42px); font-weight: 800; letter-spacing: -1px; }
        .balance-value i { color: #ffd970; font-size: 24px; }
        .daily-bonus { max-width: 190px; padding: 10px 12px; border: 1px solid #ffffff40; border-radius: 13px; background: #ffffff12; font-size: 11px; line-height: 1.5; }
        .section-title { display: flex; align-items: end; justify-content: space-between; gap: 10px; margin: 22px 2px 10px; }
        .section-title h2 { margin: 0; font-size: 16px; }
        .section-title p { margin: 2px 0 0; color: var(--vphone-muted); font-size: 11px; }
        .activity-card, .product-card, .insights-card { border: 1px solid var(--vphone-border); border-radius: 17px; background: var(--vphone-surface); }
        .activity-card { padding: 15px; }
        .activity-top { display: flex; justify-content: space-between; gap: 12px; align-items: center; }
        .activity-title { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 750; }
        .activity-title i { color: var(--vphone-accent); font-size: 17px; }
        .activity-count { flex: none; color: var(--vphone-accent-strong); font-size: 12px; font-weight: 800; }
        .progress-track { height: 9px; margin: 13px 0 7px; overflow: hidden; border-radius: 20px; background: var(--vphone-accent-soft); }
        .progress-fill { width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #6888ef, #8b5bd3); transition: width .25s ease; }
        .activity-caption, .small-note { margin: 0; color: var(--vphone-muted); font-size: 10px; line-height: 1.55; }
        .notification-card { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); gap: 16px; padding: 15px; border: 1px solid var(--vphone-border); border-radius: 17px; background: var(--vphone-surface); }
        .notification-settings { display: grid; align-content: start; gap: 8px; }
        .notification-toggle { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 10px; border: 1px solid var(--vphone-border); border-radius: 11px; color: var(--vphone-text); font-size: 11px; font-weight: 650; cursor: pointer; }
        .notification-toggle input { width: 17px; height: 17px; margin: 0; accent-color: var(--vphone-accent); }
        .notification-history-header { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 5px; }
        .notification-history-header strong { font-size: 12px; }
        .clear-history { padding: 5px 8px; border: 1px solid var(--vphone-border); border-radius: 8px; background: transparent; color: var(--vphone-muted); font-size: 10px; cursor: pointer; }
        .notification-history { display: grid; max-height: 184px; gap: 7px; overflow-y: auto; margin: 0; padding: 0; list-style: none; }
        .notification-entry { padding: 9px 10px; border-radius: 10px; background: var(--vphone-accent-soft); }
        .notification-entry strong { display: block; font-size: 11px; }
        .notification-entry p { margin: 2px 0; color: var(--vphone-muted); font-size: 10px; }
        .notification-entry time { color: var(--vphone-muted); font-size: 9px; }
        .notification-empty { padding: 18px 8px; color: var(--vphone-muted); font-size: 10px; text-align: center; }
        .product-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 11px; }
        .product-card { display: flex; min-height: 196px; flex-direction: column; padding: 14px; }
        .product-icon { display: grid; width: 38px; height: 38px; place-items: center; border-radius: 12px; background: var(--vphone-accent-soft); color: var(--vphone-accent-strong); font-size: 16px; }
        .product-card h3 { margin: 12px 0 4px; font-size: 13px; line-height: 1.35; }
        .product-card p { flex: 1; margin: 0 0 11px; color: var(--vphone-muted); font-size: 10px; line-height: 1.5; }
        .buy-button { min-height: 36px; padding: 8px 10px; border: 0; border-radius: 10px; background: var(--vphone-accent); color: #fff; font-size: 10px; font-weight: 800; cursor: pointer; }
        .buy-button:hover { filter: brightness(1.05); }
        .buy-button:disabled { opacity: .58; cursor: wait; }
        .buy-button.owned { background: #e7f6ed; color: #177444; cursor: default; }
        .insights-card { padding: 15px; }
        .insights-lock { padding: 20px; color: var(--vphone-muted); text-align: center; }
        .insights-lock i { display: block; margin-bottom: 8px; color: var(--vphone-accent); font-size: 23px; }
        .history-list { display: grid; gap: 7px; margin: 12px 0 0; padding: 0; list-style: none; }
        .history-item { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-top: 1px solid var(--vphone-border); font-size: 11px; }
        .history-item span:first-child { overflow-wrap: anywhere; }
        .history-item .amount { flex: none; font-weight: 800; }
        .amount.positive { color: #16834e; }
        .amount.negative { color: #bd4654; }
        .notice { min-height: 20px; margin: 12px 2px 0; color: var(--vphone-accent-strong); font-size: 11px; }
        .notice.error { color: #b42335; }
        @media (max-width: 600px) {
            body { padding: 0; }
            .rewards-app { min-height: 100vh; border: 0; border-radius: 0; }
            main { padding: 14px; }
            .product-grid { grid-template-columns: 1fr; }
            .notification-card { grid-template-columns: 1fr; }
            .product-card { min-height: 0; }
            .balance-card { align-items: flex-start; }
            .daily-bonus { max-width: 150px; }
        }
    </style>
</head>
<body>
    <div class="rewards-app">
        <header class="topbar">
            <button class="close-button" id="closeRewards" type="button" aria-label="Close Icon Rewards"><i class="fa-solid fa-arrow-left"></i></button>
            <div class="brand"><img src="/virtual-phone/user/icon_rewards/icon.svg" alt="" aria-hidden="true"></div>
            <div><h1>Icon Rewards</h1><p>Earn Icons and unlock phone upgrades</p></div>
        </header>
        <main>
            <section class="balance-card" aria-label="Icon balance">
                <div>
                    <h2>Your Icon balance</h2>
                    <div class="balance-value"><i class="fa-solid fa-coins" aria-hidden="true"></i><span id="balance">Loading…</span></div>
                </div>
                <div class="daily-bonus"><strong>Daily sign-in gift</strong><br>Get 10 Icons once a day when you sign in.</div>
            </section>

            <div class="section-title">
                <div><h2>Earn Icons</h2><p>Keep this app open and visible to earn activity rewards.</p></div>
                <span class="activity-count" id="dailyActivity">0 / 30 today</span>
            </div>
            <section class="activity-card">
                <div class="activity-top">
                    <div class="activity-title"><i class="fa-solid fa-stopwatch" aria-hidden="true"></i><span>Active app time</span></div>
                    <span class="activity-count" id="timeToReward">4:00 to next reward</span>
                </div>
                <div class="progress-track" role="progressbar" aria-label="Progress to next activity reward" aria-valuemin="0" aria-valuemax="240" aria-valuenow="0">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p class="activity-caption">Earn 4 Icons for every 4 active minutes, up to 30 Icons per day. The last reward may be 2 Icons to stop at the daily limit. Background time does not count.</p>
            </section>

            <div class="section-title">
                <div><h2>Reward notifications</h2><p>Choose which reward events appear in your activity history.</p></div>
            </div>
            <section class="notification-card" aria-label="Reward notification settings and history">
                <div class="notification-settings">
                    <label class="notification-toggle" for="dailyNotificationToggle"><span>Daily sign-in bonus</span><input id="dailyNotificationToggle" type="checkbox"></label>
                    <label class="notification-toggle" for="activityNotificationToggle"><span>Active-time rewards</span><input id="activityNotificationToggle" type="checkbox"></label>
                    <label class="notification-toggle" for="purchaseNotificationToggle"><span>Purchases and upgrades</span><input id="purchaseNotificationToggle" type="checkbox"></label>
                    <p class="small-note">These settings apply to alerts and history on this device.</p>
                </div>
                <div>
                    <div class="notification-history-header">
                        <strong>Recent activity</strong>
                        <button class="clear-history" id="clearNotificationHistory" type="button">Clear history</button>
                    </div>
                    <ul class="notification-history" id="notificationHistory" aria-live="polite"></ul>
                </div>
            </section>

            <div class="section-title">
                <div><h2>Upgrade store</h2><p>Spend Icons on permanent upgrades for this account.</p></div>
            </div>
            <section class="product-grid" id="products" aria-live="polite">
                <div class="product-card"><p>Loading available upgrades…</p></div>
            </section>

            <div class="section-title">
                <div><h2>Premium Insights</h2><p>Review how you earn and spend Icons.</p></div>
            </div>
            <section class="insights-card" id="insights"></section>
            <p class="notice" id="notice" role="status" aria-live="polite"></p>
        </main>
    </div>
    <script>
        (() => {
            'use strict';
            const API = '/virtual-phone/user/api/icon_rewards.php';
            const productsRoot = document.getElementById('products');
            const notice = document.getElementById('notice');
            const notificationHistoryRoot = document.getElementById('notificationHistory');
            const notificationPreferences = { daily: true, activity: true, purchase: true };
            const notificationHistory = [];
            let activityTimer = null;
            let state = null;

            function notificationStorageKey() {
                let identity = 'guest';
                try {
                    const user = JSON.parse(localStorage.getItem('vphone_user') || 'null');
                    identity = user && (user.id || user.username) || identity;
                } catch (error) {
                    console.error('Icon Rewards could not identify the local account.', error);
                }
                return 'vphone_icon_rewards_notifications_' + encodeURIComponent(String(identity));
            }

            function showNotice(message, isError = false) {
                notice.textContent = message;
                notice.classList.toggle('error', isError);
            }

            function loadNotificationData() {
                try {
                    const saved = JSON.parse(localStorage.getItem(notificationStorageKey()) || '{}');
                    for (const category of Object.keys(notificationPreferences)) {
                        if (typeof saved.preferences?.[category] === 'boolean') {
                            notificationPreferences[category] = saved.preferences[category];
                        }
                    }
                    if (Array.isArray(saved.history)) {
                        notificationHistory.push(...saved.history.filter(item =>
                            item && typeof item.category === 'string' &&
                            typeof item.title === 'string' && typeof item.message === 'string' &&
                            typeof item.created_at === 'string'
                        ).slice(0, 30));
                    }
                } catch (error) {
                    console.error('Icon Rewards notification settings could not be loaded.', error);
                    showNotice('Notification settings could not be loaded from this device.', true);
                }
                for (const [category, inputId] of Object.entries({
                    daily: 'dailyNotificationToggle',
                    activity: 'activityNotificationToggle',
                    purchase: 'purchaseNotificationToggle'
                })) {
                    document.getElementById(inputId).checked = notificationPreferences[category];
                }
                renderNotificationHistory();
            }

            function saveNotificationData() {
                try {
                    localStorage.setItem(notificationStorageKey(), JSON.stringify({
                        preferences: notificationPreferences,
                        history: notificationHistory
                    }));
                    return true;
                } catch (error) {
                    console.error('Icon Rewards notification settings could not be saved.', error);
                    showNotice('Notification settings could not be saved on this device.', true);
                    return false;
                }
            }

            function renderNotificationHistory() {
                notificationHistoryRoot.replaceChildren();
                if (!notificationHistory.length) {
                    const empty = document.createElement('li');
                    empty.className = 'notification-empty';
                    empty.textContent = 'Your reward alerts will appear here.';
                    notificationHistoryRoot.appendChild(empty);
                    return;
                }
                for (const item of notificationHistory) {
                    const entry = document.createElement('li');
                    entry.className = 'notification-entry';
                    const title = document.createElement('strong');
                    title.textContent = item.title;
                    const message = document.createElement('p');
                    message.textContent = item.message;
                    const time = document.createElement('time');
                    time.dateTime = item.created_at;
                    time.textContent = new Date(item.created_at).toLocaleString();
                    entry.append(title, message, time);
                    notificationHistoryRoot.appendChild(entry);
                }
            }

            function recordRewardNotification(category, title, message, showWhenDisabled = false) {
                if (!notificationPreferences[category]) {
                    if (showWhenDisabled) showNotice(message);
                    return;
                }
                notificationHistory.unshift({
                    category,
                    title,
                    message,
                    created_at: new Date().toISOString()
                });
                notificationHistory.splice(30);
                const saved = saveNotificationData();
                renderNotificationHistory();
                showNotice(saved ? message : message + ' The alert history could not be saved on this device.', !saved);
            }

            async function request(url, options = {}) {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    cache: 'no-store',
                    ...options
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Icon Rewards could not complete that request.');
                }
                return result;
            }

            function setActivity(activity) {
                const seconds = Number(activity.progress_seconds) || 0;
                const earned = Number(activity.earned_today) || 0;
                const percent = Math.min(100, (seconds / Number(activity.reward_seconds || 240)) * 100);
                document.getElementById('progressFill').style.width = percent + '%';
                document.querySelector('[role="progressbar"]').setAttribute('aria-valuenow', String(seconds));
                document.getElementById('timeToReward').textContent =
                    Math.floor(seconds / 60) + ':' + String(seconds % 60).padStart(2, '0') + ' / 4:00';
                document.getElementById('dailyActivity').textContent = earned + ' / ' + activity.daily_limit + ' today';
            }

            function renderInsights(data) {
                const panel = document.getElementById('insights');
                if (!data.upgrades.premium_insights) {
                    panel.innerHTML = '<div class="insights-lock"><i class="fa-solid fa-lock" aria-hidden="true"></i><strong>Premium Insights are locked</strong><p class="small-note">Unlock this feature in the store to view your recent Icon earnings and purchases.</p></div>';
                    return;
                }
                const list = document.createElement('ul');
                list.className = 'history-list';
                for (const item of data.history || []) {
                    const row = document.createElement('li');
                    row.className = 'history-item';
                    const description = document.createElement('span');
                    description.textContent = item.description + ' · ' + new Date(item.created_at).toLocaleDateString();
                    const amount = document.createElement('span');
                    amount.className = 'amount ' + (Number(item.amount) >= 0 ? 'positive' : 'negative');
                    amount.textContent = (Number(item.amount) > 0 ? '+' : '') + item.amount + ' Icons';
                    row.append(description, amount);
                    list.appendChild(row);
                }
                if (!list.children.length) {
                    const empty = document.createElement('p');
                    empty.className = 'small-note';
                    empty.textContent = 'Your Icon activity will appear here.';
                    panel.replaceChildren(empty);
                } else {
                    panel.replaceChildren(list);
                }
            }

            function productIcon(productId) {
                if (productId === 'storage_1gb') return 'fa-cloud-arrow-up';
                if (productId === 'theme_pack') return 'fa-palette';
                return 'fa-chart-line';
            }

            function renderProducts(data) {
                productsRoot.replaceChildren();
                for (const product of data.products) {
                    const card = document.createElement('article');
                    card.className = 'product-card';
                    const icon = document.createElement('span');
                    icon.className = 'product-icon';
                    icon.innerHTML = '<i class="fa-solid ' + productIcon(product.id) + '" aria-hidden="true"></i>';
                    const title = document.createElement('h3');
                    title.textContent = product.name;
                    const description = document.createElement('p');
                    description.textContent = product.description;
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'buy-button';
                    const isOwned = product.owned === true;
                    const isAtLimit = product.id === 'storage_1gb' && product.quantity >= product.limit;
                    button.disabled = isOwned || isAtLimit;
                    if (button.disabled) button.classList.add('owned');
                    button.textContent = isOwned ? 'Unlocked' : isAtLimit ? 'Storage limit reached' : 'Buy for ' + product.price + ' Icons';
                    button.addEventListener('click', () => purchase(product));
                    card.append(icon, title, description, button);
                    productsRoot.appendChild(card);
                }
            }

            function render(data) {
                state = data;
                document.getElementById('balance').textContent = Number(data.balance).toLocaleString();
                setActivity(data.activity);
                renderProducts(data);
                renderInsights(data);
            }

            async function loadState() {
                try {
                    render(await request(API + '?action=state'));
                } catch (error) {
                    showNotice(error.message, true);
                }
            }

            async function claimDailyBonus() {
                try {
                    const result = await request(API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'login_bonus' })
                    });
                    if (result.awarded > 0) {
                        recordRewardNotification('daily', 'Daily sign-in bonus', '+' + result.awarded + ' Icons added to your balance.');
                    }
                } catch (error) {
                    showNotice(error.message, true);
                }
            }

            async function activityPing() {
                if (document.visibilityState !== 'visible') return;
                try {
                    const result = await request(API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'activity_ping' })
                    });
                    if (state) {
                        state.balance = result.balance;
                        state.activity = result;
                        render(state);
                    }
                    if (result.awarded > 0) {
                        recordRewardNotification('activity', 'Active-time reward', '+' + result.awarded + ' Icons added to your balance.');
                    }
                } catch (error) {
                    showNotice(error.message, true);
                }
            }

            async function purchase(product) {
                if (!window.confirm('Spend ' + product.price + ' Icons to unlock ' + product.name + '?')) return;
                const button = Array.from(productsRoot.querySelectorAll('button')).find(item =>
                    item.textContent.includes(String(product.price))
                );
                if (button) button.disabled = true;
                try {
                    const result = await request(API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'purchase', product_id: product.id })
                    });
                    recordRewardNotification('purchase', 'Upgrade purchased', result.message, true);
                    await loadState();
                } catch (error) {
                    showNotice(error.message, true);
                    await loadState();
                }
            }

            document.getElementById('closeRewards').addEventListener('click', () => {
                if (window.parent !== window) {
                    window.parent.postMessage({ type: 'vphone:close-app' }, window.location.origin);
                } else {
                    window.location.href = '/virtual-phone/user/user_phone_dashboard.html';
                }
            });
            for (const [category, inputId] of Object.entries({
                daily: 'dailyNotificationToggle',
                activity: 'activityNotificationToggle',
                purchase: 'purchaseNotificationToggle'
            })) {
                document.getElementById(inputId).addEventListener('change', event => {
                    notificationPreferences[category] = event.currentTarget.checked;
                    if (saveNotificationData()) {
                        showNotice(event.currentTarget.checked ? 'Reward alerts enabled.' : 'Reward alerts disabled.');
                    }
                });
            }
            document.getElementById('clearNotificationHistory').addEventListener('click', () => {
                notificationHistory.length = 0;
                const saved = saveNotificationData();
                renderNotificationHistory();
                if (saved) showNotice('Reward alert history cleared.');
            });
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') activityPing();
            });
            window.addEventListener('pagehide', () => {
                if (activityTimer) window.clearInterval(activityTimer);
            });

            loadNotificationData();
            (async () => {
                await claimDailyBonus();
                await loadState();
                await activityPing();
            })();
            activityTimer = window.setInterval(activityPing, 15000);
        })();
    </script>
</body>
</html>
