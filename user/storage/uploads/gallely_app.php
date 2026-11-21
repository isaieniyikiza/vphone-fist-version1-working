<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Gallery Pro · Edit</title>
    <link rel="stylesheet" href="/virtual-phone/user/settings/theme.css" />
    <script defer src="/virtual-phone/user/settings/theme.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <style>
          /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle, #eef2f3 0%, #8e9eab 100%);
            overflow: hidden;
        }

        /* Phone Shell */
        .phone {
            width: 320px;
            height: 520px;
            background: linear-gradient(180deg, #060606 0%, #121212 100%);
            border-radius: 48px;
            padding: 14px;
            position: relative;
            box-shadow:
                0 28px 70px -20px rgba(0, 0, 0, 0.7),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                inset 0 0 0 1px rgba(255, 255, 255, 0.03);
            user-select: none;
            overflow: hidden;
        }

        /* Side Buttons (Sleek Realism) */
        .phone::before {
            content: "";
            position: absolute;
            left: -5px;
            top: 130px;
            width: 5px;
            height: 50px;
            background: #2c2c2c;
            border-radius: 5px 0 0 5px;
            box-shadow: inset -1px 0 2px rgba(255,255,255,0.2);
        }

        .phone-power-btn {
            position: absolute;
            right: -5px;
            top: 170px;
            width: 5px;
            height: 75px;
            background: #2c2c2c;
            border-radius: 0 5px 5px 0;
            box-shadow: inset 1px 0 2px rgba(255,255,255,0.2);
        }

        /* Screen Container */
        .screen {
            width: 100%;
            height: 100%;
            border-radius: 32px;
            overflow: hidden;
            position: relative;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            flex-direction: column;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);
        }

        .screen::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 32px;
            pointer-events: none;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02);
        }

        /* ════════════════════════════════════════════════════════════════
                   DYNAMIC ISLAND
                   ════════════════════════════════════════════════════════════════ */
        .dynamic-island {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 85px;
            height: 22px;
            background: #000;
            border-radius: 20px;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: #fff;
            font-size: 10px;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .dynamic-island.notify-error {
            width: 240px;
            height: 40px;
            background: #ff3b30;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(255, 59, 48, 0.5);
            padding: 0 18px;
            pointer-events: auto;
        }
        .dynamic-island.notify-success {
            width: 240px;
            height: 40px;
            background: #34c759;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(52, 199, 89, 0.5);
            padding: 0 18px;
            pointer-events: auto;
        }
        .dynamic-island.notify-info {
            width: 240px;
            height: 40px;
            background: #007aff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 122, 255, 0.5);
            padding: 0 18px;
            pointer-events: auto;
        }

        /* ════════════════════════════════════════════════════════════════
                   STATUS BAR
                   ════════════════════════════════════════════════════════════════ */
        .status {
            position: absolute;
            top: 12px;
            left: 22px;
            right: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            z-index: 900;
            pointer-events: none;
        }
        .status .time {
            pointer-events: none;
        }
        .status .icons {
            display: flex;
            align-items: center;
            gap: 5px;
            pointer-events: auto;
        }
        .status .icons i {
            font-size: 10px;
        }
        .notification-trigger-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 27px;
            min-height: 27px;
            padding: 4px 6px;
            border: 0;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            cursor: pointer;
            pointer-events: auto;
        }
        .notification-trigger-btn .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 14px;
            height: 14px;
            padding: 0 3px;
            border: 1px solid rgba(15, 23, 42, 0.6);
            border-radius: 9px;
            background: #ff3b30;
            color: #fff;
            font-size: 8px;
            line-height: 12px;
            text-align: center;
        }
        .notification-badge[hidden] {
            display: none;
        }

        .notification-center {
            position: absolute;
            top: -75%;
            left: 0;
            right: 0;
            z-index: 1450;
            display: flex;
            max-height: 72%;
            flex-direction: column;
            padding: 38px 16px 14px;
            border-radius: 0 0 26px 26px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(12, 16, 29, 0.96);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.42);
            color: #fff;
            backdrop-filter: blur(22px);
            transition: top 0.35s ease;
        }
        .notification-center.open {
            top: 0;
        }
        .notification-center-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .notification-center-header h2 {
            margin: 0;
            font-size: 14px;
        }
        .notification-center-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .notification-center-actions button {
            padding: 5px 8px;
            border: 0;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 10px;
            cursor: pointer;
        }
        .notification-center-actions .clear-notifications {
            color: #ffaaa5;
        }
        .notification-list {
            display: flex;
            min-height: 70px;
            flex: 1;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            padding-top: 10px;
        }
        .notification-item {
            padding: 10px 11px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.07);
            color: #fff;
            text-align: left;
            cursor: pointer;
        }
        .notification-item strong {
            display: block;
            margin-bottom: 3px;
            font-size: 11px;
        }
        .notification-item span {
            display: block;
            color: rgba(255, 255, 255, 0.75);
            font-size: 10px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        .notification-item time,
        .notification-empty {
            color: rgba(255, 255, 255, 0.55);
            font-size: 10px;
        }
        .notification-empty {
            padding: 18px 8px;
            text-align: center;
        }

        /* ════════════════════════════════════════════════════════════════
                   WALLPAPER
                   ════════════════════════════════════════════════════════════════ */
        .wallpaper {
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top, #1f1a3a, #0f172a, #020617);
            background-size: cover;
            background-position: center;
            z-index: 1;
            transition: background 1.2s ease;
        }

        /* ════════════════════════════════════════════════════════════════
                   CLOCK
                   ════════════════════════════════════════════════════════════════ */
        .clock {
            position: absolute;
            top: 62px;
            width: 100%;
            text-align: center;
            color: white;
            z-index: 5;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.8);
            pointer-events: none;
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .clock h1 {
            font-size: 50px;
            font-weight: 500;
            letter-spacing: -1.5px;
            line-height: 1;
            background: linear-gradient(135deg, #fff 60%, rgba(255, 255, 255, 0.6));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .clock p {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.85;
            margin-top: 4px;
            color: #fff;
            -webkit-text-fill-color: #fff;
        }
        .clock.hidden {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }

        /* ════════════════════════════════════════════════════════════════
                   SCROLL INDICATOR
                   ════════════════════════════════════════════════════════════════ */
        .scroll-indicator {
            position: absolute;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.95);
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            animation: bounce 2s infinite;
            z-index: 50;
            cursor: pointer;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
            background: rgba(0, 0, 0, 0.5);
            padding: 8px 16px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: opacity 0.4s ease, transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .scroll-indicator:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateX(-50%) scale(1.04);
        }
        .scroll-indicator.hidden {
            opacity: 0;
            pointer-events: none;
            transform: translateX(-50%) translateY(10px);
        }
        .scroll-indicator i {
            font-size: 12px;
        }
        @keyframes bounce {
            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translate(-50%, 0);
            }
            40% {
                transform: translate(-50%, -6px);
            }
            60% {
                transform: translate(-50%, -3px);
            }
        }

        /* ════════════════════════════════════════════════════════════════
                   APPS CONTAINER
                   ════════════════════════════════════════════════════════════════ */
        .apps-container {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 400px;
            z-index: 10;
            padding: 22px 16px 44px;
            overflow-y: auto;
            transform: translateY(100%);
            opacity: 0;
            transition: transform 0.45s cubic-bezier(0.1, 0.76, 0.55, 0.94), opacity 0.35s ease;
            pointer-events: none;
            background: rgba(10, 14, 28, 0.88);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 32px 32px 0 0;
            box-shadow: 0 -15px 50px rgba(0, 0, 0, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .apps-container.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .apps-container::-webkit-scrollbar {
            display: none;
        }

        .apps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px 8px;
            padding-bottom: 20px;
        }
        .app {
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-decoration: none;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .app:active {
            transform: scale(0.88);
        }
        .app:hover .icon {
            transform: scale(1.08) translateY(-2px);
        }
        .icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 6px;
            border-radius: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            color: #fff;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .app span {
            display: block;
            font-size: 10px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            margin-top: 4px;
        }

        .gallery-icon {
            background: linear-gradient(135deg, #f7971e, #ffd200);
        }
        .camera-icon {
            background: linear-gradient(135deg, #434343, #000000);
        }
        .settings-icon {
            background: linear-gradient(135deg, #6c6c6c, #3a3a3a);
        }
        .logout-icon {
            background: linear-gradient(135deg, #ff3b30, #d63031);
        }

        /* ─── Hide logout app by default ─── */
        #logoutApp {
            display: none !important;
        }
        #logoutApp.show {
            display: flex !important;
        }

        /* ════════════════════════════════════════════════════════════════
                   DOCK
                   ════════════════════════════════════════════════════════════════ */
        .dock {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 110px;
            height: 4px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 30px;
            z-index: 1001;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .dock:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateX(-50%) scale(1.1);
        }

        /* ════════════════════════════════════════════════════════════════
                   LOGIN OVERLAY
                   ════════════════════════════════════════════════════════════════ */
        .login-overlay {
            position: absolute;
            inset: 0;
            z-index: 8;
            background: transparent;
            pointer-events: none;
            display: none;
        }
        .login-overlay.active {
            display: block;
            pointer-events: auto;
        }

        /* ════════════════════════════════════════════════════════════════
                   LOGIN PANEL
                   ════════════════════════════════════════════════════════════════ */
        .login-panel {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -440px;
            height: 430px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 30px 30px 0 0;
            box-shadow: 0 -16px 55px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: bottom 0.5s cubic-bezier(0.1, 0.76, 0.55, 0.94);
            padding: 20px 22px 26px;
            z-index: 999;
            display: flex;
            flex-direction: column;
        }
        .login-panel.show {
            bottom: 0;
        }
        .login-panel .handle {
            width: 44px;
            height: 5px;
            background: rgba(0, 0, 0, 0.12);
            border-radius: 10px;
            margin: 0 auto 18px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .login-panel .handle:hover {
            background: rgba(0, 0, 0, 0.2);
        }
        .login-panel h2 {
            text-align: center;
            margin-bottom: 2px;
            font-size: 22px;
            font-weight: 800;
            color: #111;
            letter-spacing: -0.5px;
        }
        .login-panel h2 i {
            color: #007aff;
            margin-right: 6px;
        }
        .login-panel p {
            text-align: center;
            color: #777;
            font-size: 12px;
            margin-bottom: 20px;
            font-weight: 400;
        }
        .login-panel input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid rgba(0, 0, 0, 0.06);
            border-radius: 14px;
            margin-bottom: 12px;
            outline: none;
            font-size: 14px;
            font-weight: 500;
            background: rgba(0, 0, 0, 0.03);
            color: #111;
            transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
        }
        .login-panel input:focus {
            border-color: #007aff;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.12);
        }
        .login-panel input::placeholder {
            color: #aaa;
            font-weight: 400;
        }
        .login-panel button {
            padding: 14px;
            border: none;
            border-radius: 14px;
            background: #007aff;
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: opacity 0.25s, transform 0.15s, box-shadow 0.3s;
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.25);
        }
        .login-panel button:active {
            transform: scale(0.97);
        }
        .login-panel button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .login-panel button.signup {
            background: #34c759;
            box-shadow: 0 4px 16px rgba(52, 199, 89, 0.25);
        }
        .login-panel button.signup:hover {
            box-shadow: 0 6px 24px rgba(52, 199, 89, 0.35);
        }
        .login-panel button:hover {
            box-shadow: 0 6px 24px rgba(0, 122, 255, 0.35);
        }
        .spinner {
            animation: spin 0.9s linear infinite;
            display: none;
        }
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
        .login-error {
            color: #ff3b30;
            font-size: 11px;
            text-align: center;
            margin-top: -6px;
            margin-bottom: 10px;
            min-height: 18px;
            font-weight: 600;
        }

        /* ════════════════════════════════════════════════════════════════
                   KEYBOARD
                   ════════════════════════════════════════════════════════════════ */
        .keyboard {
            position: absolute;
            bottom: -250px;
            left: 0;
            right: 0;
            height: 220px;
            background: rgba(210, 215, 222, 0.98);
            backdrop-filter: blur(18px);
            border-radius: 20px 20px 0 0;
            padding: 10px 6px 26px;
            box-shadow: 0 -10px 35px rgba(0, 0, 0, 0.25);
            transition: bottom 0.4s cubic-bezier(0.1, 0.76, 0.55, 0.94);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .keyboard.active {
            bottom: 0;
        }
        .keyboard-row {
            display: flex;
            justify-content: center;
            gap: 5px;
            width: 100%;
        }
        .key {
            background: #ffffff;
            color: #000;
            border: none;
            border-radius: 7px;
            height: 40px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
            transition: background 0.08s, transform 0.08s;
            min-width: 20px;
        }
        .key:active,
        .key.physical-active {
            background: #abb4be !important;
            transform: scale(0.92);
        }
        .key.special {
            background: #acb5be;
            flex: 1.35;
            font-size: 14px;
            font-weight: 600;
        }
        .key.space {
            flex: 5.2;
            font-size: 12px;
            letter-spacing: 1px;
            color: #555;
        }
        .login-panel.keyboard-open {
            height: 470px;
        }

        /* ════════════════════════════════════════════════════════════════
                   GALLERY APP OVERLAY
                   ════════════════════════════════════════════════════════════════ */
        .gallery-app-overlay {
            position: absolute;
            inset: 0;
            z-index: 120;
            display: none;
            background: #0a0a12;
            border-radius: 38px;
            overflow: hidden;
            flex-direction: column;
        }
        .gallery-app-overlay.show {
            display: flex;
        }

        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 18px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            flex-shrink: 0;
            z-index: 10;
            min-height: 52px;
        }
        .gallery-header .title {
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.3px;
        }
        .gallery-header .title i {
            color: #ffd200;
            font-size: 18px;
        }
        .gallery-header .header-actions {
            display: flex;
            gap: 8px;
        }
        .gallery-header .header-actions button {
            background: rgba(255, 255, 255, 0.06);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gallery-header .header-actions button:active {
            transform: scale(0.86);
        }
        .gallery-header .header-actions button:hover {
            background: rgba(255, 255, 255, 0.12);
        }
        .gallery-header .header-actions button.close-btn:hover {
            background: rgba(255, 59, 48, 0.25);
        }
        .gallery-header .header-actions button.active-mode {
            background: rgba(52, 199, 89, 0.2);
            color: #34c759;
        }

        /* Gallery Grid */
        .gallery-grid-container {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            align-content: start;
        }
        .gallery-grid-container::-webkit-scrollbar {
            width: 3px;
        }
        .gallery-grid-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 4px;
        }

        .gallery-grid-item {
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid transparent;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.25s, box-shadow 0.25s;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gallery-grid-item:hover {
            transform: scale(1.04);
            border-color: rgba(255, 255, 255, 0.12);
            z-index: 2;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        }
        .gallery-grid-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-grid-item .placeholder-icon {
            color: rgba(255, 255, 255, 0.06);
            font-size: 36px;
        }
        .gallery-grid-item .item-check {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 10px;
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            transition: transform 0.2s;
        }
        .gallery-grid-item.selected .item-check {
            display: flex;
            background: #34c759;
            border-color: #34c759;
            transform: scale(1);
        }
        .gallery-grid-item.selected {
            border-color: #34c759;
            box-shadow: 0 0 0 2px rgba(52, 199, 89, 0.3);
        }
        .gallery-grid-item .item-check i {
            font-size: 10px;
        }

        .gallery-empty {
            grid-column: 1 / -1;
            text-align: center;
            color: rgba(255, 255, 255, 0.25);
            padding: 60px 20px;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }
        .gallery-empty i {
            font-size: 52px;
            opacity: 0.3;
        }

        /* Selection bar */
        .selection-bar {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 10px 18px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(14px);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            flex-shrink: 0;
        }
        .selection-bar.show {
            display: flex;
        }
        .selection-bar .count {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
        }
        .selection-bar .count span {
            color: #34c759;
        }
        .selection-bar .actions {
            display: flex;
            gap: 12px;
        }
        .selection-bar .actions button {
            background: none;
            border: none;
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 500;
        }
        .selection-bar .actions button:hover {
            background: rgba(255, 255, 255, 0.06);
        }
        .selection-bar .actions button.danger:hover {
            background: rgba(255, 59, 48, 0.15);
            color: #ff3b30;
        }

        /* ════════════════════════════════════════════════════════════════
                   PHOTO EDITOR OVERLAY  —  PROFESSIONAL
                   ════════════════════════════════════════════════════════════════ */
        .photo-editor-overlay {
            position: absolute;
            inset: 0;
            z-index: 140;
            background: #08080e;
            display: none;
            flex-direction: column;
            border-radius: 38px;
            overflow: hidden;
        }
        .photo-editor-overlay.show {
            display: flex;
        }

        /* Editor header */
        .editor-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            flex-shrink: 0;
            z-index: 10;
            min-height: 50px;
        }
        .editor-header .back-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 10px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        .editor-header .back-btn:hover {
            background: rgba(255, 255, 255, 0.06);
        }
        .editor-header .back-btn i {
            font-size: 14px;
        }
        .editor-header .editor-actions {
            display: flex;
            gap: 6px;
        }
        .editor-header .editor-actions button {
            background: rgba(255, 255, 255, 0.05);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .editor-header .editor-actions button:active {
            transform: scale(0.86);
        }
        .editor-header .editor-actions button:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .editor-header .editor-actions button.danger:hover {
            background: rgba(255, 59, 48, 0.2);
        }
        .editor-header .editor-actions button.save-btn:hover {
            background: rgba(52, 199, 89, 0.2);
        }
        .editor-header .editor-actions button.save-btn {
            color: #34c759;
        }

        /* Editor canvas area */
        .editor-canvas-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #08080e;
            position: relative;
            padding: 4px;
        }
        .editor-canvas-area img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: filter 0.2s ease, transform 0.2s ease;
            border-radius: 4px;
            box-shadow: 0 4px 40px rgba(0, 0, 0, 0.6);
        }

        /* Editor bottom panel — Professional */
        .editor-bottom-panel {
            background: rgba(10, 10, 18, 0.92);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            padding: 12px 14px 18px;
            flex-shrink: 0;
            max-height: 210px;
            overflow-y: auto;
        }
        .editor-bottom-panel::-webkit-scrollbar {
            width: 2px;
        }
        .editor-bottom-panel::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
        }

        .editor-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 12px;
            flex-wrap: wrap;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            padding-bottom: 8px;
        }
        .editor-tabs .tab {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 8px;
            padding: 5px 14px;
            color: rgba(255, 255, 255, 0.35);
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .editor-tabs .tab:hover {
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.6);
        }
        .editor-tabs .tab.active {
            background: rgba(52, 199, 89, 0.12);
            border-color: #34c759;
            color: #fff;
            box-shadow: 0 0 20px rgba(52, 199, 89, 0.05);
        }

        .editor-controls {
            display: none;
            flex-direction: column;
            gap: 8px;
            animation: fadeSlideIn 0.3s ease;
        }
        .editor-controls.active {
            display: flex;
        }

        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .editor-slider-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .editor-slider-group label {
            color: rgba(255, 255, 255, 0.5);
            font-size: 10px;
            font-weight: 700;
            min-width: 56px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .editor-slider-group input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            flex: 1;
            height: 3px;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.1);
            outline: none;
            transition: background 0.3s;
        }
        .editor-slider-group input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            transition: transform 0.15s;
        }
        .editor-slider-group input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.1);
        }
        .editor-slider-group input[type="range"]::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            cursor: pointer;
            border: none;
        }
        .editor-slider-group .value {
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            min-width: 32px;
            text-align: center;
            background: rgba(255, 255, 255, 0.04);
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Filter grid — more filters */
        .filter-grid {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 8px;
            padding: 4px 12px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.7);
        }
        .filter-btn.active {
            background: rgba(52, 199, 89, 0.12);
            border-color: #34c759;
            color: #fff;
            box-shadow: 0 0 20px rgba(52, 199, 89, 0.04);
        }

        /* Rotate controls */
        .rotate-grid {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .rotate-grid .filter-btn {
            padding: 4px 10px;
            font-size: 10px;
        }
        .rotate-grid .filter-btn i {
            margin-right: 4px;
        }

        /* Rename controls */
        .rename-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .rename-group input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 8px;
            padding: 8px 14px;
            color: #fff;
            font-size: 13px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-weight: 500;
        }
        .rename-group input:focus {
            border-color: #34c759;
            box-shadow: 0 0 0 3px rgba(52, 199, 89, 0.1);
        }
        .rename-group input::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }
        .rename-group button {
            background: #34c759;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
        }
        .rename-group button:active {
            transform: scale(0.94);
        }
        .rename-group button:hover {
            opacity: 0.85;
        }
        .rename-group button.danger {
            background: #ff3b30;
        }
        .rename-group button.danger:hover {
            opacity: 0.85;
        }
        .rename-info {
            color: rgba(255, 255, 255, 0.2);
            font-size: 10px;
            margin-top: 4px;
        }
        .rename-info span {
            color: rgba(255, 255, 255, 0.5);
        }

        /* ════════════════════════════════════════════════════════════════
                   RESPONSIVE
                   ════════════════════════════════════════════════════════════════ */
        @media (max-width: 370px) {
            .phone {
                width: 300px;
                height: 500px;
                border-radius: 42px;
                padding: 12px;
            }
            .screen {
                border-radius: 30px;
            }
            .gallery-app-overlay {
                border-radius: 30px;
            }
            .photo-editor-overlay {
                border-radius: 30px;
            }
            .clock h1 {
                font-size: 40px;
            }
            .icon {
                width: 44px;
                height: 44px;
                font-size: 18px;
                border-radius: 12px;
            }
            .app span {
                font-size: 9px;
            }
            .apps-grid {
                gap: 14px 6px;
            }
            .login-panel {
                height: 390px;
                padding: 16px 16px 20px;
            }
            .login-panel h2 {
                font-size: 18px;
            }
            .login-panel input {
                padding: 11px 14px;
                font-size: 13px;
            }
            .login-panel button {
                padding: 11px;
                font-size: 13px;
            }
            .gallery-grid-container {
                gap: 6px;
                padding: 8px;
            }
            .gallery-header {
                padding: 10px 14px;
                min-height: 44px;
            }
            .gallery-header .title {
                font-size: 15px;
            }
            .editor-header {
                padding: 8px 12px;
                min-height: 42px;
            }
            .editor-bottom-panel {
                padding: 8px 10px 14px;
                max-height: 170px;
            }
        }

        @media (max-width: 420px) {
            .phone {
                width: 100vw;
                height: 100vh;
                border-radius: 0;
                padding: 0;
            }
            .phone-power-btn {
                display: none;
            }
            .screen {
                border-radius: 0;
            }
            .gallery-app-overlay {
                border-radius: 0;
            }
            .photo-editor-overlay {
                border-radius: 0;
            }
            .dynamic-island {
                top: 8px;
            }
            .status {
                top: 10px;
                left: 16px;
                right: 16px;
            }
            .clock {
                top: 56px;
            }
            .clock h1 {
                font-size: 40px;
            }
            .apps-container {
                height: 380px;
                padding: 16px 12px 32px;
            }
            .icon {
                width: 46px;
                height: 46px;
                font-size: 19px;
            }
            .apps-grid {
                gap: 14px 6px;
            }
            .login-panel {
                height: 400px;
                padding: 16px 16px 20px;
            }
            .keyboard {
                height: 200px;
                padding: 8px 4px 20px;
            }
            .key {
                height: 36px;
                font-size: 13px;
            }
            .editor-bottom-panel {
                max-height: 180px;
                padding: 8px 10px 14px;
            }
            .editor-tabs .tab {
                font-size: 9px;
                padding: 4px 10px;
            }
            .filter-btn {
                font-size: 9px;
                padding: 3px 8px;
            }
        }
    </style>
</head>
<body>

    <div class="phone">
        <div class="phone-power-btn"></div>
        <div class="screen" id="screen">

            <!-- Wallpaper -->
            <div class="wallpaper" id="wallpaper"></div>

            <!-- Dynamic Island -->
            <div class="dynamic-island" id="dynamicIsland"></div>

            <!-- Status Bar -->
            <div class="status" id="statusBar">
                <div class="time" id="time">12:00</div>
                <div class="icons">
                    <i class="fas fa-signal"></i>
                    <i class="fas fa-wifi"></i>
                    <i class="fas fa-battery-full"></i>
                    <button class="notification-trigger-btn" id="ncTriggerBtn" type="button" aria-label="Open notifications" title="Notifications">
                        <i class="fas fa-bell" aria-hidden="true"></i>
                        <span class="notification-badge" id="notificationBadge" hidden>0</span>
                    </button>
                </div>
            </div>

            <section class="notification-center" id="notificationCenter" aria-label="Notifications" aria-hidden="true">
                <div class="notification-center-header">
                    <h2>Notifications</h2>
                    <div class="notification-center-actions">
                        <button type="button" id="ncClearBtn" class="clear-notifications">Clear</button>
                        <button type="button" id="ncHandle" aria-label="Close notifications"><i class="fas fa-times" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="notification-list" id="ncList" aria-live="polite"></div>
            </section>

            <!-- Clock -->
            <div class="clock" id="clockWidget">
                <h1 id="clock">12:00</h1>
                <p id="date">Tuesday, July 14</p>
            </div>

            <!-- Scroll Indicator -->
            <div class="scroll-indicator" id="scrollIndicator">
                <span id="indicatorText">📷 Open Gallery</span>
                <i class="fas fa-chevron-up"></i>
            </div>

            <!-- Login Overlay -->
            <div class="login-overlay active" id="loginOverlay"></div>

            <!-- Apps Container -->
            <div class="apps-container" id="appsContainer">
                <div class="apps-grid" id="appsGrid">
                    <div class="app" id="galleryAppLauncher">
                        <div class="icon gallery-icon"><i class="fas fa-images"></i></div>
                        <span>Gallery</span>
                    </div>
                    <div class="app" id="cameraAppLauncher">
                        <div class="icon camera-icon"><i class="fas fa-camera"></i></div>
                        <span>Camera</span>
                    </div>
                    <div class="app" id="settingsAppLauncher">
                        <div class="icon settings-icon"><i class="fas fa-cog"></i></div>
                        <span>Settings</span>
                    </div>
                    <div class="app" id="logoutApp">
                        <div class="icon logout-icon"><i class="fas fa-power-off"></i></div>
                        <span>Logout</span>
                    </div>
                </div>
            </div>

            <!-- Dock -->
            <div class="dock" id="homeIndicator"></div>

            <!-- ═══════════ GALLERY APP OVERLAY ═══════════ -->
            <div class="gallery-app-overlay" id="galleryAppOverlay">
                <div class="gallery-header">
                    <div class="title">
                        <i class="fas fa-images"></i>
                        <span>Gallery</span>
                    </div>
                    <div class="header-actions">
                        <button id="gallerySelectToggle" title="Select photos">
                            <i class="fas fa-check-circle"></i>
                        </button>
                        <button class="close-btn" id="closeGalleryAppBtn" title="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="gallery-grid-container" id="galleryGridContainer">
                    <div class="gallery-empty">
                        <i class="fas fa-camera"></i>
                        <span>No photos yet.<br />Capture your first photo!</span>
                    </div>
                </div>

                <div class="selection-bar" id="selectionBar">
                    <span class="count" id="selectionCount"><span>0</span> selected</span>
                    <div class="actions">
                        <button id="selectAllBtn"><i class="fas fa-check-double"></i> All</button>
                        <button class="danger" id="deleteSelectedBtn"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </div>
            </div>

            <!-- ═══════════ PHOTO EDITOR OVERLAY ═══════════ -->
            <div class="photo-editor-overlay" id="photoEditorOverlay">
                <!-- Header -->
                <div class="editor-header">
                    <button class="back-btn" id="editorBackBtn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </button>
                    <div class="editor-actions">
                        <button id="editorRenameBtn" title="Rename">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button id="editorDeleteBtn" class="danger" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button id="editorSaveBtn" class="save-btn" title="Save edits">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>

                <!-- Canvas -->
                <div class="editor-canvas-area" id="editorCanvasArea">
                    <img id="editorImage" src="" alt="Editing" />
                </div>

                <!-- Bottom Panel -->
                <div class="editor-bottom-panel" id="editorBottomPanel">
                    <!-- Tabs -->
                    <div class="editor-tabs" id="editorTabs">
                        <span class="tab active" data-tab="adjust">Adjust</span>
                        <span class="tab" data-tab="filters">Filters</span>
                        <span class="tab" data-tab="rotate">Transform</span>
                        <span class="tab" data-tab="rename">Rename</span>
                    </div>

                    <!-- Adjust -->
                    <div class="editor-controls active" id="ctrlAdjust">
                        <div class="editor-slider-group">
                            <label>Brightness</label>
                            <input type="range" id="brightnessSlider" min="0" max="200" value="100" />
                            <span class="value" id="brightnessValue">100%</span>
                        </div>
                        <div class="editor-slider-group">
                            <label>Contrast</label>
                            <input type="range" id="contrastSlider" min="0" max="200" value="100" />
                            <span class="value" id="contrastValue">100%</span>
                        </div>
                        <div class="editor-slider-group">
                            <label>Saturation</label>
                            <input type="range" id="saturationSlider" min="0" max="200" value="100" />
                            <span class="value" id="saturationValue">100%</span>
                        </div>
                        <div class="editor-slider-group">
                            <label>Blur</label>
                            <input type="range" id="blurSlider" min="0" max="12" value="0" step="0.5" />
                            <span class="value" id="blurValue">0px</span>
                        </div>
                        <div class="editor-slider-group">
                            <label>Warmth</label>
                            <input type="range" id="warmthSlider" min="0" max="200" value="100" />
                            <span class="value" id="warmthValue">100%</span>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="editor-controls" id="ctrlFilters">
                        <div class="filter-grid">
                            <span class="filter-btn active" data-filter="none">None</span>
                            <span class="filter-btn" data-filter="grayscale">B&W</span>
                            <span class="filter-btn" data-filter="sepia">Sepia</span>
                            <span class="filter-btn" data-filter="invert">Invert</span>
                            <span class="filter-btn" data-filter="vintage">Vintage</span>
                            <span class="filter-btn" data-filter="cool">Cool</span>
                            <span class="filter-btn" data-filter="warm">Warm</span>
                            <span class="filter-btn" data-filter="highcontrast">High Contrast</span>
                            <span class="filter-btn" data-filter="noir">Noir</span>
                            <span class="filter-btn" data-filter="chrome">Chrome</span>
                            <span class="filter-btn" data-filter="fade">Fade</span>
                            <span class="filter-btn" data-filter="instant">Instant</span>
                        </div>
                    </div>

                    <!-- Transform -->
                    <div class="editor-controls" id="ctrlRotate">
                        <div class="rotate-grid">
                            <button class="filter-btn" id="rotateLeftBtn"><i class="fas fa-undo"></i> Left</button>
                            <button class="filter-btn" id="rotateRightBtn"><i class="fas fa-redo"></i> Right</button>
                            <button class="filter-btn" id="flipHorizontalBtn"><i class="fas fa-arrows-alt-h"></i> Flip H</button>
                            <button class="filter-btn" id="flipVerticalBtn"><i class="fas fa-arrows-alt-v"></i> Flip V</button>
                            <button class="filter-btn" id="resetTransformBtn"><i class="fas fa-undo-alt"></i> Reset</button>
                        </div>
                    </div>

                    <!-- Rename -->
                    <div class="editor-controls" id="ctrlRename">
                        <div class="rename-group">
                            <input type="text" id="renameInput" placeholder="New file name" />
                            <button id="renameConfirmBtn">Rename</button>
                            <button class="danger" id="renameCancelBtn">Cancel</button>
                        </div>
                        <div class="rename-info">
                            Current: <span id="currentFileName">image.jpg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ LOGIN PANEL ═══════════ -->
            <div id="loginPanel" class="login-panel">
                <div class="handle" id="loginHandle"></div>
                <h2><i class="fas fa-lock"></i> Welcome</h2>
                <p>Sign in to access your Virtual Phone</p>
                <form id="loginForm" onsubmit="return false;">
                    <input type="text" id="usernameInput" placeholder="Username" inputmode="none" autocomplete="off" required />
                    <input type="password" id="passwordInput" placeholder="Password" inputmode="none" required />
                    <div class="login-error" id="loginError"></div>
                    <button type="submit" id="loginBtn">
                        <i class="fas fa-spinner spinner" id="loginSpinner"></i>
                        <span id="btnText">Login</span>
                    </button>
                    <button type="button" class="signup" id="signupBtn">Create Account</button>
                </form>
            </div>

            <!-- ═══════════ KEYBOARD ═══════════ -->
            <div id="keyboard" class="keyboard">
                <div class="keyboard-row" id="row-1">
                    <button class="key" data-key="q">q</button>
                    <button class="key" data-key="w">w</button>
                    <button class="key" data-key="e">e</button>
                    <button class="key" data-key="r">r</button>
                    <button class="key" data-key="t">t</button>
                    <button class="key" data-key="y">y</button>
                    <button class="key" data-key="u">u</button>
                    <button class="key" data-key="i">i</button>
                    <button class="key" data-key="o">o</button>
                    <button class="key" data-key="p">p</button>
                </div>
                <div class="keyboard-row" id="row-2">
                    <button class="key" data-key="a">a</button>
                    <button class="key" data-key="s">s</button>
                    <button class="key" data-key="d">d</button>
                    <button class="key" data-key="f">f</button>
                    <button class="key" data-key="g">g</button>
                    <button class="key" data-key="h">h</button>
                    <button class="key" data-key="j">j</button>
                    <button class="key" data-key="k">k</button>
                    <button class="key" data-key="l">l</button>
                </div>
                <div class="keyboard-row" id="row-3">
                    <button class="key special" id="shift-btn" data-key="shift">⇧</button>
                    <button class="key" data-key="z">z</button>
                    <button class="key" data-key="x">x</button>
                    <button class="key" data-key="c">c</button>
                    <button class="key" data-key="v">v</button>
                    <button class="key" data-key="b">b</button>
                    <button class="key" data-key="n">n</button>
                    <button class="key" data-key="m">m</button>
                    <button class="key special" id="backspace-btn" data-key="backspace">⌫</button>
                </div>
                <div class="keyboard-row">
                    <button class="key special" id="mode-btn" data-key="mode">123</button>
                    <button class="key space" id="space-btn" data-key=" ">space</button>
                    <button class="key special" id="close-keyboard-btn" data-key="close">▼</button>
                </div>
            </div>

        </div><!-- /screen -->
    </div><!-- /phone -->

    <script>
        /**
         * =====================================================================
         *  GALLERY PRO  —  Professional Photo Editor + Gallery
         *  FIXED: login, logout, keyboard, gallery, navigation
         * =====================================================================
         */
        (function() {
            'use strict';

            // ─── DOM REFS ───
            const $ = id => document.getElementById(id);
            const qs = (sel, ctx = document) => ctx.querySelector(sel);
            const qsa = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

            const DOM = {
                screen: $('screen'),
                clock: $('clock'),
                time: $('time'),
                date: $('date'),
                ncTriggerBtn: $('ncTriggerBtn'),
                notificationBadge: $('notificationBadge'),
                notificationCenter: $('notificationCenter'),
                ncList: $('ncList'),
                ncClearBtn: $('ncClearBtn'),
                ncHandle: $('ncHandle'),
                clockWidget: $('clockWidget'),
                wallpaper: $('wallpaper'),
                loginPanel: $('loginPanel'),
                loginHandle: $('loginHandle'),
                loginOverlay: $('loginOverlay'),
                scrollIndicator: $('scrollIndicator'),
                indicatorText: $('indicatorText'),
                keyboard: $('keyboard'),
                usernameInput: $('usernameInput'),
                passwordInput: $('passwordInput'),
                loginForm: $('loginForm'),
                loginBtn: $('loginBtn'),
                btnText: $('btnText'),
                loginSpinner: $('loginSpinner'),
                loginError: $('loginError'),
                dynamicIsland: $('dynamicIsland'),
                logoutApp: $('logoutApp'),
                allKeys: qsa('.key'),
                row1: $('row-1'),
                row2: $('row-2'),
                row3: $('row-3'),
                shiftBtn: $('shift-btn'),
                backspaceBtn: $('backspace-btn'),
                spaceBtn: $('space-btn'),
                modeBtn: $('mode-btn'),
                appsContainer: $('appsContainer'),
                homeIndicator: $('homeIndicator'),
                signupBtn: $('signupBtn'),
                cameraAppLauncher: $('cameraAppLauncher'),
                settingsAppLauncher: $('settingsAppLauncher'),

                // Gallery
                galleryAppOverlay: $('galleryAppOverlay'),
                galleryGridContainer: $('galleryGridContainer'),
                closeGalleryAppBtn: $('closeGalleryAppBtn'),
                galleryAppLauncher: $('galleryAppLauncher'),
                gallerySelectToggle: $('gallerySelectToggle'),
                selectionBar: $('selectionBar'),
                selectionCount: $('selectionCount'),
                selectAllBtn: $('selectAllBtn'),
                deleteSelectedBtn: $('deleteSelectedBtn'),

                // Editor
                photoEditorOverlay: $('photoEditorOverlay'),
                editorImage: $('editorImage'),
                editorBackBtn: $('editorBackBtn'),
                editorRenameBtn: $('editorRenameBtn'),
                editorDeleteBtn: $('editorDeleteBtn'),
                editorSaveBtn: $('editorSaveBtn'),
                editorCanvasArea: $('editorCanvasArea'),
                editorBottomPanel: $('editorBottomPanel'),
                editorTabs: $('editorTabs'),
                brightnessSlider: $('brightnessSlider'),
                contrastSlider: $('contrastSlider'),
                saturationSlider: $('saturationSlider'),
                blurSlider: $('blurSlider'),
                warmthSlider: $('warmthSlider'),
                brightnessValue: $('brightnessValue'),
                contrastValue: $('contrastValue'),
                saturationValue: $('saturationValue'),
                blurValue: $('blurValue'),
                warmthValue: $('warmthValue'),
                renameInput: $('renameInput'),
                renameConfirmBtn: $('renameConfirmBtn'),
                renameCancelBtn: $('renameCancelBtn'),
                currentFileName: $('currentFileName'),
                rotateLeftBtn: $('rotateLeftBtn'),
                rotateRightBtn: $('rotateRightBtn'),
                flipHorizontalBtn: $('flipHorizontalBtn'),
                flipVerticalBtn: $('flipVerticalBtn'),
                resetTransformBtn: $('resetTransformBtn'),
            };

            // ─── STATE ───
            const STATE = {
                isShifted: false,
                isNumMode: false,
                activeInput: null,
                isLoggedIn: false,
                isLoginPending: false,
                isLoggingOut: false,
                wallpaperIndex: 0,
                sessionUser: null,
                isAppsOpen: false,

                // Gallery
                images: [],
                selectedImages: [],
                selectionMode: false,
                currentImageIndex: -1,
                currentImageSrc: null,

                // Editor
                originalImageData: null,
                currentFilter: 'none',
                rotation: 0,
                flipH: false,
                flipV: false,
                brightness: 100,
                contrast: 100,
                saturation: 100,
                blur: 0,
                warmth: 100,
                isDirty: false,
            };

            const CONFIG = {
                CLOCK_INTERVAL: 1000,
                NOTIFICATION_DURATION: 3200,
                LOGIN_TIMEOUT: 15000,
                STORAGE_KEY: 'vphone_user',
                GALLERY_ENDPOINT: 'get_images.php',
                DELETE_ENDPOINT: 'delete_image.php',
                RENAME_ENDPOINT: 'rename_image.php',
                DEMO_USERNAME: 'admin',
                DEMO_PASSWORD: 'admin',
                DASHBOARD_URL: '/virtual-phone/user/user_phone_dashboard.html',
                NOTIFICATIONS_KEY: 'vphone_notifications',
            };

            const WALLPAPERS = [
                "linear-gradient(135deg, rgba(10,10,30,0.7), rgba(30,20,60,0.8)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=900&q=80')",
                "linear-gradient(135deg, rgba(20,30,50,0.7), rgba(10,50,80,0.8)), url('https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=900&q=80')",
                "linear-gradient(135deg, rgba(40,20,30,0.7), rgba(80,30,50,0.8)), url('https://images.unsplash.com/photo-1470071459604-7b8ec44ffd5d?auto=format&fit=crop&w=900&q=80')",
            ];

            const KEYS_ALPHABET = ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k',
                'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm'
            ];
            const KEYS_NUMBERS = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '/', ':', ';', '(', '7', '&', '@',
                '"', '.', ',', '?', '!', "'", '_'
            ];

            // ─── HELPERS ───
            function showNotification(message, type = 'info') {
                const island = DOM.dynamicIsland;
                if (!island) return;
                island.textContent = message;
                const cls = type === 'success' ? 'notify-success' :
                    type === 'error' ? 'notify-error' : 'notify-info';
                island.className = 'dynamic-island ' + cls;
                clearTimeout(island._hideTimer);
                island._hideTimer = setTimeout(() => {
                    island.className = 'dynamic-island';
                    island.textContent = '';
                }, CONFIG.NOTIFICATION_DURATION);
            }

            function setLoginError(msg) {
                if (DOM.loginError) DOM.loginError.textContent = msg || '';
            }

            // ─── CLOCK ───
            function updateClock() {
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const timeStr = h + ':' + m;
                if (DOM.clock) DOM.clock.textContent = timeStr;
                if (DOM.time) DOM.time.textContent = timeStr;
                if (DOM.date) {
                    DOM.date.textContent = now.toLocaleDateString('en-US', {
                        weekday: 'long',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }

            // ─── WALLPAPER ───
            let wallpaperTimer = null;

            function setWallpaper(style) {
                if (DOM.wallpaper) DOM.wallpaper.style.background = style;
            }

            function rotateWallpaper() {
                if (STATE.isLoggedIn && STATE.sessionUser && STATE.sessionUser.profile_image) {
                    setWallpaper(
                        `linear-gradient(135deg, rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('${STATE.sessionUser.profile_image}')`
                        );
                    return;
                }
                setWallpaper(WALLPAPERS[STATE.wallpaperIndex]);
                STATE.wallpaperIndex = (STATE.wallpaperIndex + 1) % WALLPAPERS.length;
            }

            function startWallpaperRotation() {
                if (wallpaperTimer) clearInterval(wallpaperTimer);
                rotateWallpaper();
                wallpaperTimer = setInterval(rotateWallpaper, 10000);
            }

            // ─── APP DRAWER ───
            function toggleApps(open) {
                const container = DOM.appsContainer;
                const indicator = DOM.scrollIndicator;
                if (open === undefined) open = !STATE.isAppsOpen;

                if (open) {
                    if (!STATE.isLoggedIn) {
                        showLoginPanel();
                        return;
                    }
                    container.classList.add('open');
                    indicator.classList.add('hidden');
                    STATE.isAppsOpen = true;
                } else {
                    container.classList.remove('open');
                    indicator.classList.remove('hidden');
                    STATE.isAppsOpen = false;
                }
            }

            // ─── LOGIN ───
            function showLoginPanel() {
                if (STATE.isLoggedIn) return;
                DOM.loginPanel.classList.add('show');
                DOM.loginOverlay.classList.add('active');
                if (DOM.scrollIndicator) DOM.scrollIndicator.classList.add('hidden');
                toggleApps(false);
                setLoginError('');
                // Ensure logout button is hidden when not logged in
                toggleLogoutButton(false);
            }

            function hideLoginPanel() {
                DOM.loginPanel.classList.remove('show');
                DOM.loginOverlay.classList.remove('active');
                hideKeyboard();
                if (DOM.scrollIndicator) DOM.scrollIndicator.classList.remove('hidden');
                setLoginError('');
            }

            // ─── KEYBOARD ───
            function showKeyboard(inputTarget) {
                if (!inputTarget) return;
                STATE.activeInput = inputTarget;
                DOM.keyboard.classList.add('active');
                DOM.loginPanel.classList.add('keyboard-open');
                inputTarget.focus();
            }

            function hideKeyboard() {
                DOM.keyboard.classList.remove('active');
                DOM.loginPanel.classList.remove('keyboard-open');
                if (STATE.activeInput) {
                    STATE.activeInput.blur();
                    STATE.activeInput = null;
                }
            }

            function handleKeyInput(char) {
                const input = STATE.activeInput;
                if (!input) return;
                switch (char) {
                    case 'space':
                        input.value += ' ';
                        break;
                    case '⌫':
                    case 'backspace':
                        input.value = input.value.slice(0, -1);
                        break;
                    case '⇧':
                        toggleShift();
                        return;
                    case '123':
                    case 'ABC':
                        toggleMode();
                        return;
                    case '▼':
                        hideKeyboard();
                        return;
                    default:
                        input.value += char;
                        if (STATE.isShifted) toggleShift();
                        break;
                }
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }

            function toggleShift() {
                STATE.isShifted = !STATE.isShifted;
                if (DOM.shiftBtn) DOM.shiftBtn.style.background = STATE.isShifted ? '#ffffff' : '#acb5be';
                DOM.allKeys.forEach(key => {
                    const val = key.getAttribute('data-key');
                    if (val && val.length === 1 && /^[a-zA-Z]$/.test(val)) {
                        key.textContent = STATE.isShifted ? val.toUpperCase() : val.toLowerCase();
                    }
                });
            }

            function toggleMode() {
                STATE.isNumMode = !STATE.isNumMode;
                if (DOM.modeBtn) DOM.modeBtn.textContent = STATE.isNumMode ? 'ABC' : '123';
                if (DOM.shiftBtn) DOM.shiftBtn.textContent = STATE.isNumMode ? '#+=' : '⇧';
                const currentSet = STATE.isNumMode ? KEYS_NUMBERS : KEYS_ALPHABET;
                const rows = [DOM.row1, DOM.row2, DOM.row3];
                let idx = 0;
                rows.forEach(row => {
                    if (!row) return;
                    row.querySelectorAll('.key').forEach(key => {
                        if (key.id === 'shift-btn' || key.id === 'backspace-btn' || key.id ===
                            'mode-btn') return;
                        if (idx < currentSet.length) {
                            key.textContent = currentSet[idx];
                            key.setAttribute('data-key', currentSet[idx]);
                            idx++;
                        }
                    });
                });
            }

            function onKeyClick(e) {
                const key = e.currentTarget;
                if (!key || !STATE.activeInput) return;
                const text = key.textContent.trim();
                let char = text;
                if (key.id === 'shift-btn') char = '⇧';
                else if (key.id === 'backspace-btn') char = '⌫';
                else if (key.id === 'mode-btn') char = STATE.isNumMode ? 'ABC' : '123';
                else if (key.id === 'space-btn') char = 'space';
                handleKeyInput(char);
            }

            function highlightPhysicalKey(el) {
                if (!el) return;
                el.classList.add('physical-active');
                clearTimeout(el._phyTimer);
                el._phyTimer = setTimeout(() => el.classList.remove('physical-active'), 100);
            }

            DOM.allKeys.forEach(key => key.addEventListener('click', onKeyClick));
            DOM.keyboard.addEventListener('click', function(e) { e.stopPropagation(); });

            document.addEventListener('keydown', function(e) {
                const input = STATE.activeInput;
                if (!input) return;
                const key = e.key;
                if (key === 'Backspace') { handleKeyInput('⌫');
                    e.preventDefault();
                    highlightPhysicalKey(DOM.backspaceBtn); return; }
                if (key === ' ') { handleKeyInput('space');
                    e.preventDefault();
                    highlightPhysicalKey(DOM.spaceBtn); return; }
                if (key === 'Shift') { toggleShift();
                    e.preventDefault();
                    highlightPhysicalKey(DOM.shiftBtn); return; }
                if (key === 'Enter') {
                    if (input === DOM.usernameInput) {
                        DOM.passwordInput.focus();
                        showKeyboard(DOM.passwordInput);
                    } else {
                        hideKeyboard();
                        DOM.loginForm.dispatchEvent(new Event('submit', { bubbles: true }));
                    }
                    e.preventDefault();
                    return;
                }
                if (key.length === 1) {
                    const targetKey = Array.from(DOM.allKeys).find(k => k.getAttribute('data-key') === key
                    .toLowerCase());
                    if (targetKey) {
                        const char = STATE.isShifted ? key.toUpperCase() : key.toLowerCase();
                        handleKeyInput(char);
                        e.preventDefault();
                        highlightPhysicalKey(targetKey);
                    }
                }
            });

            // ─── LOGIN / LOGOUT ───
            function toggleLogoutButton(show) {
                const el = DOM.logoutApp;
                if (!el) return;
                if (show) {
                    el.classList.add('show');
                } else {
                    el.classList.remove('show');
                }
            }

            function updateIndicatorText() {
                if (DOM.indicatorText) {
                    DOM.indicatorText.textContent = STATE.isLoggedIn ?
                        '📷 Open Gallery' :
                        'Login to access your apps';
                }
            }

            function onLoginSuccess(user) {
                STATE.isLoggedIn = true;
                STATE.sessionUser = user;
                localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(user));
                showNotification('✓ Welcome, ' + user.username + '!', 'success');
                hideLoginPanel();
                toggleLogoutButton(true);
                updateIndicatorText();
                if (user && user.profile_image) {
                    setWallpaper(
                        `linear-gradient(135deg, rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('${user.profile_image}')`);
                    if (wallpaperTimer) { clearInterval(wallpaperTimer);
                        wallpaperTimer = null; }
                } else {
                    if (!wallpaperTimer) startWallpaperRotation();
                }
                toggleApps(false);
                // Open gallery automatically after login
                setTimeout(openGalleryApp, 500);
            }

            async function handleLogin(e) {
                e.preventDefault();
                if (STATE.isLoginPending) return;

                const username = DOM.usernameInput.value.trim();
                const password = DOM.passwordInput.value;
                setLoginError('');

                if (!username || !password) {
                    setLoginError('⚠ Please enter username and password.');
                    return;
                }

                STATE.isLoginPending = true;
                DOM.loginBtn.disabled = true;
                DOM.btnText.textContent = 'Wait...';
                DOM.loginSpinner.style.display = 'inline-block';

                try {
                    const formData = new FormData();
                    formData.append('username', username);
                    formData.append('password', password);

                    const response = await fetch('/virtual-phone/user/api/login_api.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        signal: AbortSignal.timeout(CONFIG.LOGIN_TIMEOUT),
                    });

                    const text = await response.text();
                    let data;
                    try { data = JSON.parse(text); } catch (_) { data = null; }

                    if (response.ok && data && data.success && data.user) {
                        onLoginSuccess(data.user);
                        return;
                    }

                    // Demo fallback
                    if (username === CONFIG.DEMO_USERNAME && password === CONFIG.DEMO_PASSWORD) {
                        onLoginSuccess({ id: 1, username: username, profile_image: null });
                        return;
                    }

                    const msg = data && data.message ? data.message : 'Invalid credentials.';
                    setLoginError('⚠ ' + msg);
                    showNotification('Login failed: ' + msg, 'error');

                } catch (err) {
                    // Offline demo fallback
                    if (username === CONFIG.DEMO_USERNAME && password === CONFIG.DEMO_PASSWORD) {
                        onLoginSuccess({ id: 1, username: username, profile_image: null });
                        showNotification('✓ Offline login (demo)', 'success');
                        return;
                    }
                    setLoginError('⚠ Connection error. Please try again.');
                    showNotification('⚠ Login failed. Check your connection.', 'error');
                } finally {
                    STATE.isLoginPending = false;
                    DOM.loginBtn.disabled = false;
                    DOM.btnText.textContent = 'Login';
                    DOM.loginSpinner.style.display = 'none';
                }
            }

            function loadCurrentUser() {
                try {
                    const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                    if (!stored) return null;
                    const user = JSON.parse(stored);
                    if (user && user.id) {
                        STATE.sessionUser = user;
                        STATE.isLoggedIn = true;
                        return user;
                    }
                    return null;
                } catch (_) { return null; }
            }

            function checkSession() {
                const user = loadCurrentUser();
                if (user) {
                    hideLoginPanel();
                    toggleLogoutButton(true);
                    updateIndicatorText();
                    if (user && user.profile_image) {
                        setWallpaper(
                            `linear-gradient(135deg, rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('${user.profile_image}')`);
                        if (wallpaperTimer) { clearInterval(wallpaperTimer);
                            wallpaperTimer = null; }
                    } else {
                        if (!wallpaperTimer) startWallpaperRotation();
                    }
                    return true;
                } else {
                    toggleLogoutButton(false);
                    showLoginPanel();
                    updateIndicatorText();
                    if (!wallpaperTimer) startWallpaperRotation();
                    return false;
                }
            }

            async function logout() {
                if (STATE.isLoggingOut) return;
                STATE.isLoggingOut = true;
                showNotification('⏳ Logging out...', 'info');

                try {
                    await fetch('/virtual-phone/user/api/logout_api.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({}),
                        signal: AbortSignal.timeout(5000),
                    });
                } catch (_) {}

                // Reset local state
                localStorage.removeItem(CONFIG.STORAGE_KEY);
                STATE.isLoggedIn = false;
                STATE.sessionUser = null;
                STATE.isLoggingOut = false;

                DOM.usernameInput.value = '';
                DOM.passwordInput.value = '';
                toggleLogoutButton(false);
                toggleApps(false);
                updateIndicatorText();
                startWallpaperRotation();
                closeGalleryApp();
                closeEditor();
                showLoginPanel();
                showNotification('✓ Logged out.', 'success');
            }

            // ─── TOUCH / SWIPE ───
            let touchStartY = 0;
            let touchStartX = 0;
            const SWIPE_THRESHOLD = 40;

            document.addEventListener('touchstart', function(e) {
                touchStartY = e.touches[0].clientY;
                touchStartX = e.touches[0].clientX;
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                const currentY = e.touches[0].clientY;
                const currentX = e.touches[0].clientX;
                const diffY = touchStartY - currentY;
                const diffX = Math.abs(touchStartX - currentX);
                if (diffX > 30) return;

                if (diffY > SWIPE_THRESHOLD) {
                    if (!STATE.isLoggedIn) {
                        showLoginPanel();
                    } else {
                        toggleApps(true);
                    }
                } else if (diffY < -SWIPE_THRESHOLD) {
                    if (STATE.isAppsOpen) toggleApps(false);
                }
            }, { passive: true });

            // ─── SCROLL INDICATOR ───
            DOM.scrollIndicator.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showLoginPanel();
                } else {
                    openGalleryApp();
                }
            });

            DOM.homeIndicator.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleApps(false);
            });

            DOM.loginHandle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showNotification('Please login to continue', 'info');
                } else {
                    hideLoginPanel();
                }
            });

            // ─── INPUT FOCUS ───
            DOM.usernameInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) showKeyboard(this);
            });
            DOM.passwordInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) showKeyboard(this);
            });

            DOM.loginForm.addEventListener('submit', handleLogin);
            DOM.signupBtn.addEventListener('click', function() {
                showNotification('Create account feature coming soon!', 'success');
            });

            // ─── LOGOUT ───
            DOM.logoutApp.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                toggleApps(false);
                logout();
            });

            // ─── CAMERA LAUNCHER ───
            DOM.cameraAppLauncher.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', 'error');
                    showLoginPanel();
                    return;
                }
                toggleApps(false);
                window.location.href = '/virtual-phone/user/camera/camera.html';
            });

            DOM.settingsAppLauncher.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', 'error');
                    showLoginPanel();
                    return;
                }
                toggleApps(false);
                showNotification('⚙️ Settings coming soon!', 'success');
            });

            // ════════════════════════════════════════════════════════════════
            //  GALLERY APP
            // ════════════════════════════════════════════════════════════════

            function openGalleryApp() {
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', 'error');
                    showLoginPanel();
                    return;
                }

                DOM.galleryAppOverlay.classList.add('show');
                DOM.clockWidget.classList.add('hidden');
                STATE.selectionMode = false;
                STATE.selectedImages = [];
                updateSelectionUI();
                loadGalleryImages();
                showNotification('📁 Gallery opened', 'success');
            }

            function closeGalleryApp() {
                sessionStorage.setItem('phoneAppJustClosed', 'true');
                window.location.assign(CONFIG.DASHBOARD_URL);
            }

            function loadNotifications() {
                let notifications = [];
                try {
                    const stored = localStorage.getItem(CONFIG.NOTIFICATIONS_KEY);
                    notifications = stored ? JSON.parse(stored) : [];
                    if (!Array.isArray(notifications)) {
                        throw new TypeError('Stored notifications must be an array.');
                    }
                } catch (error) {
                    console.error('Could not load notifications:', error);
                    notifications = [];
                    showNotification('Notifications could not be loaded.', 'error');
                }

                DOM.ncList.replaceChildren();
                DOM.notificationBadge.textContent = notifications.length > 99 ? '99+' : String(notifications.length);
                DOM.notificationBadge.hidden = notifications.length === 0;

                if (notifications.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'notification-empty';
                    empty.textContent = 'No chat notifications yet.';
                    DOM.ncList.appendChild(empty);
                    return;
                }

                [...notifications].reverse().forEach((notification) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'notification-item';

                    const title = document.createElement('strong');
                    title.textContent = notification.sender_id ? `Message from user ${notification.sender_id}` : 'Chat notification';
                    const message = document.createElement('span');
                    message.textContent = notification.message || 'Open Chat to view this notification.';
                    item.append(title, message);

                    if (notification.time) {
                        const time = document.createElement('time');
                        const timestamp = new Date(notification.time);
                        time.textContent = Number.isNaN(timestamp.getTime()) ? '' : timestamp.toLocaleString();
                        item.appendChild(time);
                    }

                    item.addEventListener('click', () => {
                        window.location.assign('/virtual-phone/user/chat_room/vphone_chat.html');
                    });
                    DOM.ncList.appendChild(item);
                });
            }

            function toggleNotificationCenter(forceOpen) {
                const open = forceOpen === undefined
                    ? !DOM.notificationCenter.classList.contains('open')
                    : forceOpen;
                DOM.notificationCenter.classList.toggle('open', open);
                DOM.notificationCenter.setAttribute('aria-hidden', String(!open));
                if (open) loadNotifications();
            }

            async function loadGalleryImages() {
                try {
                    let userId = null;
                    try {
                        const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                        if (stored) {
                            const user = JSON.parse(stored);
                            if (user && user.id) userId = user.id;
                        }
                    } catch (_) {}

                    if (!userId) {
                        DOM.galleryGridContainer.innerHTML =
                            `<div class="gallery-empty"><i class="fas fa-lock"></i>Please login to view photos</div>`;
                        return;
                    }

                    const response = await fetch(`${CONFIG.GALLERY_ENDPOINT}?user_id=${userId}&t=${Date.now()}`);
                    const data = await response.json();

                    if (data && data.success && data.images && data.images.length > 0) {
                        STATE.images = data.images;
                        renderGallery(data.images);
                    } else {
                        STATE.images = [];
                        DOM.galleryGridContainer.innerHTML =
                            `<div class="gallery-empty"><i class="fas fa-camera"></i>No photos yet.<br />Capture your first photo!</div>`;
                    }

                } catch (error) {
                    console.error('Gallery load error:', error);
                    DOM.galleryGridContainer.innerHTML =
                        `<div class="gallery-empty"><i class="fas fa-wifi"></i>Could not load photos</div>`;
                }
            }

            function renderGallery(images) {
                if (!images || images.length === 0) {
                    DOM.galleryGridContainer.innerHTML =
                        `<div class="gallery-empty"><i class="fas fa-camera"></i>No photos yet.<br />Capture your first photo!</div>`;
                    return;
                }

                let html = '';
                images.forEach((img, index) => {
                    const src = typeof img === 'string' ? img : img.url || img.path || img;
                    const isSelected = STATE.selectedImages.includes(src);
                    html += `
                                <div class="gallery-grid-item ${isSelected ? 'selected' : ''}" data-src="${src}" data-index="${index}">
                                    <img src="${src}" alt="Photo ${index+1}" loading="lazy" />
                                    <div class="item-check"><i class="fas fa-check"></i></div>
                                </div>
                            `;
                });

                DOM.galleryGridContainer.innerHTML = html;

                DOM.galleryGridContainer.querySelectorAll('.gallery-grid-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        const src = this.dataset.src;
                        const index = parseInt(this.dataset.index);

                        if (STATE.selectionMode) {
                            const idx = STATE.selectedImages.indexOf(src);
                            if (idx > -1) {
                                STATE.selectedImages.splice(idx, 1);
                                this.classList.remove('selected');
                            } else {
                                STATE.selectedImages.push(src);
                                this.classList.add('selected');
                            }
                            updateSelectionUI();
                        } else {
                            openEditor(src, index);
                        }
                    });

                    let longPressTimer = null;
                    item.addEventListener('touchstart', function(e) {
                        longPressTimer = setTimeout(() => {
                            if (!STATE.selectionMode) {
                                STATE.selectionMode = true;
                                DOM.gallerySelectToggle.innerHTML = '<i class="fas fa-times"></i>';
                                DOM.gallerySelectToggle.classList.add('active-mode');
                                const src = this.dataset.src;
                                if (!STATE.selectedImages.includes(src)) {
                                    STATE.selectedImages.push(src);
                                    this.classList.add('selected');
                                }
                                updateSelectionUI();
                                showNotification('Selection mode enabled', 'success');
                            }
                        }, 500);
                    });
                    item.addEventListener('touchend', function(e) {
                        clearTimeout(longPressTimer);
                    });
                    item.addEventListener('touchmove', function(e) {
                        clearTimeout(longPressTimer);
                    });
                });
            }

            function updateSelectionUI() {
                const count = STATE.selectedImages.length;
                const bar = DOM.selectionBar;
                const countEl = DOM.selectionCount;

                if (STATE.selectionMode || count > 0) {
                    bar.classList.add('show');
                    countEl.innerHTML = `<span>${count}</span> selected`;
                } else {
                    bar.classList.remove('show');
                }
            }

            // ─── SELECTION TOGGLE ───
            DOM.gallerySelectToggle.addEventListener('click', function() {
                STATE.selectionMode = !STATE.selectionMode;
                if (!STATE.selectionMode) {
                    STATE.selectedImages = [];
                    DOM.galleryGridContainer.querySelectorAll('.gallery-grid-item').forEach(el => {
                        el.classList.remove('selected');
                    });
                    this.innerHTML = '<i class="fas fa-check-circle"></i>';
                    this.classList.remove('active-mode');
                    updateSelectionUI();
                    showNotification('Selection mode disabled', 'info');
                } else {
                    this.innerHTML = '<i class="fas fa-times"></i>';
                    this.classList.add('active-mode');
                    showNotification('Tap photos to select', 'info');
                }
            });

            // ─── SELECT ALL ───
            DOM.selectAllBtn.addEventListener('click', function() {
                const items = DOM.galleryGridContainer.querySelectorAll('.gallery-grid-item');
                if (STATE.selectedImages.length === items.length && items.length > 0) {
                    STATE.selectedImages = [];
                    items.forEach(el => el.classList.remove('selected'));
                } else {
                    STATE.selectedImages = [];
                    items.forEach(el => {
                        const src = el.dataset.src;
                        if (src && !STATE.selectedImages.includes(src)) {
                            STATE.selectedImages.push(src);
                            el.classList.add('selected');
                        }
                    });
                }
                updateSelectionUI();
            });

            // ─── DELETE SELECTED ───
            DOM.deleteSelectedBtn.addEventListener('click', async function() {
                if (STATE.selectedImages.length === 0) return;
                if (!confirm(`Delete ${STATE.selectedImages.length} photo(s)?`)) return;

                let userId = null;
                try {
                    const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                    if (stored) {
                        const user = JSON.parse(stored);
                        if (user && user.id) userId = user.id;
                    }
                } catch (_) {}

                if (!userId) {
                    showNotification('⚠ Please login first', 'error');
                    return;
                }

                let deleted = 0;
                for (const src of STATE.selectedImages) {
                    try {
                        const formData = new FormData();
                        formData.append('user_id', userId);
                        formData.append('image_path', src);

                        const response = await fetch(CONFIG.DELETE_ENDPOINT, {
                            method: 'POST',
                            body: formData,
                        });
                        const result = await response.json();
                        if (result && result.success) deleted++;
                    } catch (_) {}
                }

                STATE.selectedImages = [];
                STATE.selectionMode = false;
                DOM.gallerySelectToggle.innerHTML = '<i class="fas fa-check-circle"></i>';
                DOM.gallerySelectToggle.classList.remove('active-mode');
                updateSelectionUI();
                showNotification(`🗑️ ${deleted} photo(s) deleted`, 'success');
                loadGalleryImages();
            });

            // ─── CLOSE GALLERY ───
            DOM.closeGalleryAppBtn.addEventListener('click', function() {
                closeGalleryApp();
            });

            // ════════════════════════════════════════════════════════════════
            //  PHOTO EDITOR  —  PROFESSIONAL
            // ════════════════════════════════════════════════════════════════

            let currentEditorImage = null;

            function openEditor(src, index) {
                STATE.currentImageIndex = index;
                STATE.currentImageSrc = src;

                STATE.brightness = 100;
                STATE.contrast = 100;
                STATE.saturation = 100;
                STATE.blur = 0;
                STATE.warmth = 100;
                STATE.currentFilter = 'none';
                STATE.rotation = 0;
                STATE.flipH = false;
                STATE.flipV = false;
                STATE.isDirty = false;
                STATE.originalImageData = null;

                DOM.brightnessSlider.value = 100;
                DOM.contrastSlider.value = 100;
                DOM.saturationSlider.value = 100;
                DOM.blurSlider.value = 0;
                DOM.warmthSlider.value = 100;
                DOM.brightnessValue.textContent = '100%';
                DOM.contrastValue.textContent = '100%';
                DOM.saturationValue.textContent = '100%';
                DOM.blurValue.textContent = '0px';
                DOM.warmthValue.textContent = '100%';

                DOM.editorTabs.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                DOM.editorTabs.querySelector('[data-tab="adjust"]').classList.add('active');
                document.querySelectorAll('#ctrlAdjust, #ctrlFilters, #ctrlRotate, #ctrlRename').forEach(el => el
                    .classList.remove('active'));
                document.getElementById('ctrlAdjust').classList.add('active');

                document.querySelectorAll('.filter-btn[data-filter]').forEach(el => {
                    el.classList.toggle('active', el.dataset.filter === 'none');
                });

                const fileName = src.split('/').pop();
                DOM.currentFileName.textContent = fileName;
                DOM.renameInput.value = fileName.replace(/\.[^.]+$/, '');

                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = function() {
                    currentEditorImage = img;
                    STATE.originalImageData = img;
                    applyEdits();
                    DOM.photoEditorOverlay.classList.add('show');
                    showNotification('📷 Photo opened for editing', 'success');
                };
                img.onerror = function() {
                    showNotification('⚠ Failed to load image', 'error');
                };
                img.src = src;
            }

            function closeEditor() {
                DOM.photoEditorOverlay.classList.remove('show');
                currentEditorImage = null;
                STATE.currentImageIndex = -1;
                STATE.currentImageSrc = null;
            }

            function applyEdits() {
                if (!currentEditorImage) return;

                const displayImg = DOM.editorImage;
                let filterStr = '';

                const brightness = STATE.brightness / 100;
                filterStr += `brightness(${brightness}) `;

                const contrast = STATE.contrast / 100;
                filterStr += `contrast(${contrast}) `;

                const saturation = STATE.saturation / 100;
                filterStr += `saturate(${saturation}) `;

                if (STATE.blur > 0) {
                    filterStr += `blur(${STATE.blur}px) `;
                }

                // Warmth via sepia + hue-rotate
                const warmthVal = STATE.warmth / 100;
                if (warmthVal !== 1) {
                    const sepiaVal = Math.max(0, (warmthVal - 0.5) * 0.5);
                    const hueVal = (warmthVal - 1) * 30;
                    if (sepiaVal > 0.01) filterStr += `sepia(${sepiaVal}) `;
                    if (Math.abs(hueVal) > 0.5) filterStr += `hue-rotate(${hueVal}deg) `;
                }

                switch (STATE.currentFilter) {
                    case 'grayscale':
                        filterStr += 'grayscale(100%) ';
                        break;
                    case 'sepia':
                        filterStr += 'sepia(100%) ';
                        break;
                    case 'invert':
                        filterStr += 'invert(100%) ';
                        break;
                    case 'vintage':
                        filterStr += 'sepia(60%) contrast(105%) brightness(92%) saturate(80%) ';
                        break;
                    case 'cool':
                        filterStr += 'sepia(20%) saturate(70%) hue-rotate(30deg) ';
                        break;
                    case 'warm':
                        filterStr += 'sepia(40%) saturate(120%) hue-rotate(-15deg) ';
                        break;
                    case 'highcontrast':
                        filterStr += 'contrast(160%) brightness(102%) saturate(140%) ';
                        break;
                    case 'noir':
                        filterStr += 'grayscale(100%) contrast(140%) brightness(90%) ';
                        break;
                    case 'chrome':
                        filterStr += 'saturate(150%) contrast(115%) brightness(105%) ';
                        break;
                    case 'fade':
                        filterStr += 'saturate(60%) contrast(90%) brightness(105%) ';
                        break;
                    case 'instant':
                        filterStr += 'sepia(30%) saturate(110%) contrast(95%) brightness(102%) ';
                        break;
                    default:
                        break;
                }

                let transformStr = '';
                if (STATE.rotation !== 0) {
                    transformStr += `rotate(${STATE.rotation}deg) `;
                }
                if (STATE.flipH) {
                    transformStr += 'scaleX(-1) ';
                }
                if (STATE.flipV) {
                    transformStr += 'scaleY(-1) ';
                }

                displayImg.style.filter = filterStr.trim() || 'none';
                displayImg.style.transform = transformStr.trim() || 'none';
                displayImg.src = currentEditorImage.src;

                STATE.isDirty = true;
            }

            // ─── EDITOR TAB SWITCHING ───
            DOM.editorTabs.addEventListener('click', function(e) {
                const tab = e.target.closest('.tab');
                if (!tab) return;

                DOM.editorTabs.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const tabName = tab.dataset.tab;
                document.querySelectorAll('#ctrlAdjust, #ctrlFilters, #ctrlRotate, #ctrlRename').forEach(el => el
                    .classList.remove('active'));
                const map = {
                    adjust: 'ctrlAdjust',
                    filters: 'ctrlFilters',
                    rotate: 'ctrlRotate',
                    rename: 'ctrlRename'
                };
                document.getElementById(map[tabName]).classList.add('active');
            });

            // ─── ADJUST SLIDERS ───
            DOM.brightnessSlider.addEventListener('input', function() {
                STATE.brightness = parseInt(this.value);
                DOM.brightnessValue.textContent = STATE.brightness + '%';
                applyEdits();
            });
            DOM.contrastSlider.addEventListener('input', function() {
                STATE.contrast = parseInt(this.value);
                DOM.contrastValue.textContent = STATE.contrast + '%';
                applyEdits();
            });
            DOM.saturationSlider.addEventListener('input', function() {
                STATE.saturation = parseInt(this.value);
                DOM.saturationValue.textContent = STATE.saturation + '%';
                applyEdits();
            });
            DOM.blurSlider.addEventListener('input', function() {
                STATE.blur = parseFloat(this.value);
                DOM.blurValue.textContent = STATE.blur + 'px';
                applyEdits();
            });
            DOM.warmthSlider.addEventListener('input', function() {
                STATE.warmth = parseInt(this.value);
                DOM.warmthValue.textContent = STATE.warmth + '%';
                applyEdits();
            });

            // ─── FILTERS ───
            document.querySelectorAll('.filter-btn[data-filter]').forEach(el => {
                el.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn[data-filter]').forEach(b => b.classList.remove(
                    'active'));
                    this.classList.add('active');
                    STATE.currentFilter = this.dataset.filter;
                    applyEdits();
                });
            });

            // ─── ROTATE / FLIP ───
            DOM.rotateLeftBtn.addEventListener('click', function() {
                STATE.rotation -= 90;
                applyEdits();
            });
            DOM.rotateRightBtn.addEventListener('click', function() {
                STATE.rotation += 90;
                applyEdits();
            });
            DOM.flipHorizontalBtn.addEventListener('click', function() {
                STATE.flipH = !STATE.flipH;
                applyEdits();
            });
            DOM.flipVerticalBtn.addEventListener('click', function() {
                STATE.flipV = !STATE.flipV;
                applyEdits();
            });
            DOM.resetTransformBtn.addEventListener('click', function() {
                STATE.rotation = 0;
                STATE.flipH = false;
                STATE.flipV = false;
                applyEdits();
                showNotification('Transform reset', 'success');
            });

            // ─── RENAME ───
            DOM.editorRenameBtn.addEventListener('click', function() {
                DOM.editorTabs.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                DOM.editorTabs.querySelector('[data-tab="rename"]').classList.add('active');
                document.querySelectorAll('#ctrlAdjust, #ctrlFilters, #ctrlRotate, #ctrlRename').forEach(el => el
                    .classList.remove('active'));
                document.getElementById('ctrlRename').classList.add('active');
                DOM.renameInput.focus();
            });

            DOM.renameConfirmBtn.addEventListener('click', async function() {
                const newName = DOM.renameInput.value.trim();
                if (!newName) {
                    showNotification('⚠ Please enter a name', 'error');
                    return;
                }

                const oldPath = STATE.currentImageSrc;
                const oldName = oldPath.split('/').pop();
                const ext = oldName.includes('.') ? oldName.substring(oldName.lastIndexOf('.')) : '.jpg';
                const newFileName = newName + ext;

                let userId = null;
                try {
                    const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                    if (stored) {
                        const user = JSON.parse(stored);
                        if (user && user.id) userId = user.id;
                    }
                } catch (_) {}

                if (!userId) {
                    showNotification('⚠ Please login first', 'error');
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('old_path', oldPath);
                    formData.append('new_name', newFileName);

                    const response = await fetch(CONFIG.RENAME_ENDPOINT, {
                        method: 'POST',
                        body: formData,
                    });
                    const result = await response.json();

                    if (result && result.success) {
                        showNotification('✅ Renamed successfully', 'success');
                        DOM.currentFileName.textContent = newFileName;
                        STATE.currentImageSrc = result.new_path || oldPath.replace(oldName, newFileName);
                        const idx = STATE.images.indexOf(oldPath);
                        if (idx > -1) {
                            STATE.images[idx] = STATE.currentImageSrc;
                        }
                        loadGalleryImages();
                        closeEditor();
                        showNotification('📁 Gallery updated', 'success');
                    } else {
                        showNotification('⚠ Rename failed: ' + (result.message || 'Unknown error'), 'error');
                    }
                } catch (error) {
                    console.error('Rename error:', error);
                    showNotification('⚠ Error renaming file', 'error');
                }
            });

            DOM.renameCancelBtn.addEventListener('click', function() {
                DOM.editorTabs.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                DOM.editorTabs.querySelector('[data-tab="adjust"]').classList.add('active');
                document.querySelectorAll('#ctrlAdjust, #ctrlFilters, #ctrlRotate, #ctrlRename').forEach(el => el
                    .classList.remove('active'));
                document.getElementById('ctrlAdjust').classList.add('active');
            });

            // ─── DELETE FROM EDITOR ───
            DOM.editorDeleteBtn.addEventListener('click', async function() {
                if (!confirm('Delete this photo?')) return;

                const src = STATE.currentImageSrc;
                let userId = null;
                try {
                    const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                    if (stored) {
                        const user = JSON.parse(stored);
                        if (user && user.id) userId = user.id;
                    }
                } catch (_) {}

                if (!userId) {
                    showNotification('⚠ Please login first', 'error');
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('image_path', src);

                    const response = await fetch(CONFIG.DELETE_ENDPOINT, {
                        method: 'POST',
                        body: formData,
                    });
                    const result = await response.json();

                    if (result && result.success) {
                        showNotification('🗑️ Photo deleted', 'success');
                        closeEditor();
                        loadGalleryImages();
                    } else {
                        showNotification('⚠ Delete failed', 'error');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    showNotification('⚠ Error deleting photo', 'error');
                }
            });

            // ─── SAVE EDITS ───
            DOM.editorSaveBtn.addEventListener('click', async function() {
                if (!STATE.isDirty) {
                    showNotification('No changes to save', 'info');
                    return;
                }

                const img = DOM.editorImage;
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                const tempImg = new Image();
                tempImg.crossOrigin = 'anonymous';
                tempImg.onload = function() {
                    canvas.width = tempImg.width;
                    canvas.height = tempImg.height;

                    const filterStr = img.style.filter || '';
                    ctx.filter = filterStr;

                    ctx.save();
                    if (STATE.rotation !== 0) {
                        ctx.translate(canvas.width / 2, canvas.height / 2);
                        ctx.rotate(STATE.rotation * Math.PI / 180);
                        ctx.translate(-canvas.width / 2, -canvas.height / 2);
                    }
                    if (STATE.flipH) {
                        ctx.translate(canvas.width, 0);
                        ctx.scale(-1, 1);
                    }
                    if (STATE.flipV) {
                        ctx.translate(0, canvas.height);
                        ctx.scale(1, -1);
                    }

                    ctx.drawImage(tempImg, 0, 0, canvas.width, canvas.height);
                    ctx.restore();

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                    uploadEditedImage(dataUrl);
                };
                tempImg.onerror = function() {
                    showNotification('⚠ Failed to process image', 'error');
                };
                tempImg.src = img.src;
            });

            async function uploadEditedImage(dataUrl) {
                let userId = null;
                try {
                    const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                    if (stored) {
                        const user = JSON.parse(stored);
                        if (user && user.id) userId = user.id;
                    }
                } catch (_) {}

                if (!userId) {
                    showNotification('⚠ Please login first', 'error');
                    return;
                }

                try {
                    const fd = new FormData();
                    fd.append('user_id', userId);
                    fd.append('image', dataUrl);

                    const response = await fetch('save.php', {
                        method: 'POST',
                        body: fd,
                    });

                    const result = await response.text();
                    console.log('Upload result:', result);

                    showNotification('✅ Edits saved!', 'success');
                    STATE.isDirty = false;
                    closeEditor();
                    loadGalleryImages();
                } catch (error) {
                    console.error('Save error:', error);
                    showNotification('⚠ Failed to save edits', 'error');
                }
            }

            // ─── EDITOR BACK ───
            DOM.editorBackBtn.addEventListener('click', function() {
                if (STATE.isDirty) {
                    if (!confirm('You have unsaved changes. Discard?')) return;
                }
                closeEditor();
            });

            // ─── ESCAPE ───
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (DOM.photoEditorOverlay.classList.contains('show')) {
                        if (STATE.isDirty) {
                            if (!confirm('You have unsaved changes. Discard?')) return;
                        }
                        closeEditor();
                    } else if (DOM.galleryAppOverlay.classList.contains('show')) {
                        closeGalleryApp();
                    }
                }
            });

            // ════════════════════════════════════════════════════════════════
            //  EXPOSE PUBLIC API
            // ════════════════════════════════════════════════════════════════

            window.vphone = window.vphone || {};
            window.vphone.logout = logout;
            window.vphone.showNotification = showNotification;
            window.vphone.getUser = function() { return STATE.sessionUser; };
            window.vphone.isLoggedIn = function() { return STATE.isLoggedIn; };
            window.vphone.openGallery = openGalleryApp;
            window.vphone.closeGallery = closeGalleryApp;
            window.vphone.loadGallery = loadGalleryImages;

            // ─── INIT ───
            function init() {
                updateClock();
                setInterval(updateClock, CONFIG.CLOCK_INTERVAL);
                loadNotifications();

                DOM.ncTriggerBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    toggleNotificationCenter();
                });
                DOM.ncHandle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    toggleNotificationCenter(false);
                });
                DOM.ncClearBtn.addEventListener('click', function() {
                    localStorage.removeItem(CONFIG.NOTIFICATIONS_KEY);
                    loadNotifications();
                });
                document.addEventListener('click', function(event) {
                    if (DOM.notificationCenter.classList.contains('open') &&
                        !DOM.notificationCenter.contains(event.target) &&
                        !DOM.ncTriggerBtn.contains(event.target)) {
                        toggleNotificationCenter(false);
                    }
                });

                const hasSession = checkSession();
                if (!hasSession) {
                    showLoginPanel();
                }

                startWallpaperRotation();

                if (STATE.isLoggedIn) {
                    setTimeout(openGalleryApp, 600);
                }

                window.addEventListener('online', () => showNotification('📶 Back online.', 'success'));
                window.addEventListener('offline', () => showNotification('📡 Disconnected.', 'error'));

                DOM.galleryAppLauncher.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (!STATE.isLoggedIn) {
                        showNotification('⚠ Please login first', 'error');
                        showLoginPanel();
                        return;
                    }
                    toggleApps(false);
                    openGalleryApp();
                });

                console.log('[Gallery Pro] Initialized.');
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

        })();
    </script>

</body>
</html>