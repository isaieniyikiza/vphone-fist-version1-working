(function () {
    'use strict';

    const THEMES = {
        ocean: '/virtual-phone/theme/theme.jpg',
        forest: '/virtual-phone/theme/theme1.jpg',
        midnight: '/virtual-phone/theme/theme2.jpg',
        sunrise: '/virtual-phone/theme/theme4.jpg',
        aurora: '/virtual-phone/theme/theme2.jpg'
    };
    const BUILT_IN_WALLPAPERS = Object.values(THEMES);
    const CUSTOM_WALLPAPER_PATTERN = /^data:image\/(?:jpeg|png|webp|gif);base64,[A-Za-z0-9+/=]+$/;
    const FILE_WALLPAPER_PATTERN = /^\/virtual-phone\/user\/storage\/api\/download\.php\?id=[A-Za-z0-9%+/_=-]+$/;
    const LAYOUTS = ['standard', 'compact', 'comfortable'];
    const DEFAULTS = { theme: 'ocean', wallpaper: '', layout: 'standard' };
    const LOCK_ITERATIONS = 120000;

    function userKey() {
        try {
            const user = JSON.parse(localStorage.getItem('vphone_user') || 'null');
            const identity = user && (user.id || user.username);
            return identity ? encodeURIComponent(String(identity)) : 'guest';
        } catch (error) {
            console.error('Virtual Phone settings: could not read the current user.', error);
            return 'guest';
        }
    }

    function storageKey() {
        return 'vphone_preferences_' + userKey();
    }

    function liveWallpaperStorageKey() {
        return 'vphone_live_wallpaper_' + userKey();
    }

    function liveWallpaperIntervalStorageKey() {
        return 'vphone_live_wallpaper_interval_' + userKey();
    }

    function isLiveWallpaperEnabled() {
        try {
            return localStorage.getItem(liveWallpaperStorageKey()) === 'true';
        } catch (error) {
            console.error('Could not read the live wallpaper preference.', error);
            return false;
        }
    }

    function setLiveWallpaperEnabled(enabled) {
        try {
            localStorage.setItem(liveWallpaperStorageKey(), enabled ? 'true' : 'false');
            window.dispatchEvent(new CustomEvent('vphone:live-wallpaper-changed', {
                detail: { enabled: Boolean(enabled), interval: getLiveWallpaperInterval() }
            }));
        } catch (error) {
            console.error('Could not save the live wallpaper preference.', error);
            throw new Error('The live wallpaper setting could not be saved in this browser.');
        }
    }

    function getLiveWallpaperInterval() {
        try {
            const interval = Number(localStorage.getItem(liveWallpaperIntervalStorageKey()));
            return [30, 60, 120, 300].includes(interval) ? interval : 60;
        } catch (error) {
            console.error('Could not read the live wallpaper interval.', error);
            return 60;
        }
    }

    function setLiveWallpaperInterval(value) {
        const interval = Number(value);
        if (![30, 60, 120, 300].includes(interval)) {
            throw new Error('Choose a wallpaper interval from the available options.');
        }
        try {
            localStorage.setItem(liveWallpaperIntervalStorageKey(), String(interval));
            window.dispatchEvent(new CustomEvent('vphone:live-wallpaper-changed', {
                detail: { enabled: isLiveWallpaperEnabled(), interval }
            }));
        } catch (error) {
            console.error('Could not save the live wallpaper interval.', error);
            throw new Error('The wallpaper interval could not be saved in this browser.');
        }
    }

    function lockStorageKey() {
        return 'vphone_app_locks_' + userKey();
    }

    function appKey(value) {
        const url = value instanceof URL ? value : new URL(value, window.location.href);
        const runtimeId = url.pathname === '/virtual-phone/user/apps/runtime.php'
            ? new URLSearchParams(url.search).get('id')
            : null;
        return url.pathname + (runtimeId ? '?id=' + encodeURIComponent(runtimeId) : '');
    }

    function getLocks() {
        try {
            const locks = JSON.parse(localStorage.getItem(lockStorageKey()) || '{}');
            return locks && typeof locks === 'object' && !Array.isArray(locks) ? locks : {};
        } catch (error) {
            console.error('Virtual Phone app locks could not be loaded.', error);
            return {};
        }
    }

    function saveLocks(locks) {
        try {
            localStorage.setItem(lockStorageKey(), JSON.stringify(locks));
        } catch (error) {
            console.error('Virtual Phone app locks could not be saved.', error);
            throw new Error('App lock settings could not be saved in this browser.');
        }
    }

    function unlockedKey(key) {
        return 'vphone_unlocked_' + userKey() + '_' + encodeURIComponent(key);
    }

    function isUnlocked(key) {
        try {
            const allowed = sessionStorage.getItem(unlockedKey(key)) === 'true';
            if (allowed) sessionStorage.removeItem(unlockedKey(key));
            return allowed;
        } catch (error) {
            console.error('Could not read the app unlock session.', error);
            return false;
        }
    }

    function setUnlocked(key) {
        try {
            sessionStorage.setItem(unlockedKey(key), 'true');
        } catch (error) {
            console.error('Could not save the app unlock session.', error);
            throw new Error('This browser could not remember the unlocked app for this session.');
        }
    }

    async function hashCredential(credential, salt) {
        if (!window.crypto?.subtle) {
            throw new Error('Secure app locks require a modern browser on localhost or HTTPS.');
        }
        const material = await crypto.subtle.importKey(
            'raw',
            new TextEncoder().encode(credential),
            'PBKDF2',
            false,
            ['deriveBits']
        );
        const bits = await crypto.subtle.deriveBits({
            name: 'PBKDF2',
            salt,
            iterations: LOCK_ITERATIONS,
            hash: 'SHA-256'
        }, material, 256);
        return Array.from(new Uint8Array(bits), byte => byte.toString(16).padStart(2, '0')).join('');
    }

    async function makeLock(method, credential) {
        if (method !== 'pin' && method !== 'pattern') throw new Error('Choose a PIN or pattern lock.');
        if (method === 'pin' && !/^\d{4,12}$/.test(credential)) {
            throw new Error('Your PIN must contain 4 to 12 digits.');
        }
        if (method === 'pattern' && (!/^[0-8]{4,9}$/.test(credential) || new Set(credential).size < 4)) {
            throw new Error('Your pattern must connect at least 4 different dots.');
        }
        const salt = crypto.getRandomValues(new Uint8Array(16));
        const hash = await hashCredential(credential, salt);
        return {
            method,
            salt: Array.from(salt, byte => byte.toString(16).padStart(2, '0')).join(''),
            hash,
            iterations: LOCK_ITERATIONS,
            updatedAt: new Date().toISOString()
        };
    }

    async function verifyCredential(lock, credential) {
        if (!lock || !['pin', 'pattern'].includes(lock.method) ||
            typeof lock.salt !== 'string' || typeof lock.hash !== 'string') return false;
        const salt = new Uint8Array(lock.salt.match(/.{2}/g)?.map(byte => parseInt(byte, 16)) || []);
        if (salt.length !== 16) return false;
        const hash = await hashCredential(credential, salt);
        if (hash.length !== lock.hash.length) return false;
        let difference = 0;
        for (let index = 0; index < hash.length; index++) {
            difference |= hash.charCodeAt(index) ^ lock.hash.charCodeAt(index);
        }
        return difference === 0;
    }

    function makePatternPad(onChange) {
        const pad = document.createElement('div');
        pad.className = 'vphone-pattern-pad';
        pad.setAttribute('role', 'group');
        pad.setAttribute('aria-label', 'Pattern dots. Tap at least four different dots in order.');
        const selected = [];
        let drawing = false;
        const selectDot = (dot, index) => {
            if (selected.includes(String(index))) return;
            selected.push(String(index));
            dot.classList.add('selected');
            dot.setAttribute('aria-pressed', 'true');
            onChange(selected.join(''));
        };
        for (let index = 0; index < 9; index++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'vphone-pattern-dot';
            dot.setAttribute('aria-label', 'Pattern dot ' + (index + 1));
            dot.setAttribute('aria-pressed', 'false');
            dot.addEventListener('pointerdown', event => {
                event.preventDefault();
                drawing = true;
                selectDot(dot, index);
            });
            dot.addEventListener('pointerenter', () => {
                if (drawing) selectDot(dot, index);
            });
            dot.addEventListener('click', () => selectDot(dot, index));
            pad.appendChild(dot);
        }
        pad.addEventListener('pointerup', () => { drawing = false; });
        pad.addEventListener('pointercancel', () => { drawing = false; });
        const reset = document.createElement('button');
        reset.type = 'button';
        reset.className = 'vphone-pattern-reset';
        reset.textContent = 'Clear pattern';
        reset.addEventListener('click', () => {
            selected.length = 0;
            pad.querySelectorAll('.vphone-pattern-dot').forEach(dot => {
                dot.classList.remove('selected');
                dot.setAttribute('aria-pressed', 'false');
            });
            onChange('');
        });
        const wrapper = document.createElement('div');
        wrapper.append(pad, reset);
        return wrapper;
    }

    function showCredentialDialog(title, lock, onResult) {
        const screen = document.querySelector('.screen');
        const host = screen || document.body;
        const overlay = document.createElement('div');
        overlay.className = 'vphone-lock-overlay';
        if (host !== document.body) {
            overlay.style.position = 'absolute';
            overlay.style.inset = '0';
            overlay.style.zIndex = '1600';
        }
        const dialog = document.createElement('section');
        dialog.className = 'vphone-lock-dialog';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        const heading = document.createElement('h2');
        heading.textContent = title;
        const description = document.createElement('p');
        description.textContent = lock.method === 'pin'
            ? 'Enter your app PIN to continue.'
            : 'Tap the pattern you saved to continue.';
        const error = document.createElement('p');
        error.className = 'vphone-lock-error';
        error.setAttribute('role', 'status');
        const form = document.createElement('form');
        let credential = '';
        let input;
        if (lock.method === 'pin') {
            input = document.createElement('input');
            input.type = 'password';
            input.inputMode = 'numeric';
            input.autocomplete = 'current-password';
            input.pattern = '[0-9]{4,12}';
            input.maxLength = 12;
            input.placeholder = '4–12 digit PIN';
            input.required = true;
            input.addEventListener('input', () => { credential = input.value; });
            form.appendChild(input);
        } else {
            form.appendChild(makePatternPad(value => { credential = value; }));
        }
        const submit = document.createElement('button');
        submit.type = 'submit';
        submit.className = 'vphone-lock-submit';
        submit.textContent = 'Unlock app';
        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'vphone-lock-cancel';
        cancel.textContent = 'Back to phone';
        cancel.addEventListener('click', () => {
            overlay.remove();
            onResult(false);
        });
        form.appendChild(submit);
        dialog.append(heading, description, form, cancel, error);
        overlay.appendChild(dialog);
        host.appendChild(overlay);
        if (input) input.focus();
        form.addEventListener('submit', async event => {
            event.preventDefault();
            submit.disabled = true;
            error.textContent = '';
            try {
                const valid = await verifyCredential(lock, credential);
                if (valid) {
                    overlay.remove();
                    onResult(true);
                } else {
                    error.textContent = 'That PIN or pattern did not match. Try again.';
                    credential = '';
                    if (input) {
                        input.value = '';
                        input.focus();
                    } else {
                        const newPad = makePatternPad(value => { credential = value; });
                        form.querySelector('.vphone-pattern-pad').parentElement.replaceWith(newPad);
                    }
                    submit.disabled = false;
                }
            } catch (failure) {
                error.textContent = failure.message || 'Could not verify this app lock.';
                submit.disabled = false;
            }
        });
        return overlay;
    }

    async function requestUnlock(value, launchApp) {
        const key = appKey(value);
        const lock = getLocks()[key];
        if (!lock) return true;
        return new Promise(resolve => {
            showCredentialDialog('Unlock ' + (lock.appName || 'app'), lock, success => {
                if (success) {
                    if (launchApp !== false) {
                        try {
                            setUnlocked(key);
                        } catch (error) {
                            console.error(error);
                            resolve(false);
                            return;
                        }
                    }
                }
                resolve(success);
            });
        });
    }

    function protectCurrentApp() {
        const path = window.location.pathname;
        if (!path.startsWith('/virtual-phone/user/') ||
            path === '/virtual-phone/user/user_phone_dashboard.html' ||
            path.includes('/api/')) return;
        const key = appKey(window.location.href);
        const lock = getLocks()[key];
        if (lock && !isUnlocked(key)) {
            requestUnlock(window.location.href, false).then(unlocked => {
                if (!unlocked) {
                    if (window.parent !== window) {
                        window.parent.postMessage({ type: 'vphone:close-app' }, window.location.origin);
                    } else {
                        window.location.replace('/virtual-phone/user/user_phone_dashboard.html');
                    }
                }
            });
        }
    }

    function getPreferences() {
        try {
            const saved = JSON.parse(localStorage.getItem(storageKey()) || '{}');
            return {
                theme: Object.prototype.hasOwnProperty.call(THEMES, saved.theme) ? saved.theme : DEFAULTS.theme,
                wallpaper: typeof saved.wallpaper === 'string' &&
                    (saved.wallpaper === '' || BUILT_IN_WALLPAPERS.includes(saved.wallpaper) ||
                        CUSTOM_WALLPAPER_PATTERN.test(saved.wallpaper) || FILE_WALLPAPER_PATTERN.test(saved.wallpaper))
                    ? saved.wallpaper
                    : '',
                layout: LAYOUTS.includes(saved.layout) ? saved.layout : DEFAULTS.layout
            };
        } catch (error) {
            console.error('Virtual Phone settings could not be loaded.', error);
            return { ...DEFAULTS };
        }
    }

    function applyPreferences() {
        const preferences = getPreferences();
        const root = document.documentElement;
        const isDashboard = Boolean(document.querySelector('.phone') && document.querySelector('.apps-grid'));
        root.dataset.vphoneTheme = preferences.theme;
        root.dataset.vphoneLayout = preferences.layout;
        root.classList.toggle('vphone-dashboard', isDashboard);
        if (preferences.wallpaper) {
            root.dataset.vphoneHasWallpaper = 'true';
        } else {
            delete root.dataset.vphoneHasWallpaper;
        }

        if (preferences.wallpaper) {
            root.style.setProperty('--vphone-wallpaper', `url("${preferences.wallpaper}")`);
        } else {
            root.style.removeProperty('--vphone-wallpaper');
        }

        const wallpaper = document.getElementById('wallpaper');
        if (wallpaper) {
            wallpaper.style.backgroundImage = preferences.wallpaper ? `url("${preferences.wallpaper}")` : '';
        }

        document.dispatchEvent(new CustomEvent('vphone:preferences-changed', { detail: preferences }));
        return preferences;
    }

    function savePreferences(changes) {
        const current = getPreferences();
        const next = {
            theme: Object.prototype.hasOwnProperty.call(THEMES, changes.theme) ? changes.theme : current.theme,
            wallpaper: typeof changes.wallpaper === 'string' ? changes.wallpaper : current.wallpaper,
            layout: LAYOUTS.includes(changes.layout) ? changes.layout : current.layout
        };
        try {
            localStorage.setItem(storageKey(), JSON.stringify(next));
        } catch (error) {
            console.error('Virtual Phone settings could not be saved.', error);
            throw new Error('Settings could not be saved in this browser. Check available storage.');
        }
        applyPreferences();
        return next;
    }

    window.VPhoneSettings = {
        themes: THEMES,
        layouts: LAYOUTS,
        getPreferences,
        getWallpaperUrl: () => getPreferences().wallpaper,
        savePreferences,
        isLiveWallpaperEnabled,
        setLiveWallpaperEnabled,
        getLiveWallpaperInterval,
        setLiveWallpaperInterval,
        userKey,
        apply: applyPreferences
    };

    window.VPhoneAppLock = {
        appKey,
        getLocks,
        hasLock: value => Boolean(getLocks()[appKey(value)]),
        getLock: value => getLocks()[appKey(value)] || null,
        setLock: (value, lock) => {
            const locks = getLocks();
            locks[appKey(value)] = lock;
            saveLocks(locks);
        },
        removeLock: value => {
            const locks = getLocks();
            delete locks[appKey(value)];
            saveLocks(locks);
        },
        makeLock,
        makePatternPad,
        verifyCredential,
        requestUnlock
    };

    applyPreferences();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', protectCurrentApp, { once: true });
    } else {
        protectCurrentApp();
    }
    window.addEventListener('storage', event => {
        if (event.key === storageKey()) applyPreferences();
        if (event.key === 'vphone_user') applyPreferences();
    });
})();
