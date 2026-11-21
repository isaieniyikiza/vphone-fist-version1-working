<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instagram · Virtual Phone</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="/virtual-phone/user/settings/theme.css">
    <script defer src="/virtual-phone/user/settings/theme.js"></script>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 18px;
            background: linear-gradient(145deg, #eef1f8, #dfe5ef);
            color: #101828;
            font-family: Poppins, "Segoe UI", sans-serif;
        }
        button, input { font: inherit; }
        .instagram-app {
            width: min(420px, 100%);
            min-height: min(740px, calc(100vh - 36px));
            overflow: hidden;
            border: 1px solid rgba(20, 32, 56, .12);
            border-radius: 28px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(34, 43, 69, .20);
        }
        .splash {
            position: fixed;
            z-index: 20;
            inset: 0;
            display: grid;
            place-items: center;
            background: #fff;
            transition: opacity .35s ease, visibility .35s ease;
        }
        .splash.hidden { opacity: 0; visibility: hidden; }
        .splash-mark {
            width: 88px;
            height: 88px;
            display: grid;
            place-items: center;
            border-radius: 24px;
            color: #fff;
            background: radial-gradient(circle at 30% 107%, #fdf497 0, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
            font-size: 46px;
            animation: instagramPulse 1.1s ease-in-out infinite alternate;
            box-shadow: 0 16px 38px rgba(210, 36, 159, .24);
        }
        @keyframes instagramPulse { from { transform: scale(.92); } to { transform: scale(1); } }
        .topbar {
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #eef0f4;
        }
        .wordmark {
            font-family: Georgia, serif;
            font-size: 25px;
            font-weight: 700;
            letter-spacing: -1.5px;
        }
        .topbar-actions { display: flex; gap: 8px; }
        .icon-button {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: #f5f7fa;
            color: #1f2937;
            cursor: pointer;
        }
        main { min-height: calc(min(740px, calc(100vh - 36px)) - 68px); }
        .screen { display: none; min-height: 100%; }
        .screen.active { display: block; }
        .setup {
            min-height: calc(min(740px, calc(100vh - 36px)) - 68px);
            display: grid;
            align-content: center;
            padding: 28px;
        }
        .setup-mark {
            width: 76px;
            height: 76px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 23px;
            background: linear-gradient(145deg, #833ab4, #fd1d1d, #fcb045);
            color: #fff;
            font-size: 39px;
            box-shadow: 0 14px 34px rgba(220, 67, 117, .23);
        }
        .setup h1 { margin: 0; text-align: center; font-size: 21px; }
        .setup-copy { margin: 8px auto 22px; max-width: 300px; text-align: center; color: #667085; font-size: 12px; line-height: 1.65; }
        .field { display: grid; gap: 7px; margin-bottom: 12px; }
        .field label { color: #667085; font-size: 11px; font-weight: 600; }
        .field input {
            width: 100%;
            height: 48px;
            border: 1px solid #dfe3ea;
            border-radius: 13px;
            padding: 0 13px;
            background: #fafbfc;
            color: #101828;
            outline: none;
        }
        .field input:focus { border-color: #0095f6; box-shadow: 0 0 0 3px rgba(0, 149, 246, .11); }
        .primary {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 13px;
            background: #0095f6;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .primary:active { transform: scale(.985); }
        .security-note { margin: 13px 0 0; text-align: center; color: #98a2b3; font-size: 9.5px; line-height: 1.55; }
        .profile {
            padding: 18px 16px;
        }
        .profile-head { display: flex; align-items: center; gap: 14px; }
        .avatar {
            width: 78px;
            height: 78px;
            flex: none;
            display: grid;
            place-items: center;
            border: 3px solid #fff;
            border-radius: 50%;
            outline: 2px solid #ef4f91;
            background: linear-gradient(145deg, #f9cf59, #ef4f91, #7b4ca7);
            color: #fff;
            font-size: 28px;
            font-weight: 700;
        }
        .profile-stats { flex: 1; display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; }
        .profile-stats strong { display: block; font-size: 15px; }
        .profile-stats span { color: #667085; font-size: 9px; }
        .profile-name { margin: 12px 0 3px; font-size: 13px; font-weight: 700; }
        .profile-description { margin: 0 0 14px; color: #667085; font-size: 11px; }
        .profile-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .secondary {
            min-height: 38px;
            border: 1px solid #e4e7ec;
            border-radius: 9px;
            background: #fff;
            color: #344054;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }
        .feed { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px; margin-top: 16px; }
        .feed-item {
            aspect-ratio: 1;
            border-radius: 2px;
            background: linear-gradient(135deg, var(--tone1), var(--tone2));
        }
        .feed-item:nth-child(1) { --tone1:#2b8cff; --tone2:#7ed6ff; }
        .feed-item:nth-child(2) { --tone1:#ffd36a; --tone2:#ff5f78; }
        .feed-item:nth-child(3) { --tone1:#7edfb4; --tone2:#4d78de; }
        .feed-item:nth-child(4) { --tone1:#9d7ae5; --tone2:#f7a5cf; }
        .feed-item:nth-child(5) { --tone1:#ff9b67; --tone2:#ffcf70; }
        .feed-item:nth-child(6) { --tone1:#59c9db; --tone2:#7e69e6; }
        .saved-card {
            margin: 12px 16px 0;
            padding: 12px;
            border: 1px solid #eef0f4;
            border-radius: 13px;
            background: #fafbfc;
        }
        .saved-card strong { display: block; font-size: 11px; }
        .saved-card p { margin: 3px 0 9px; color: #667085; font-size: 10px; }
        .remove-account { border: 0; background: transparent; color: #d92d20; font-size: 10px; font-weight: 700; cursor: pointer; }
        .notice { min-height: 20px; margin: 10px 16px 0; text-align: center; color: #667085; font-size: 10px; }
        @media (max-width: 520px) {
            body { padding: 0; }
            .instagram-app { min-height: 100vh; border: 0; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="splash" id="splash" aria-label="Instagram is loading">
        <div class="splash-mark"><i class="fab fa-instagram"></i></div>
    </div>
    <div class="instagram-app">
        <header class="topbar">
            <div class="wordmark">Instagram</div>
            <div class="topbar-actions">
                <button class="icon-button" id="openOfficial" type="button" aria-label="Open official Instagram" title="Open Instagram"><i class="fa-brands fa-instagram"></i></button>
                <button class="icon-button" id="closeInstagram" type="button" aria-label="Close Instagram"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </header>
        <main>
            <section class="screen setup active" id="setupScreen">
                <div class="setup-mark"><i class="fab fa-instagram"></i></div>
                <h1>Set up Instagram</h1>
                <p class="setup-copy">Save a display username for this phone app. Instagram sign-in and your password remain on Instagram’s official website.</p>
                <form id="instagramForm">
                    <div class="field">
                        <label for="instagramUsername">Instagram username</label>
                        <input id="instagramUsername" name="username" type="text" maxlength="30" autocomplete="username" placeholder="@username" required>
                    </div>
                    <button class="primary" type="submit">Save and continue</button>
                </form>
                <p class="security-note">Passwords are never stored or filled here. Use Instagram’s official login page to sign in.</p>
            </section>
            <section class="screen" id="profileScreen">
                <div class="profile">
                    <div class="profile-head">
                        <div class="avatar" id="avatar">I</div>
                        <div class="profile-stats">
                            <div><strong>0</strong><span>Posts</span></div>
                            <div><strong>0</strong><span>Followers</span></div>
                            <div><strong>0</strong><span>Following</span></div>
                        </div>
                    </div>
                    <p class="profile-name" id="profileName">@username</p>
                    <p class="profile-description">Connected from Virtual Phone</p>
                    <div class="profile-actions">
                        <button class="secondary" id="editProfile" type="button">Edit profile</button>
                        <button class="secondary" id="openOfficialSecondary" type="button">Open Instagram</button>
                    </div>
                    <div class="feed" aria-label="Profile preview">
                        <div class="feed-item"></div><div class="feed-item"></div><div class="feed-item"></div>
                        <div class="feed-item"></div><div class="feed-item"></div><div class="feed-item"></div>
                    </div>
                </div>
                <div class="saved-card" id="savedCard">
                    <strong>Saved account for this phone</strong>
                    <p id="savedUsername"></p>
                    <button class="remove-account" id="removeAccount" type="button">Remove saved account</button>
                </div>
            </section>
            <p class="notice" id="notice" role="status" aria-live="polite"></p>
        </main>
    </div>
    <script>
        (() => {
            'use strict';
            const storageKey = 'vphone_instagram_username';
            const setupScreen = document.getElementById('setupScreen');
            const profileScreen = document.getElementByElementById('profileScreen');
            const usernameInput = document.getElementById('instagramUsername');
            const notice = document.getElementById('notice');

            function cleanUsername(value) {
                return String(value || '').trim().replace(/^@/, '').replace(/[^a-zA-Z0-9_.]/g, '').slice(0, 30);
            }

            function showNotice(message) {
                notice.textContent = message;
            }

            function showProfile(username) {
                const clean = cleanUsername(username);
                if (!clean) {
                    setupScreen.classList.add('active');
                    profileScreen.classList.remove('active');
                    return;
                }
                usernameInput.value = clean;
                document.getElementById('profileName').textContent = '@' + clean;
                document.getElementById('savedUsername').textContent = '@' + clean;
                document.getElementById('avatar').textContent = clean.charAt(0).toUpperCase();
                setupScreen.classList.remove('active');
                profileScreen.classList.add('active');
            }

            function openOfficialInstagram() {
                const popup = window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
                if (!popup) showNotice('Allow popups, then use the Instagram button again.');
                else showNotice('Instagram is open in a separate tab. Sign in on Instagram’s official page.');
            }

            document.getElementById('instagramForm').addEventListener('submit', event => {
                event.preventEventDefault();
            });
        })();
    </script>
</body>
</html>
