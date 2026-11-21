<?php
declare(strict_types=1);
header('Location: /virtual-phone/admin/', true, 302);
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Phone Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root{font-family:Inter,Segoe UI,Arial,sans-serif;color:#182033;background:#f3f5fb;font-synthesis:none}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:radial-gradient(circle at 85% 10%,#dce6ff 0,transparent 30%),#f3f5fb}
        button,input,textarea{font:inherit}button{cursor:pointer}.shell{max-width:1120px;margin:0 auto;padding:32px 22px 60px}
        .top{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:24px}.brand{display:flex;align-items:center;gap:12px}
        .brand-icon{width:46px;height:46px;border-radius:15px;display:grid;place-items:center;color:#fff;background:linear-gradient(135deg,#5865f2,#9a5df5);font-size:20px}
        h1{font-size:24px;margin:0}.subtitle{color:#718096;font-size:13px;margin:5px 0 0}.top a{color:#5262df;text-decoration:none;font-weight:700}
        .panel{background:#fff;border:1px solid #e6eaf2;border-radius:20px;box-shadow:0 12px 35px #273c6710;padding:24px;margin:18px 0}
        .login{max-width:460px;margin:8vh auto}.login h2{margin:0 0 7px}.login p{color:#718096;line-height:1.55}.field{display:grid;gap:7px;margin:16px 0}
        label{font-size:13px;font-weight:700;color:#344054}input,textarea{width:100%;padding:12px 13px;border:1px solid #d6dbe7;border-radius:11px;background:#fff;color:#182033;outline:0}
        input:focus,textarea:focus{border-color:#6978f4;box-shadow:0 0 0 3px #6978f426}textarea{min-height:85px;resize:vertical}
        .btn{border:0;border-radius:11px;padding:12px 16px;font-weight:750;color:#fff;background:#5865f2}.btn:hover{background:#4855df}.btn:disabled{opacity:.55;cursor:wait}
        .btn.secondary{background:#eef1ff;color:#4554d6}.btn.danger{background:#fff0ed;color:#c54631}.btn.small{padding:8px 11px;font-size:12px}
        .message{padding:11px 13px;margin:14px 0;border-radius:10px;font-size:13px;line-height:1.45}.message.error{background:#fff0ed;color:#a93222}.message.success{background:#e8f8ef;color:#187342}
        .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}.stat{background:#f7f8ff;border-radius:15px;padding:17px}.stat strong{font-size:25px;display:block}.stat span{font-size:12px;color:#718096}
        .section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.section-head h2{font-size:18px;margin:0}
        .upload-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}.wide{grid-column:1/-1}.hint{font-size:12px;line-height:1.5;color:#758096}
        .file-box{padding:14px;border:1px dashed #bbc4d7;border-radius:13px;background:#fafbff}.file-box input{border:0;padding:6px 0}
        .app-list{display:grid;gap:10px}.app-row{display:flex;align-items:center;gap:13px;padding:13px;border:1px solid #edf0f5;border-radius:14px}
        .app-icon{height:48px;width:48px;flex:none;border-radius:14px;object-fit:cover;background:#edf0ff}.app-copy{flex:1;min-width:0}.app-copy strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.app-copy small{color:#758096}
        .empty{padding:28px 14px;text-align:center;color:#758096;border:1px dashed #dce1ec;border-radius:14px}
        @media(max-width:700px){.shell{padding:20px 14px 40px}.stats{grid-template-columns:1fr}.upload-grid{grid-template-columns:1fr}.wide{grid-column:auto}.app-row{align-items:flex-start;flex-wrap:wrap}.app-copy{min-width:calc(100% - 70px)}}
    </style>
</head>
<body>
<main class="shell">
    <header class="top">
        <div class="brand"><div class="brand-icon"><i class="fa-solid fa-mobile-screen-button"></i></div><div><h1>Virtual Phone Admin</h1><p class="subtitle">Manage apps in your users’ Play Store</p></div></div>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <a href="/virtual-phone/admin/"><i class="fa-solid fa-chart-line"></i> Full dashboard</a>
            <a href="/virtual-phone/user/user_phone_dashboard.html"><i class="fa-solid fa-arrow-left"></i> Phone</a>
        </div>
    </header>

    <section class="panel login" id="authPanel" hidden>
        <h2 id="authTitle">Admin sign in</h2>
        <p id="authDescription">Sign in to manage the Virtual Phone app catalog.</p>
        <form id="authForm">
            <div class="field"><label for="authUsername">Admin username</label><input id="authUsername" name="username" minlength="3" maxlength="80" autocomplete="username" required></div>
            <div class="field"><label for="authPassword">Password</label><input id="authPassword" name="password" type="password" minlength="10" maxlength="200" autocomplete="current-password" required></div>
            <div id="authMessage" aria-live="polite"></div>
            <button class="btn" type="submit" id="authSubmit">Sign in</button>
        </form>
    </section>

    <div id="adminDashboard" hidden>
        <div class="panel">
            <div class="section-head"><div><h2>App catalog overview</h2><p class="subtitle">Add an app folder once; users can install it from their Play Store.</p></div><button class="btn secondary" id="signOutBtn" type="button"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out</button></div>
            <div class="stats">
                <div class="stat"><strong id="totalApps">0</strong><span>Apps in catalog</span></div>
                <div class="stat"><strong id="publishedApps">0</strong><span>Published apps</span></div>
                <div class="stat"><strong id="totalInstalls">0</strong><span>Total installs</span></div>
            </div>
        </div>

        <section class="panel">
            <div class="section-head"><div><h2><i class="fa-solid fa-cloud-arrow-up"></i> Add an app</h2><p class="subtitle">Choose a project folder with an index.php, index.html, or index.htm in its root.</p></div></div>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="upload-grid">
                    <div class="field"><label for="appName">App name</label><input id="appName" name="name" maxlength="100" placeholder="Weather" required></div>
                    <div class="field"><label for="folderName">Project folder name</label><input id="folderName" name="folder_name" maxlength="80" placeholder="weather-app" required></div>
                    <div class="field wide"><label for="appDescription">Description</label><textarea id="appDescription" name="description" maxlength="500" placeholder="What can users do with this app?"></textarea></div>
                    <div class="field"><label for="appIcon">App icon</label><div class="file-box"><input id="appIcon" name="icon" type="file" accept="image/png,image/jpeg,image/gif,image/webp" required><div class="hint">PNG, JPG, GIF, or WebP · up to 2 MB</div></div></div>
                    <div class="field"><label for="projectFiles">App project folder</label><div class="file-box"><input id="projectFiles" type="file" webkitdirectory directory multiple required><div class="hint" id="folderHint">Select a folder from your computer. Up to 500 files and 100 MB total.</div></div></div>
                    <div class="wide"><div id="uploadMessage" aria-live="polite"></div><button class="btn" type="submit" id="uploadBtn"><i class="fa-solid fa-plus"></i> Publish app</button></div>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="section-head"><div><h2>Published apps</h2><p class="subtitle">Unpublish an app to hide it from the Store without deleting user data.</p></div><button class="btn secondary small" id="refreshApps" type="button"><i class="fa-solid fa-rotate"></i> Refresh</button></div>
            <div class="app-list" id="appList"><div class="empty">Loading app catalog…</div></div>
        </section>
    </div>
</main>
<script>
(() => {
    const API = '/virtual-phone/user/admin/api/';
    const authPanel = document.getElementById('authPanel');
    const dashboard = document.getElementById('adminDashboard');
    const authTitle = document.getElementById('authTitle');
    const authDescription = document.getElementById('authDescription');
    const authSubmit = document.getElementById('authSubmit');
    let setupRequired = false;

    function showMessage(target, text, type = 'error') {
        target.className = text ? `message ${type}` : '';
        target.textContent = text;
    }

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {credentials: 'same-origin', ...options});
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || `Request failed (${response.status}).`);
        return data;
    }

    function showDashboard(admin) {
        authPanel.hidden = true;
        dashboard.hidden = false;
        document.querySelector('.subtitle').textContent = `Signed in as ${admin.username} · App catalog management`;
        loadApps();
    }

    async function loadApps() {
        const list = document.getElementById('appList');
        list.innerHTML = '<div class="empty">Loading app catalog…</div>';
        try {
            const data = await request(API + 'apps.php');
            const apps = data.apps || [];
            window.appUploadLimit = data.limits?.max_files || 20;
            document.getElementById('folderHint').textContent = `Select a project folder. Up to ${window.appUploadLimit} files per request; PHP upload settings may impose a smaller size limit.`;
            document.getElementById('totalApps').textContent = apps.length;
            document.getElementById('publishedApps').textContent = apps.filter(app => Number(app.is_published) === 1).length;
            document.getElementById('totalInstalls').textContent = apps.reduce((sum, app) => sum + Number(app.installs || 0), 0);
            if (!apps.length) {
                list.innerHTML = '<div class="empty">No apps yet. Upload your first project folder above.</div>';
                return;
            }
            list.replaceChildren(...apps.map(app => {
                const row = document.createElement('article');
                row.className = 'app-row';
                row.innerHTML = `<img class="app-icon" src="${escapeHtml(app.icon_path)}" alt=""><div class="app-copy"><strong>${escapeHtml(app.name)}</strong><small>${escapeHtml(app.slug)} · ${Number(app.installs)} installs · ${Number(app.is_published) === 1 ? 'Published' : 'Unpublished'}</small></div>`;
                const button = document.createElement('button');
                const published = Number(app.is_published) === 1;
                button.className = `btn small ${published ? 'danger' : 'secondary'}`;
                button.type = 'button';
                button.textContent = published ? 'Unpublish' : 'Publish';
                button.addEventListener('click', async () => {
                    button.disabled = true;
                    try {
                        await request(API + 'apps.php', {method:'PATCH', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:app.id, is_published:!published})});
                        await loadApps();
                    } catch (error) {
                        alert(error.message);
                        button.disabled = false;
                    }
                });
                row.append(button);
                return row;
            }));
        } catch (error) {
            list.innerHTML = '';
            const message = document.createElement('div');
            message.className = 'message error';
            message.textContent = error.message;
            list.append(message);
        }
    }

    async function init() {
        try {
            const data = await request(API + 'session.php');
            setupRequired = data.setup_required;
            if (data.admin) {
                showDashboard(data.admin);
                return;
            }
            authPanel.hidden = false;
            authTitle.textContent = setupRequired ? 'Create your admin account' : 'Admin sign in';
            authDescription.textContent = setupRequired
                ? 'Choose a unique admin username and a strong password. First-time setup is only available from this computer.'
                : 'Sign in to manage the Virtual Phone app catalog.';
            authSubmit.textContent = setupRequired ? 'Create admin account' : 'Sign in';
            document.getElementById('authPassword').setAttribute('autocomplete', setupRequired ? 'new-password' : 'current-password');
        } catch (error) {
            authPanel.hidden = false;
            showMessage(document.getElementById('authMessage'), error.message);
        }
    }

    document.getElementById('authForm').addEventListener('submit', async event => {
        event.preventDefault();
        const button = authSubmit;
        button.disabled = true;
        showMessage(document.getElementById('authMessage'), '');
        try {
            const data = await request(API + 'session.php', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({
                    action: setupRequired ? 'setup' : 'login',
                    username: document.getElementById('authUsername').value.trim(),
                    password: document.getElementById('authPassword').value
                })
            });
            showDashboard(data.admin);
        } catch (error) {
            showMessage(document.getElementById('authMessage'), error.message);
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('projectFiles').addEventListener('change', event => {
        const files = Array.from(event.target.files || []);
        const firstRelativePath = files[0]?.webkitRelativePath || '';
        if (firstRelativePath.includes('/')) {
            document.getElementById('folderName').value = firstRelativePath.split('/')[0];
        }
        document.getElementById('folderHint').textContent = files.length
            ? `${files.length} project files selected · PHP currently accepts up to ${window.appUploadLimit || 20} files per request`
            : 'Select a project folder from your computer.';
    });

    document.getElementById('uploadForm').addEventListener('submit', async event => {
        event.preventDefault();
        const button = document.getElementById('uploadBtn');
        const message = document.getElementById('uploadMessage');
        const files = Array.from(document.getElementById('projectFiles').files || []);
        if (!files.length) {
            showMessage(message, 'Select an app project folder first.');
            return;
        }
        const formData = new FormData();
        formData.append('name', document.getElementById('appName').value.trim());
        formData.append('folder_name', document.getElementById('folderName').value.trim());
        formData.append('description', document.getElementById('appDescription').value.trim());
        formData.append('icon', document.getElementById('appIcon').files[0]);
        formData.append('expected_file_count', String(files.length));
        for (const file of files) {
            formData.append('project_files[]', file, file.webkitRelativePath || file.name);
        }
        button.disabled = true;
        showMessage(message, `Uploading ${files.length} project files…`, 'success');
        try {
            const data = await request(API + 'apps.php', {method:'POST', body:formData});
            showMessage(message, data.message, 'success');
            event.target.reset();
            document.getElementById('folderHint').textContent = `Select an app folder. PHP currently accepts up to ${window.appUploadLimit || 20} files per request.`;
            await loadApps();
        } catch (error) {
            showMessage(message, error.message);
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('refreshApps').addEventListener('click', loadApps);
    document.getElementById('signOutBtn').addEventListener('click', async () => {
        try {
            await request(API + 'session.php', {method:'DELETE'});
            window.location.reload();
        } catch (error) {
            alert(error.message);
        }
    });

    init();
})();
</script>
</body>
</html>
