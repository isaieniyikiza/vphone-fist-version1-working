(() => {
    'use strict';

    const API = '/virtual-phone/user/api/google_workspace.php';
    const app = document.body.dataset.googleApp;
    const REQUIRED_SCOPE = {
        drive: 'https://www.googleapis.com/auth/drive',
        contacts: 'https://www.googleapis.com/auth/contacts',
        photos: 'https://www.googleapis.com/auth/photospicker.mediaitems.readonly'
    }[app];
    const $ = (selector, root = document) => root.querySelector(selector);
    const state = { connected: false, authorizedForApp: false, email: '', driveParent: '', driveStack: [], driveNext: null, driveFiles: [], driveSearchTimer: null, googleContacts: [], localContacts: [], pickerSession: null, pickerTimer: null, pickerPollMs: 5000 };
    const notice = $('#notice');

    function showNotice(message, type = '') {
        notice.textContent = message;
        notice.className = 'notice' + (type ? ' ' + type : '');
        notice.hidden = !message;
    }

    function setBusy(button, busy, text) {
        if (!button) return;
        button.disabled = busy;
        if (busy) {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner" aria-hidden="true"></span>' + (text || 'Please wait');
        } else if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
            delete button.dataset.originalText;
        }
    }

    async function request(action, options = {}) {
        const method = options.method || 'GET';
        const url = new URL(API, window.location.origin);
        url.searchParams.set('action', action);
        Object.entries(options.query || {}).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') url.searchParams.set(key, String(value));
        });
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: options.body instanceof FormData ? undefined : (options.body ? { 'Content-Type': 'application/json' } : undefined),
            body: options.body instanceof FormData ? options.body : (options.body ? JSON.stringify(options.body) : undefined)
        });
        const contentType = response.headers.get('content-type') || '';
        const payload = contentType.includes('application/json') ? await response.json() : null;
        if (!response.ok || !payload?.success) {
            throw new Error(payload?.message || 'The Google app could not complete that request.');
        }
        return payload;
    }

    function closeApp() {
        if (window.parent !== window) {
            window.parent.postMessage({ type: 'vphone:close-app' }, window.location.origin);
        } else {
            window.location.assign('/virtual-phone/user/user_phone_dashboard.html');
        }
    }

    function connect() {
        const popup = window.open(API + '?action=connect&app=' + encodeURIComponent(app), 'vphoneGoogleAuth', 'popup,width=520,height=720');
        if (!popup) {
            showNotice('Allow pop-ups to connect Google, then try again.', 'error');
            return;
        }
        showNotice('Google authorization opened in a secure pop-up. Follow the prompts there.');
        let attempts = 0;
        const poll = window.setInterval(async () => {
            attempts++;
            if (popup.closed || attempts > 90) {
                window.clearInterval(poll);
                try {
                    const status = await refreshConnection();
                    if (!state.authorizedForApp) {
                        showNotice('Google did not grant this app the requested access. In Google Cloud Console, add this email under Google Auth Platform → Audience → Test users, then reconnect and approve access.', 'error');
                    }
                } catch (error) {
                    showNotice(error.message, 'error');
                }
                return;
            }
            try {
                const status = await request('status', { query: { app } });
                if (status.connected && status.scopes.includes(REQUIRED_SCOPE)) {
                    window.clearInterval(poll);
                    popup.close();
                    state.connected = true;
                    state.authorizedForApp = true;
                    state.email = status.email;
                    renderConnection();
                    showNotice('Google account connected successfully.', 'success');
                    loadApp();
                }
            } catch (error) {
                window.clearInterval(poll);
                showNotice(error.message, 'error');
            }
        }, 2000);
    }

    async function disconnect() {
        if (!window.confirm('Disconnect this Google account from your Virtual Phone?')) return;
        const button = $('#accountButton');
        setBusy(button, true, 'Disconnecting');
        try {
            const result = await request('disconnect', { method: 'POST', body: {} });
            state.connected = false;
            state.authorizedForApp = false;
            state.email = '';
            state.pickerSession = null;
            if (state.pickerTimer) window.clearInterval(state.pickerTimer);
            renderConnection();
            showNotice(result.message, result.revocationConfirmed ? 'success' : 'error');
        } catch (error) {
            showNotice(error.message, 'error');
        } finally {
            setBusy(button, false);
        }
    }

    async function refreshConnection() {
        const result = await request('status', { query: { app } });
        state.connected = Boolean(result.connected);
        state.authorizedForApp = state.connected && result.scopes.includes(REQUIRED_SCOPE);
        state.email = result.email || '';
        renderConnection();
        return result;
    }

    function renderConnection() {
        $('#accountTitle').textContent = state.connected ? 'Connected account' : 'Connect Google';
        $('#accountEmail').textContent = state.connected ? state.email : 'Private to your Virtual Phone account';
        $('#accountButton').textContent = state.authorizedForApp ? 'Disconnect' : state.connected ? 'Allow access' : 'Connect';
        $('#accountButton').classList.toggle('button-light', state.authorizedForApp);
        $('#accountButton').classList.toggle('button-primary', !state.authorizedForApp);
        $('#appContent').hidden = !state.authorizedForApp;
        $('#connectPrompt').hidden = state.authorizedForApp;
        if (!state.authorizedForApp && state.pickerTimer) {
            window.clearInterval(state.pickerTimer);
            state.pickerTimer = null;
        }
    }

    function formatBytes(value) {
        const bytes = Number(value || 0);
        if (!bytes) return 'Google file';
        const units = ['B', 'KB', 'MB', 'GB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return (bytes / (1024 ** index)).toFixed(index ? 1 : 0) + ' ' + units[index];
    }

    function escapeText(value) {
        return String(value || '');
    }

    async function loadDrive(reset = true) {
        const container = $('#driveFiles');
        const query = $('#driveSearch').value.trim();
        if (reset) {
            state.driveFiles = [];
            state.driveNext = null;
            container.innerHTML = '<div class="empty-inline"><span class="spinner"></span> Loading your Drive…</div>';
        }
        $('#driveBack').hidden = !state.driveStack.length;
        try {
            const result = await request('drive_list', { query: { parent: state.driveParent, search: query, pageToken: reset ? '' : state.driveNext } });
            state.driveFiles = reset ? result.files : state.driveFiles.concat(result.files);
            state.driveNext = result.nextPageToken || null;
            renderDrive();
        } catch (error) {
            container.innerHTML = '';
            showNotice(error.message, 'error');
        }
    }

    function renderDrive() {
        const container = $('#driveFiles');
        const files = state.driveFiles;
        $('#folderTitle').textContent = state.driveParent ? 'Drive folder' : 'My Drive';
        $('#fileCount').textContent = files.length ? files.length + ' file(s) in this view' : 'No files found here';
        $('#driveMore').hidden = !state.driveNext;
        container.replaceChildren();
        if (!files.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-inline';
            empty.textContent = 'No files here yet. Upload a file or choose another folder.';
            container.append(empty);
            return;
        }
        files.forEach(file => {
            const folder = file.mimeType === 'application/vnd.google-apps.folder';
            const row = document.createElement('article');
            row.className = 'file-row';
            const icon = document.createElement('div');
            icon.className = 'file-icon' + (folder ? ' folder' : '');
            icon.innerHTML = '<i class="fa-solid ' + (folder ? 'fa-folder' : 'fa-file-lines') + '" aria-hidden="true"></i>';
            const info = document.createElement('div');
            info.className = 'file-info';
            const name = document.createElement('strong');
            name.textContent = file.name || 'Unnamed file';
            name.title = file.name || '';
            const meta = document.createElement('span');
            meta.textContent = folder ? 'Folder' : formatBytes(file.size);
            info.append(name, meta);
            const actions = document.createElement('div');
            actions.className = 'row-actions';
            if (folder) {
                const open = document.createElement('button');
                open.type = 'button';
                open.title = 'Open folder';
                open.setAttribute('aria-label', 'Open ' + (file.name || 'folder'));
                open.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                open.addEventListener('click', () => {
                    state.driveStack.push(state.driveParent);
                    state.driveParent = file.id;
                    loadDrive();
                });
                actions.append(open);
            } else {
                const download = document.createElement('button');
                download.type = 'button';
                download.title = 'Download file';
                download.setAttribute('aria-label', 'Download ' + (file.name || 'file'));
                download.innerHTML = '<i class="fa-solid fa-download"></i>';
                download.addEventListener('click', () => downloadDriveFile(file));
                actions.append(download);
                if (file.canEdit) {
                    const replaceLabel = document.createElement('label');
                    replaceLabel.className = 'replace-button';
                    replaceLabel.title = 'Replace file contents';
                    replaceLabel.setAttribute('aria-label', 'Replace ' + (file.name || 'file'));
                    replaceLabel.innerHTML = '<i class="fa-solid fa-rotate"></i>';
                    const replaceInput = document.createElement('input');
                    replaceInput.type = 'file';
                    replaceInput.hidden = true;
                    replaceInput.addEventListener('change', () => {
                        if (replaceInput.files?.[0]) updateDriveFile(file, replaceInput.files[0]);
                    });
                    replaceLabel.append(replaceInput);
                    actions.append(replaceLabel);
                }
                if (file.canDelete) {
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.title = 'Delete file uploaded by this app';
                    remove.setAttribute('aria-label', 'Delete ' + (file.name || 'file'));
                    remove.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
                    remove.addEventListener('click', () => deleteDriveFile(file));
                    actions.append(remove);
                }
            }
            row.append(icon, info, actions);
            container.append(row);
        });
    }

    async function updateDriveFile(file, replacement) {
        const formData = new FormData();
        formData.append('id', file.id);
        formData.append('file', replacement);
        showNotice('Updating ' + (file.name || 'Drive file') + '…');
        try {
            const result = await request('drive_update', { method: 'POST', body: formData });
            showNotice(result.message, 'success');
            loadDrive();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    }

    async function downloadDriveFile(file) {
        try {
            const response = await fetch(API + '?action=drive_download&id=' + encodeURIComponent(file.id), { credentials: 'same-origin' });
            if (!response.ok) {
                const error = await response.json().catch(() => null);
                throw new Error(error?.message || 'Google Drive could not download this file.');
            }
            const blob = await response.blob();
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = file.name || 'google-drive-file';
            link.click();
            window.setTimeout(() => URL.revokeObjectURL(link.href), 1000);
        } catch (error) {
            showNotice(error.message, 'error');
        }
    }

    async function deleteDriveFile(file) {
        if (!window.confirm('Move "' + file.name + '" to Google Drive trash?')) return;
        try {
            const result = await request('drive_delete', { method: 'POST', body: { id: file.id } });
            showNotice(result.message, 'success');
            loadDrive();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    }

    async function uploadDriveFile(file) {
        if (!file) return;
        const uploadInput = $('#driveUpload');
        const formData = new FormData();
        formData.append('file', file);
        showNotice('Uploading ' + file.name + ' to Google Drive…');
        try {
            const result = await request('drive_upload', { method: 'POST', body: formData });
            showNotice(result.message, 'success');
            loadDrive();
        } catch (error) {
            showNotice(error.message, 'error');
        } finally {
            uploadInput.value = '';
        }
    }

    function initials(name) {
        return String(name || '?').trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0)).join('').toUpperCase() || '?';
    }

    async function loadGoogleContacts() {
        const list = $('#contactList');
        list.innerHTML = '<div class="empty-inline"><span class="spinner"></span> Loading Google contacts…</div>';
        try {
            const result = await request('contacts_list');
            state.googleContacts = result.contacts || [];
            renderGoogleContacts();
            $('#contactCount').textContent = state.googleContacts.length + ' Google contact(s)';
            $('#selectAllContacts').hidden = !state.googleContacts.length;
        } catch (error) {
            list.replaceChildren();
            showNotice(error.message, 'error');
        }
    }

    function renderGoogleContacts() {
        const filter = ($('#contactSearch').value || '').trim().toLowerCase();
        const list = $('#contactList');
        list.replaceChildren();
        const contacts = state.googleContacts.filter(contact => (contact.name + ' ' + contact.number + ' ' + contact.email).toLowerCase().includes(filter));
        if (!contacts.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-inline';
            empty.textContent = state.googleContacts.length ? 'No contacts match your search.' : 'No Google contacts found.';
            list.append(empty);
            return;
        }
        contacts.forEach(contact => {
            const row = document.createElement('label');
            row.className = 'contact-row';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = contact.resourceName;
            checkbox.className = 'google-contact-check';
            const avatar = document.createElement('span');
            avatar.className = 'contact-avatar';
            if (contact.photo) {
                const image = document.createElement('img');
                image.src = contact.photo;
                image.alt = '';
                image.referrerPolicy = 'no-referrer';
                image.onerror = () => { avatar.textContent = initials(contact.name); };
                avatar.append(image);
            } else {
                avatar.textContent = initials(contact.name);
            }
            const details = document.createElement('span');
            details.className = 'contact-details';
            const name = document.createElement('strong');
            name.textContent = contact.name || 'Unnamed contact';
            const sub = document.createElement('span');
            sub.textContent = contact.number || contact.email || 'No phone or email';
            details.append(name, sub);
            row.append(checkbox, avatar, details);
            list.append(row);
        });
    }

    async function importContacts() {
        const resources = Array.from(document.querySelectorAll('.google-contact-check:checked'), input => input.value);
        if (!resources.length) {
            showNotice('Select at least one Google contact to import.', 'error');
            return;
        }
        const button = $('#importContacts');
        setBusy(button, true, 'Importing');
        try {
            const result = await request('contacts_import', { method: 'POST', body: { resources } });
            showNotice(result.message, 'success');
        } catch (error) {
            showNotice(error.message, 'error');
        } finally {
            setBusy(button, false);
        }
    }

    async function loadLocalContacts() {
        try {
            const result = await request('local_contacts');
            state.localContacts = result.contacts || [];
            state.selectedLocal = true;
            renderLocalContacts();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    }

    function renderLocalContacts() {
        const list = $('#localContactList');
        list.hidden = false;
        $('#exportContacts').hidden = !state.localContacts.length;
        $('#localContactCount').textContent = state.localContacts.length
            ? state.localContacts.length + ' contact(s) on this phone'
            : 'No saved phone contacts to sync yet.';
        list.replaceChildren();
        state.localContacts.forEach(contact => {
            const row = document.createElement('label');
            row.className = 'contact-row';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = String(contact.id);
            checkbox.className = 'local-contact-check';
            const avatar = document.createElement('span');
            avatar.className = 'contact-avatar';
            avatar.textContent = initials(contact.name);
            const details = document.createElement('span');
            details.className = 'contact-details';
            const name = document.createElement('strong');
            name.textContent = contact.name || 'Unnamed contact';
            const number = document.createElement('span');
            number.textContent = contact.number || 'No phone number';
            details.append(name, number);
            row.append(checkbox, avatar, details);
            list.append(row);
        });
    }

    async function exportContacts() {
        const contactIds = Array.from(document.querySelectorAll('.local-contact-check:checked'), input => Number(input.value));
        if (!contactIds.length) {
            showNotice('Select at least one phone contact to sync.', 'error');
            return;
        }
        if (contactIds.length > 50) {
            showNotice('Select no more than 50 phone contacts at a time.', 'error');
            return;
        }
        const button = $('#exportContacts');
        setBusy(button, true, 'Syncing');
        try {
            const result = await request('contacts_export', { method: 'POST', body: { contact_ids: contactIds } });
            showNotice(result.message, 'success');
        } catch (error) {
            showNotice(error.message, 'error');
        } finally {
            setBusy(button, false);
        }
    }

    async function startPhotoPicker() {
        const button = $('#pickerStart');
        setBusy(button, true, 'Opening Google Photos');
        try {
            const result = await request('photo_picker_create', { method: 'POST', body: {} });
            state.pickerSession = result.session;
            const pollInterval = String(result.session.pollingConfig?.pollInterval || '');
            const pollMatch = pollInterval.match(/^(\d+(?:\.\d+)?)s$/);
            state.pickerPollMs = pollMatch ? Math.max(1000, Number(pollMatch[1]) * 1000) : 5000;
            const popup = window.open(result.session.pickerUri, 'vphoneGooglePhotosPicker', 'popup,width=900,height=720');
            if (!popup) throw new Error('Allow pop-ups to open the Google Photos picker, then try again.');
            $('#pickerStatus').textContent = 'Choose photos in the Google Photos window. This app will show your selection when you return.';
            if (state.pickerTimer) window.clearInterval(state.pickerTimer);
            state.pickerTimer = window.setInterval(checkPhotoPicker, state.pickerPollMs);
        } catch (error) {
            showNotice(error.message, 'error');
        } finally {
            setBusy(button, false);
        }
    }

    async function checkPhotoPicker() {
        try {
            const result = await request('photo_picker_status');
            state.pickerSession = result.session;
            if (result.session.mediaItemsSet) {
                window.clearInterval(state.pickerTimer);
                state.pickerTimer = null;
                renderPhotos(result.session.mediaItems || []);
            } else {
                $('#pickerStatus').textContent = 'Waiting for your Google Photos selection. Finish choosing photos in the Google Photos window.';
            }
        } catch (error) {
            if (error.message.includes('Start a Google Photos selection')) {
                if (state.pickerTimer) window.clearInterval(state.pickerTimer);
                state.pickerTimer = null;
                $('#pickerStatus').textContent = 'Choose photos to begin.';
                return;
            }
            showNotice(error.message, 'error');
        }
    }

    function renderPhotos(items) {
        const grid = $('#photoGrid');
        grid.replaceChildren();
        $('#pickerStatus').textContent = items.length + ' selected photo(s). Import any photo to your File Manager.';
        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-inline';
            empty.textContent = 'No photos were selected. Try choosing photos again.';
            grid.append(empty);
            return;
        }
        items.forEach(item => {
            const media = item.mediaFile || {};
            const card = document.createElement('article');
            card.className = 'photo-card';
            const image = document.createElement('img');
            image.loading = 'lazy';
            image.alt = media.filename || 'Selected Google Photos image';
            image.src = API + '?action=photo_thumbnail&id=' + encodeURIComponent(item.id);
            const footer = document.createElement('div');
            footer.className = 'photo-card-footer';
            const name = document.createElement('span');
            name.textContent = media.filename || 'Selected photo';
            const button = document.createElement('button');
            button.className = 'button button-primary';
            button.type = 'button';
            button.textContent = 'Save';
            button.addEventListener('click', async () => {
                setBusy(button, true, 'Saving');
                try {
                    const result = await request('photo_import', { method: 'POST', body: { media_id: item.id } });
                    showNotice(result.message, 'success');
                    button.textContent = 'Saved';
                    button.disabled = true;
                } catch (error) {
                    showNotice(error.message, 'error');
                } finally {
                    if (!button.disabled) setBusy(button, false);
                }
            });
            footer.append(name, button);
            card.append(image, footer);
            grid.append(card);
        });
    }

    function loadApp() {
        if (app === 'drive') {
            loadDrive();
        } else if (app === 'contacts') {
            loadGoogleContacts();
        }
    }

    $('#accountButton').addEventListener('click', () => state.authorizedForApp ? disconnect() : connect());
    document.querySelectorAll('.connect-cta').forEach(button => button.addEventListener('click', connect));
    $('.close-app').addEventListener('click', closeApp);

    if (app === 'drive') {
        $('#driveBack').addEventListener('click', () => {
            state.driveParent = state.driveStack.pop() || '';
            loadDrive();
        });
        $('#driveMore').addEventListener('click', () => loadDrive(false));
        $('#driveSearch').addEventListener('input', () => {
            window.clearTimeout(state.driveSearchTimer);
            state.driveSearchTimer = window.setTimeout(() => loadDrive(), 350);
        });
        $('#driveUpload').addEventListener('change', event => uploadDriveFile(event.target.files?.[0]));
    }
    if (app === 'contacts') {
        $('#refreshContacts').addEventListener('click', loadGoogleContacts);
        $('#contactSearch').addEventListener('input', renderGoogleContacts);
        $('#importContacts').addEventListener('click', importContacts);
        $('#selectAllContacts').addEventListener('click', () => {
            const checks = Array.from(document.querySelectorAll('.google-contact-check'));
            const shouldCheck = checks.some(input => !input.checked);
            checks.forEach(input => { input.checked = shouldCheck; });
            $('#selectAllContacts').textContent = shouldCheck ? 'Clear selection' : 'Select all';
        });
        $('#loadLocalContacts').addEventListener('click', loadLocalContacts);
        $('#exportContacts').addEventListener('click', exportContacts);
    }
    if (app === 'photos') {
        $('#pickerStart').addEventListener('click', startPhotoPicker);
    }

    refreshConnection().then(result => {
        if (state.authorizedForApp) loadApp();
    }).catch(error => showNotice(error.message, 'error'));

    window.addEventListener('message', event => {
        if (event.origin === window.location.origin && event.data?.type === 'vphone-google-connected') {
            refreshConnection().then(result => {
                if (result.connected) {
                    showNotice('Google account connected successfully.', 'success');
                    loadApp();
                }
            }).catch(error => showNotice(error.message, 'error'));
        } else if (event.origin === window.location.origin && event.data?.type === 'vphone-google-error') {
            showNotice(String(event.data.message || 'Google authorization could not be completed.'), 'error');
        }
    });
})();
