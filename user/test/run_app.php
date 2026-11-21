<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Virtual Phone · Ingram Popup</title>
    <link rel="stylesheet" href="/virtual-phone/user/settings/theme.css" />
    <script defer src="/virtual-phone/user/settings/theme.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        /* ================================================================
               RESET & BASE
               ================================================================ */
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
            position: relative;
            padding: 10px;
        }

        /* ================================================================
               PHONE SHELL
               ================================================================ */
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
            flex-shrink: 0;
        }

        /* Side buttons */
        .phone::before {
            content: "";
            position: absolute;
            left: -5px;
            top: 130px;
            width: 5px;
            height: 50px;
            background: #2c2c2c;
            border-radius: 5px 0 0 5px;
            box-shadow: inset -1px 0 2px rgba(255, 255, 255, 0.2);
            z-index: 200;
        }

        .phone-power-btn {
            position: absolute;
            right: -5px;
            top: 170px;
            width: 5px;
            height: 75px;
            background: #2c2c2c;
            border-radius: 0 5px 5px 0;
            box-shadow: inset 1px 0 2px rgba(255, 255, 255, 0.2);
            z-index: 200;
        }

        /* ================================================================
               SCREEN
               ================================================================ */
        .screen {
            width: 100%;
            height: 100%;
            border-radius: 32px;
            overflow: hidden;
            position: relative;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            flex-direction: column;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
        }

        .screen::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 32px;
            pointer-events: none;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.02);
            z-index: 150;
        }

        /* ================================================================
               DYNAMIC ISLAND
               ================================================================ */
        .dynamic-island {
            position: absolute;
            top: 11px;
            left: 50%;
            transform: translateX(-50%);
            width: 90px;
            height: 25px;
            background: #000;
            border-radius: 20px;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            color: #fff;
            font-size: 10px;
            overflow: hidden;
            white-space: nowrap;
        }

        .dynamic-island.notify-error {
            width: 220px;
            height: 38px;
            background: #ff3b30;
            border-radius: 19px;
            box-shadow: 0 4px 15px rgba(255, 59, 48, 0.4);
            padding: 0 15px;
        }

        .dynamic-island.notify-success {
            width: 220px;
            height: 38px;
            background: #34c759;
            border-radius: 19px;
            box-shadow: 0 4px 15px rgba(52, 199, 89, 0.4);
            padding: 0 15px;
        }

        /* ================================================================
               STATUS BAR
               ================================================================ */
        .status {
            position: absolute;
            top: 14px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            z-index: 90;
            pointer-events: none;
        }

        .icons i {
            margin-left: 5px;
            font-size: 10px;
        }

        /* ================================================================
               WALLPAPER
               ================================================================ */
        .wallpaper {
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top, #3b82f6, #1d4ed8, #0f172a);
            background-size: cover;
            background-position: center;
            animation: bgMove 12s infinite alternate ease-in-out;
            z-index: 1;
            transition: background 1s ease, transform 1s ease;
        }

        @keyframes bgMove {
            0% {
                transform: scale(1) rotate(0deg);
            }
            100% {
                transform: scale(1.15) rotate(2deg);
            }
        }

        /* ================================================================
               CLOCK
               ================================================================ */
        .clock {
            position: absolute;
            top: 70px;
            width: 100%;
            text-align: center;
            color: white;
            z-index: 5;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .clock h1 {
            font-size: 42px;
            font-weight: 600;
            letter-spacing: -1px;
            line-height: 1;
        }

        .clock p {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.9;
            margin-top: 4px;
        }

        /* ================================================================
               SCROLL INDICATOR
               ================================================================ */
        .scroll-indicator {
            position: absolute;
            top: 155px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.85);
            font-size: 10px;
            font-weight: 500;
            text-align: center;
            animation: bounce 2s infinite;
            z-index: 5;
            cursor: pointer;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
        }

        .scroll-indicator i {
            display: block;
            font-size: 14px;
            margin-top: 4px;
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
                transform: translate(-50%, 6px);
            }
            60% {
                transform: translate(-50%, 3px);
            }
        }

        /* ================================================================
               APPS GRID
               ================================================================ */
        .apps {
            position: absolute;
            bottom: 60px;
            left: 0;
            right: 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            padding: 16px;
            z-index: 5;
        }

        .app {
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .app:active {
            transform: scale(0.9);
        }

        .icon {
            width: 44px;
            height: 44px;
            margin: auto;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .3);
        }

        .instagram {
            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
        }
        .whatsapp {
            background: #25D366;
        }
        .facebook {
            background: #1877F2;
        }
        .camera {
            background: linear-gradient(45deg, #434343 0%, #000000 100%);
        }
        .gallery {
            background: #ff9800;
        }
        .settings {
            background: #555;
        }
        .browser {
            background: #00BCD4;
        }
        .phoneicon {
            background: #4CAF50;
        }
        .logout-icon {
            background: #ff3b30;
        }

        .app span {
            display: block;
            margin-top: 5px;
            font-size: 9px;
            font-weight: 500;
            color: white;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
        }

        /* ================================================================
               DOCK
               ================================================================ */
        .dock {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 30px;
            z-index: 101;
            pointer-events: none;
        }

        /* ================================================================
               LOGIN PANEL (drawer)
               ================================================================ */
        .login-panel {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -430px;
            height: 420px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 28px 28px 0 0;
            box-shadow: 0 -12px 45px rgba(0, 0, 0, .35);
            border: 1px solid rgba(255, 255, 255, 0.45);
            transition: bottom 0.4s cubic-bezier(0.1, 0.76, 0.55, 0.94);
            padding: 18px 18px 22px;
            z-index: 99;
            display: flex;
            flex-direction: column;
        }

        .login-panel.show {
            bottom: 0;
        }

        .login-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 28px 28px 0 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0));
            pointer-events: none;
        }

        .handle {
            width: 40px;
            height: 5px;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            margin: 0 auto 15px;
            cursor: pointer;
        }

        .login-panel h2 {
            text-align: center;
            margin-bottom: 2px;
            font-size: 20px;
            font-weight: 700;
            color: #111;
        }

        .login-panel p {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-bottom: 18px;
        }

        .login-panel .hint {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-bottom: 14px;
        }

        .login-panel input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            margin-bottom: 12px;
            outline: none;
            font-size: 13px;
            font-weight: 500;
            background: rgba(0, 0, 0, 0.03);
            color: #111;
            transition: all 0.25s ease;
        }

        .login-panel input:focus {
            border-color: #007AFF;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15);
        }

        .login-panel button {
            padding: 12px;
            border: none;
            border-radius: 14px;
            background: #007AFF;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 8px;
            transition: opacity 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-panel button:active {
            opacity: 0.9;
            transform: scale(0.98);
        }

        .login-panel button.signup {
            background: #34C759;
        }

        .login-panel button:disabled {
            opacity: 0.6;
            cursor: default;
        }

        .spinner {
            animation: spin 1s linear infinite;
            display: none;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* ================================================================
               KEYBOARD
               ================================================================ */
        .keyboard {
            position: absolute;
            bottom: -240px;
            left: 0;
            right: 0;
            height: 210px;
            background: rgba(220, 224, 230, 0.98);
            backdrop-filter: blur(15px);
            border-radius: 18px 18px 0 0;
            padding: 8px 5px 24px;
            box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.2);
            transition: bottom 0.35s cubic-bezier(0.1, 0.76, 0.55, 0.94);
            z-index: 100;
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
            border-radius: 6px;
            height: 38px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            transition: background 0.1s, transform 0.05s;
            user-select: none;
        }

        .key:active,
        .key.physical-active {
            background: #abb4be !important;
            transform: scale(0.92);
        }

        .key.special {
            background: #acb5be;
            flex: 1.35;
            font-size: 13px;
        }

        .key.space {
            flex: 5.2;
        }

        .login-panel.keyboard-open {
            height: 460px;
        }

        /* ================================================================
               HIDE LOGOUT APP BY DEFAULT
               ================================================================ */
        #logoutApp {
            display: none;
        }
        #logoutApp.show {
            display: block;
        }

        /* ================================================================
               ─── INGRAM COVER OVERLAY ───
               (full‑screen overlay that appears when Instagram is opened)
               ================================================================ */
        .cover-overlay {
            position: fixed;
            inset: 0;
            background: rgba(8, 20, 36, 0.72);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: coverFadeIn 0.4s ease;
        }

        .cover-overlay.active {
            display: flex;
        }

        @keyframes coverFadeIn {
            0% {
                opacity: 0;
                backdrop-filter: blur(0px);
            }
            100% {
                opacity: 1;
                backdrop-filter: blur(12px);
            }
        }

        /* ── Cover Panel ── */
        .cover-panel {
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.50);
            padding: 28px 30px 26px 30px;
            max-width: 480px;
            width: 100%;
            animation: panelSlideUp 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            position: relative;
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
        }

        .cover-panel::-webkit-scrollbar {
            width: 4px;
        }
        .cover-panel::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        .cover-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1a6eff, #22c55e, #1a6eff);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%,
            100% {
                background-position: 0% 0%;
            }
            50% {
                background-position: 100% 0%;
            }
        }

        @keyframes panelSlideUp {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.96);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .cover-panel .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e9edf2;
        }

        .cover-panel .panel-header .title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cover-panel .panel-header .title-group .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #22c55e;
            display: inline-block;
            animation: pulse-dot 1.5s ease-in-out infinite;
            flex-shrink: 0;
        }

        .cover-panel .panel-header .title-group .dot.closed {
            background: #ef4444;
            animation: none;
        }

        @keyframes pulse-dot {
            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.5;
                transform: scale(0.8);
            }
        }

        .cover-panel .panel-header .title-group h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0b1e33;
            letter-spacing: -0.2px;
        }

        .cover-panel .panel-header .title-group .status-label {
            font-size: 12px;
            font-weight: 500;
            color: #15803d;
            background: #dcfce7;
            padding: 2px 12px;
            border-radius: 40px;
        }

        .cover-panel .panel-header .title-group .status-label.closed {
            color: #b91c1c;
            background: #fee2e2;
        }

        .cover-panel .panel-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ── Size Controls ── */
        .size-controls {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .size-controls .size-label {
            font-size: 13px;
            font-weight: 600;
            color: #0b1e33;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .size-controls .size-label small {
            font-weight: 400;
            color: #6b7f93;
            font-size: 11px;
        }

        .size-controls .size-presets {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .size-controls .size-presets button {
            padding: 5px 12px;
            border: 2px solid #e9edf2;
            border-radius: 40px;
            background: #fff;
            color: #4a5c6e;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .size-controls .size-presets button:hover {
            border-color: #1a6eff;
            color: #1a6eff;
            transform: translateY(-1px);
        }

        .size-controls .size-presets button.active {
            border-color: #1a6eff;
            background: #e0edff;
            color: #0d5ce0;
        }

        .size-controls .size-presets button .dim {
            font-weight: 400;
            color: #8a9aa8;
            font-size: 10px;
        }

        .size-controls .size-presets button.active .dim {
            color: #1a6eff;
        }

        .size-controls .custom-size-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 2px;
        }

        .size-controls .custom-size-row label {
            font-size: 12px;
            color: #4a5c6e;
            font-weight: 500;
        }

        .size-controls .custom-size-row input[type="number"] {
            width: 60px;
            padding: 5px 8px;
            border: 2px solid #e9edf2;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            transition: 0.2s ease;
            background: #f8fafc;
            color: #0b1e33;
        }

        .size-controls .custom-size-row input[type="number"]:focus {
            outline: none;
            border-color: #1a6eff;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(26, 110, 255, 0.10);
        }

        .size-controls .custom-size-row .dim-label {
            font-size: 12px;
            color: #6b7f93;
        }

        .size-controls .custom-size-row .apply-btn {
            padding: 5px 14px;
            border: 2px solid #1a6eff;
            border-radius: 40px;
            background: #e0edff;
            color: #0d5ce0;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .size-controls .custom-size-row .apply-btn:active {
            transform: scale(0.95);
        }

        /* ── Action Buttons ── */
        .cover-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 2px;
        }

        .cover-actions button {
            flex: 1;
            min-width: 80px;
            padding: 10px 14px;
            border: none;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .cover-actions .btn-focus {
            background: #1a6eff;
            color: #fff;
            box-shadow: 0 4px 12px rgba(26, 110, 255, 0.25);
        }
        .cover-actions .btn-focus:hover {
            background: #0d5ce0;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 110, 255, 0.35);
        }

        .cover-actions .btn-close {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }
        .cover-actions .btn-close:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35);
        }

        .cover-actions .btn-back {
            background: #22c55e;
            color: #fff;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.30);
        }
        .cover-actions .btn-back:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.40);
        }

        .cover-actions .btn-back:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .cover-close-x {
            position: absolute;
            top: 14px;
            right: 16px;
            background: transparent;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #8a9aa8;
            transition: 0.2s ease;
            line-height: 1;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .cover-close-x:hover {
            color: #0b1e33;
            background: #f0f4f8;
        }

        .cover-panel .panel-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            padding-top: 10px;
            border-top: 1px solid #e9edf2;
        }

        .cover-panel .panel-footer .text-xs {
            font-size: 11px;
            color: #8a9aa8;
        }

        /* ================================================================
               TOAST (for Instagram popup)
               ================================================================ */
        .toast-global {
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            background: #0b1e33;
            color: #fff;
            padding: 10px 24px;
            border-radius: 60px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            z-index: 99999;
            opacity: 0;
            transition: opacity 0.4s ease, transform 0.4s ease;
            pointer-events: none;
            white-space: nowrap;
        }

        .toast-global.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* ================================================================
               RESPONSIVE
               ================================================================ */
        @media (max-width: 420px) {
            .phone {
                width: 100%;
                height: 100vh;
                border-radius: 0;
                padding: 0;
                box-shadow: none;
            }
            .phone::before,
            .phone-power-btn {
                display: none;
            }
            .screen {
                border-radius: 0;
            }
            .screen::after {
                border-radius: 0;
            }
            .cover-panel {
                padding: 20px 16px 18px 16px;
                margin: 6px;
                border-radius: 24px;
                max-height: 95vh;
            }
            .cover-panel .panel-header .title-group h2 {
                font-size: 16px;
            }
            .cover-actions button {
                min-width: 60px;
                font-size: 12px;
                padding: 8px 10px;
            }
            .size-controls .size-presets button {
                font-size: 11px;
                padding: 4px 10px;
            }
            .cover-close-x {
                top: 10px;
                right: 12px;
                font-size: 20px;
            }
            .toast-global {
                font-size: 12px;
                padding: 8px 16px;
                white-space: normal;
                max-width: 90%;
                text-align: center;
                top: 16px;
            }
            .clock h1 {
                font-size: 34px;
            }
            .apps {
                gap: 8px;
                padding: 12px;
                bottom: 50px;
            }
            .icon {
                width: 38px;
                height: 38px;
                font-size: 15px;
            }
            .app span {
                font-size: 8px;
            }
            .dynamic-island.notify-error,
            .dynamic-island.notify-success {
                width: 180px;
                height: 32px;
                font-size: 9px;
                padding: 0 12px;
            }
            .cover-overlay {
                padding: 10px;
            }
        }

        @media (max-width: 360px) {
            .cover-panel .panel-header .title-group .status-label {
                display: none;
            }
            .size-controls .custom-size-row input[type="number"] {
                width: 50px;
            }
            .cover-actions button {
                min-width: 50px;
                font-size: 11px;
                padding: 6px 8px;
            }
        }
    </style>
</head>

<body>

    <!-- ================================================================
    TOAST (global)
    ================================================================ -->
    <div class="toast-global" id="toastGlobal"></div>

    <!-- ================================================================
    PHONE SHELL
    ================================================================ -->
    <div class="phone">
        <div class="phone-power-btn"></div>
        <div class="screen">

            <!-- Wallpaper -->
            <div class="wallpaper" id="wallpaper"></div>

            <!-- Dynamic Island -->
            <div class="dynamic-island" id="dynamicIsland"></div>

            <!-- Status Bar -->
            <div class="status">
                <div class="time" id="time">12:00</div>
                <div class="icons">
                    <i class="fas fa-signal"></i>
                    <i class="fas fa-wifi"></i>
                    <i class="fas fa-battery-full"></i>
                </div>
            </div>

            <!-- Clock -->
            <div class="clock">
                <h1 id="clock">12:00</h1>
                <p id="date">Tuesday, July 14</p>
            </div>

            <!-- Scroll Indicator -->
            <div class="scroll-indicator" id="scrollIndicator">
                Swipe up or Scroll for Login
                <i class="fas fa-chevron-down"></i>
            </div>

            <!-- Apps -->
            <div class="apps">
                <!-- Instagram → triggers the popup + cover -->
                <div class="app" id="instagramApp">
                    <div class="icon instagram"><i class="fab fa-instagram"></i></div>
                    <span>Instagram</span>
                </div>
                <div class="app"><div class="icon whatsapp"><i class="fab fa-whatsapp"></i></div><span>WhatsApp</span></div>
                <div class="app"><div class="icon facebook"><i class="fab fa-facebook-f"></i></div><span>Facebook</span></div>
                <div class="app"><div class="icon browser"><i class="fas fa-globe"></i></div><span>Browser</span></div>
                <div class="app"><div class="icon gallery"><i class="fas fa-image"></i></div><span>Gallery</span></div>
                <div class="app"><div class="icon camera"><i class="fas fa-camera"></i></div><span>Camera</span></div>
                <div class="app"><div class="icon settings"><i class="fas fa-cog"></i></div><span>Settings</span></div>
                <div class="app" id="profileApp"><div class="icon phoneicon"><i class="fas fa-phone"></i></div><span>Phone</span></div>
                <!-- Logout -->
                <div class="app" id="logoutApp"><div class="icon logout-icon"><i class="fas fa-power-off"></i></div><span>Logout</span></div>
            </div>

            <div class="dock"></div>

            <!-- Login Panel -->
            <div id="loginPanel" class="login-panel">
                <div class="handle"></div>
                <h2>Welcome</h2>
                <p>Sign in to your Virtual Phone</p>
                <form id="loginForm" onsubmit="return false;">
                    <input type="text" id="usernameInput" name="username" placeholder="Username" inputmode="none" autocomplete="off" required>
                    <input type="password" id="passwordInput" name="password" placeholder="Password" inputmode="none" required>
                    <button type="submit" id="loginBtn">
                        <i class="fas fa-spinner spinner" id="loginSpinner"></i>
                        <span id="btnText">Login</span>
                    </button>
                    <button type="button" class="signup">Create Account</button>
                </form>
            </div>

            <!-- Keyboard -->
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

        </div>
    </div>

    <!-- ================================================================
    COVER OVERLAY (Ingram popup controls)
    ================================================================ -->
    <div class="cover-overlay" id="coverOverlay">

        <div class="cover-panel">

            <!-- Close X -->
            <button class="cover-close-x" id="coverCloseX" title="Close popup &amp; return">✕</button>

            <!-- Panel Header -->
            <div class="panel-header">
                <div class="title-group">
                    <span class="dot" id="coverDot"></span>
                    <h2>📸 Ingram</h2>
                    <span class="status-label" id="coverStatusLabel">● Open</span>
                </div>
            </div>

            <!-- Panel Body -->
            <div class="panel-body">

                <!-- SIZE CONTROLS -->
                <div class="size-controls">
                    <div class="size-label">
                        📐 Popup size
                        <small>— preset or custom</small>
                    </div>

                    <div class="size-presets" id="sizePresets">
                        <button data-width="360" data-height="600" class="active">
                            Small <span class="dim">360×600</span>
                        </button>
                        <button data-width="420" data-height="700">
                            Medium <span class="dim">420×700</span>
                        </button>
                        <button data-width="520" data-height="800">
                            Large <span class="dim">520×800</span>
                        </button>
                        <button data-width="640" data-height="900">
                            XL <span class="dim">640×900</span>
                        </button>
                    </div>

                    <div class="custom-size-row">
                        <label for="customWidth">Custom:</label>
                        <input type="number" id="customWidth" value="480" min="300" max="1200" step="10" />
                        <span class="dim-label">×</span>
                        <input type="number" id="customHeight" value="750" min="400" max="1400" step="10" />
                        <span class="dim-label">px</span>
                        <button class="apply-btn" id="applyCustomSizeBtn">Apply</button>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="cover-actions">
                    <button class="btn-focus" id="coverFocusBtn">🔍 Bring to front</button>
                    <button class="btn-close" id="coverCloseBtn">✕ Close popup</button>
                    <button class="btn-back" id="coverBackBtn">↩️ Back</button>
                </div>

                <!-- Footer -->
                <div class="panel-footer">
                    <span class="text-xs" id="coverPopupUrl">instagram.com</span>
                    <span class="text-xs" id="coverSizeDisplay">420 × 700</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ================================================================
    JAVASCRIPT — Merged: Virtual Phone + Ingram Popup
    ================================================================ -->
    <script>
        (function() {
            'use strict';

            // ================================================================
            // ─── PART 1: VIRTUAL PHONE ───
            // ================================================================

            // ---- DOM refs ----
            const DOM = {
                clock: document.getElementById('clock'),
                time: document.getElementById('time'),
                date: document.getElementById('date'),
                wallpaper: document.getElementById('wallpaper'),
                loginPanel: document.getElementById('loginPanel'),
                scrollIndicator: document.getElementById('scrollIndicator'),
                keyboard: document.getElementById('keyboard'),
                usernameInput: document.getElementById('usernameInput'),
                passwordInput: document.getElementById('passwordInput'),
                loginForm: document.getElementById('loginForm'),
                loginBtn: document.getElementById('loginBtn'),
                btnText: document.getElementById('btnText'),
                loginSpinner: document.getElementById('loginSpinner'),
                dynamicIsland: document.getElementById('dynamicIsland'),
                profileApp: document.querySelector('#profileApp'),
                profileIcon: document.querySelector('#profileApp .icon'),
                profileName: document.querySelector('#profileApp span'),
                logoutApp: document.getElementById('logoutApp'),
                allKeys: document.querySelectorAll('.key'),
                row1: document.getElementById('row-1'),
                row2: document.getElementById('row-2'),
                row3: document.getElementById('row-3'),
                shiftBtn: document.getElementById('shift-btn'),
                backspaceBtn: document.getElementById('backspace-btn'),
                spaceBtn: document.getElementById('space-btn'),
                modeBtn: document.getElementById('mode-btn')
            };

            // ---- State ----
            const STATE = {
                isShifted: false,
                isNumMode: false,
                activeInput: null,
                isLoggedIn: false,
                isLoginPending: false,
                isLoggingOut: false,
                wallpaperIndex: 0,
                sessionUser: null
            };

            // ---- Constants ----
            const CONFIG = {
                WALLPAPER_INTERVAL: 9000,
                CLOCK_INTERVAL: 1000,
                NOTIFICATION_DURATION: 3000,
                LOGIN_TIMEOUT: 15000,
                SWIPE_THRESHOLD: 50,
                SCROLL_THRESHOLD: 15,
                STORAGE_KEY: 'vphone_user'
            };

            const WALLPAPERS = [
                "linear-gradient(135deg, rgba(3,37,65,0.9), rgba(10,78,120,0.9)), url('https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=900&q=80')",
                "linear-gradient(135deg, rgba(35,23,68,0.9), rgba(98,0,234,0.8)), url('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80')",
                "linear-gradient(135deg, rgba(7,46,74,0.9), rgba(6,101,166,0.85)), url('https://images.unsplash.com/photo-1499696017737-6d9c6015f3ea?auto=format&fit=crop&w=900&q=80')"
            ];

            const KEYS_ALPHABET = ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k',
                'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm'
            ];
            const KEYS_NUMBERS = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '/', ':', ';', '(', '7', '&', '@',
                '"', '.', ',', '?', '!', "'", '_'
            ];

            // ---- Helpers ----
            function safeParseJSON(text) {
                try { return JSON.parse(text); } catch (_) { return null; }
            }

            function isOnline() {
                return navigator.onLine !== false;
            }

            // ---- Notification ----
            function showNotification(message, isSuccess = false) {
                const island = DOM.dynamicIsland;
                if (!island) return;
                island.textContent = message;
                island.className = 'dynamic-island ' + (isSuccess ? 'notify-success' : 'notify-error');
                clearTimeout(island._hideTimer);
                island._hideTimer = setTimeout(() => {
                    island.className = 'dynamic-island';
                    island.textContent = '';
                }, CONFIG.NOTIFICATION_DURATION);
            }

            // ---- Clock ----
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

            // ---- Wallpaper ----
            function rotateWallpaper() {
                const el = DOM.wallpaper;
                if (!el) return;
                el.style.background = WALLPAPERS[STATE.wallpaperIndex];
                el.style.transform = 'scale(1)';
                requestAnimationFrame(() => { el.style.transform = 'scale(1.02)'; });
                STATE.wallpaperIndex = (STATE.wallpaperIndex + 1) % WALLPAPERS.length;
            }

            // ---- Login Panel ----
            function showLoginPanel() {
                if (STATE.isLoggedIn) return;
                DOM.loginPanel.classList.add('show');
                if (DOM.scrollIndicator) DOM.scrollIndicator.style.display = 'none';
            }

            function hideLoginPanel() {
                DOM.loginPanel.classList.remove('show');
                hideKeyboard();
                if (DOM.scrollIndicator) DOM.scrollIndicator.style.display = 'block';
            }

            let touchStartY = 0;
            function onTouchStart(e) { touchStartY = e.touches[0].clientY; }
            function onTouchMove(e) {
                const currentY = e.touches[0].clientY;
                if (touchStartY - currentY > CONFIG.SWIPE_THRESHOLD) showLoginPanel();
            }
            function onWheel(e) {
                if (e.deltaY > CONFIG.SCROLL_THRESHOLD) showLoginPanel();
            }
            function onDocumentClick(e) {
                const panel = DOM.loginPanel;
                const indicator = DOM.scrollIndicator;
                if (panel && !panel.contains(e.target) && indicator !== e.target) hideLoginPanel();
            }

            // ---- Keyboard ----
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
                if (STATE.activeInput) { STATE.activeInput.blur();
                    STATE.activeInput = null; }
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
                        break;
                    case '123':
                    case 'ABC':
                        toggleMode();
                        break;
                    case '▼':
                        hideKeyboard();
                        break;
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
                    const keys = row.querySelectorAll('.key');
                    keys.forEach(key => {
                        if (key.id === 'shift-btn' || key.id === 'backspace-btn' || key.id === 'mode-btn')
                            return;
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

            function onPhysicalKeyDown(e) {
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
                    if (input === DOM.usernameInput) { DOM.passwordInput.focus();
                        showKeyboard(DOM.passwordInput); } else { hideKeyboard();
                        DOM.loginForm.dispatchEvent(new Event('submit', { bubbles: true })); }
                    e.preventDefault();
                    return;
                }
                if (key.length === 1) {
                    const targetKey = Array.from(DOM.allKeys).find(k => k.getAttribute('data-key') === key.toLowerCase());
                    if (targetKey) {
                        const char = STATE.isShifted ? key.toUpperCase() : key.toLowerCase();
                        handleKeyInput(char);
                        e.preventDefault();
                        highlightPhysicalKey(targetKey);
                    }
                }
            }

            function highlightPhysicalKey(el) {
                if (!el) return;
                el.classList.add('physical-active');
                clearTimeout(el._phyTimer);
                el._phyTimer = setTimeout(() => { el.classList.remove('physical-active'); }, 100);
            }

            // ---- API helpers ----
            async function apiRequest(endpoint, options = {}) {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), CONFIG.LOGIN_TIMEOUT);
                try {
                    const response = await fetch(endpoint, {
                        ...options,
                        signal: controller.signal,
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', ...(options.headers || {}) }
                    });
                    clearTimeout(timeoutId);
                    if (!response.ok) throw new Error('HTTP_ERROR_' + response.status);
                    const text = await response.text();
                    const data = safeParseJSON(text);
                    if (data === null) throw new Error('INVALID_JSON');
                    return data;
                } catch (error) {
                    clearTimeout(timeoutId);
                    if (error.name === 'AbortError') throw new Error('TIMEOUT');
                    if (error.message === 'INVALID_JSON') throw new Error('INVALID_JSON');
                    if (error.message.startsWith('HTTP_ERROR_')) throw new Error('HTTP_ERROR');
                    if (!isOnline()) throw new Error('OFFLINE');
                    throw new Error('CONNECTION_ERROR');
                }
            }

            // ---- Login ----
            async function handleLogin(e) {
                e.preventDefault();
                if (STATE.isLoginPending) return;
                const username = DOM.usernameInput.value.trim();
                const password = DOM.passwordInput.value;
                if (!username || !password) { showNotification('⚠ Username and password are required.', false); return; }
                if (!isOnline()) { showNotification('⚠ No internet connection. Please try again.', false); return; }

                STATE.isLoginPending = true;
                DOM.loginBtn.disabled = true;
                DOM.btnText.textContent = 'Signing in...';
                DOM.loginSpinner.style.display = 'inline-block';

                try {
                    const formData = new FormData();
                    formData.append('username', username);
                    formData.append('password', password);
                    const data = await apiRequest('/virtual-phone/user/api/login_api.php', {
                        method: 'POST',
                        body: formData
                    });
                    if (data.success && data.user) {
                        STATE.isLoggedIn = true;
                        STATE.sessionUser = data.user;
                        localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(data.user));
                        showNotification('✓ Welcome, ' + data.user.username + '!', true);
                        updateProfileUI(data.user);
                        hideLoginPanel();
                        toggleLogoutButton(true);
                        setTimeout(() => {
                            window.location.href = '/virtual-phone/user_phone_dashboard.';
                        }, 800);
                    } else {
                        showNotification('⚠ ' + (data.message || 'Login failed. Please try again.'), false);
                    }
                } catch (error) {
                    let message = '⚠ Connection error. Please try again.';
                    if (error.message === 'OFFLINE') message = '⚠ No internet connection. Please check your network.';
                    else if (error.message === 'TIMEOUT') message = '⚠ Request timed out. Please try again.';
                    else if (error.message === 'HTTP_ERROR') message = '⚠ Server error. Please try again later.';
                    else if (error.message === 'INVALID_JSON') message = '⚠ Server response error. Please contact support.';
                    showNotification(message, false);
                    console.error('[Login Error]', error);
                } finally {
                    STATE.isLoginPending = false;
                    DOM.loginBtn.disabled = false;
                    DOM.btnText.textContent = 'Login';
                    DOM.loginSpinner.style.display = 'none';
                }
            }

            // ---- Session & Logout ----
            function loadCurrentUser() {
                try {
                    const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                    if (!stored) return null;
                    const user = JSON.parse(stored);
                    if (user && user.id) { STATE.sessionUser = user;
                        STATE.isLoggedIn = true; return user; }
                    return null;
                } catch (_) { return null; }
            }

            function updateProfileUI(user) {
                if (!user) return;
                if (DOM.profileIcon) {
                    if (user.profile_image) {
                        DOM.profileIcon.style.backgroundImage = 'url(' + user.profile_image + ')';
                        DOM.profileIcon.style.backgroundSize = 'cover';
                        DOM.profileIcon.style.backgroundPosition = 'center';
                        DOM.profileIcon.textContent = '';
                    } else {
                        DOM.profileIcon.style.backgroundImage = '';
                        DOM.profileIcon.textContent = user.username.charAt(0).toUpperCase();
                    }
                }
                if (DOM.profileName) DOM.profileName.textContent = user.username;
            }

            function toggleLogoutButton(show) {
                if (DOM.logoutApp) {
                    if (show) DOM.logoutApp.classList.add('show');
                    else DOM.logoutApp.classList.remove('show');
                }
            }

            function checkSession() {
                const user = loadCurrentUser();
                if (user) { updateProfileUI(user);
                    hideLoginPanel();
                    toggleLogoutButton(true); return true; } else { toggleLogoutButton(false); return false; }
            }

            function resetUIAfterLogout() {
                localStorage.removeItem(CONFIG.STORAGE_KEY);
                STATE.isLoggedIn = false;
                STATE.sessionUser = null;
                if (DOM.profileIcon) { DOM.profileIcon.style.backgroundImage = '';
                    DOM.profileIcon.textContent = '👤'; }
                if (DOM.profileName) DOM.profileName.textContent = 'Profile';
                DOM.usernameInput.value = '';
                DOM.passwordInput.value = '';
                toggleLogoutButton(false);
            }

            async function logout() {
                if (STATE.isLoggingOut) return;
                STATE.isLoggingOut = true;
                showNotification('⏳ Logging out...', false);
                try {
                    const data = await apiRequest('/api/logout_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({})
                    });
                    if (data && data.success === true) showNotification('✓ Logged out successfully.', true);
                    else showNotification('⚠ ' + (data.message || 'Logout response error.'), false);
                } catch (error) {
                    let message = '⚠ Logout failed. You have been logged out locally.';
                    if (error.message === 'OFFLINE') message = '⚠ You are offline. Logged out locally.';
                    else if (error.message === 'HTTP_ERROR') message = '⚠ Server error during logout. Logged out locally.';
                    else if (error.message === 'TIMEOUT') message = '⚠ Logout request timed out. Logged out locally.';
                    showNotification(message, false);
                    console.error('[Logout API Error]', error);
                } finally {
                    resetUIAfterLogout();
                    STATE.isLoggingOut = false;
                    showLoginPanel();
                }
            }

            // ---- Network ----
            function onOnlineStatus() {
                if (isOnline()) showNotification('📶 Back online.', true);
                else showNotification('📡 You are offline. Some features may be unavailable.', false);
            }


            // ================================================================
            // ─── PART 2: INGRAM POPUP ───
            // ================================================================

            // ---- DOM refs for popup ----
            const coverOverlay = document.getElementById('coverOverlay');
            const coverDot = document.getElementById('coverDot');
            const coverStatusLabel = document.getElementById('coverStatusLabel');
            const coverPopupUrl = document.getElementById('coverPopupUrl');
            const coverSizeDisplay = document.getElementById('coverSizeDisplay');
            const coverFocusBtn = document.getElementById('coverFocusBtn');
            const coverCloseBtn = document.getElementById('coverCloseBtn');
            const coverBackBtn = document.getElementById('coverBackBtn');
            const coverCloseX = document.getElementById('coverCloseX');
            const sizePresets = document.getElementById('sizePresets');
            const customWidth = document.getElementById('customWidth');
            const customHeight = document.getElementById('customHeight');
            const applyCustomSizeBtn = document.getElementById('applyCustomSizeBtn');
            const instagramApp = document.getElementById('instagramApp');
            const toastGlobal = document.getElementById('toastGlobal');

            // ---- Popup state ----
            const TARGET_URL = 'https://www.instagram.com/';
            let popupWindow = null;
            let monitorInterval = null;
            let toastTimeout = null;
            let currentWidth = 420;
            let currentHeight = 700;

            // ---- Toast (global) ----
            function showToast(message, icon = '✅', duration = 2500) {
                if (toastTimeout) { clearTimeout(toastTimeout);
                    toastGlobal.classList.remove('show'); }
                toastGlobal.innerHTML = `<span style="margin-right:8px;">${icon}</span> ${message}`;
                toastGlobal.classList.add('show');
                toastTimeout = setTimeout(() => {
                    toastGlobal.classList.remove('show');
                    toastTimeout = null;
                }, duration);
            }

            // ---- Update cover UI ----
            function updateCoverUI() {
                const isOpen = popupWindow && !popupWindow.closed;
                if (!isOpen) {
                    coverOverlay.classList.remove('active');
                    coverDot.className = 'dot closed';
                    coverStatusLabel.textContent = '● Closed';
                    coverStatusLabel.className = 'status-label closed';
                    coverBackBtn.disabled = true;
                    if (monitorInterval) { clearInterval(monitorInterval);
                        monitorInterval = null; }
                } else {
                    coverOverlay.classList.add('active');
                    coverDot.className = 'dot';
                    coverStatusLabel.textContent = '● Open';
                    coverStatusLabel.className = 'status-label';
                    coverBackBtn.disabled = false;
                    coverPopupUrl.textContent = TARGET_URL.replace(/^https?:\/\//, '');
                    coverSizeDisplay.textContent = `${currentWidth} × ${currentHeight}`;
                    if (!monitorInterval) {
                        monitorInterval = setInterval(() => {
                            if (popupWindow && popupWindow.closed) {
                                updateCoverUI();
                                showToast('Popup was closed', '🔒', 2000);
                            }
                        }, 800);
                    }
                }
            }

            // ---- Open popup ----
            function openPopup() {
                if (popupWindow && !popupWindow.closed) {
                    popupWindow.focus();
                    showToast('Popup already open — brought to front', '🔍', 1500);
                    updateCoverUI();
                    return;
                }
                if (popupWindow) { popupWindow = null; }

                const w = currentWidth;
                const h = currentHeight;
                const left = Math.max(0, (window.screen.availWidth - w) / 2);
                const top = Math.max(0, (window.screen.availHeight - h) / 2);
                const features =
                    `width=${w},height=${h},left=${left},top=${top},resizable=yes,scrollbars=yes,status=no,location=yes,toolbar=no,menubar=no`;

                popupWindow = window.open(TARGET_URL, 'InstagramLatchPopup', features);

                if (!popupWindow) {
                    showToast('Popup blocked! Please allow popups for this site.', '❌', 3000);
                    return;
                }

                setTimeout(() => {
                    updateCoverUI();
                    showToast('Instagram opened in popup', '📸', 1800);
                }, 300);
            }

            // ---- Close popup & return ----
            function closePopupAndReturn() {
                if (popupWindow && !popupWindow.closed) {
                    popupWindow.close();
                    showToast('Popup closed — welcome back!', '🏠', 2000);
                } else {
                    showToast('Popup was already closed', '👋', 1500);
                }
                popupWindow = null;
                setTimeout(() => { updateCoverUI(); }, 100);
                window.focus();
            }

            // ---- Close popup only ----
            function closePopupOnly() {
                if (popupWindow && !popupWindow.closed) {
                    popupWindow.close();
                    showToast('Popup closed', '✕', 1500);
                } else {
                    showToast('Popup was already closed', '👋', 1500);
                }
                popupWindow = null;
                setTimeout(() => { updateCoverUI(); }, 100);
            }

            // ---- Focus popup ----
            function focusPopup() {
                if (popupWindow && !popupWindow.closed) {
                    popupWindow.focus();
                    showToast('Brought popup to front', '🔍', 1500);
                } else {
                    showToast('Popup is closed — reopening...', '📸', 1500);
                    openPopup();
                }
            }

            // ---- Resize popup ----
            function resizePopup(width, height) {
                currentWidth = Math.max(280, Math.min(1400, width));
                currentHeight = Math.max(350, Math.min(1600, height));
                coverSizeDisplay.textContent = `${currentWidth} × ${currentHeight}`;

                if (popupWindow && !popupWindow.closed) {
                    popupWindow.close();
                    popupWindow = null;
                    setTimeout(() => {
                        openPopup();
                        showToast(`Resized to ${currentWidth}×${currentHeight}`, '📐', 1500);
                    }, 200);
                } else {
                    showToast(`Size set to ${currentWidth}×${currentHeight}`, '📐', 1500);
                    updateCoverUI();
                }

                document.querySelectorAll('#sizePresets button').forEach(btn => {
                    const w = parseInt(btn.dataset.width);
                    const h = parseInt(btn.dataset.height);
                    btn.classList.toggle('active', w === currentWidth && h === currentHeight);
                });
            }


            // ================================================================
            // ─── EVENT BINDING ───
            // ================================================================

            // ---- Virtual phone events ----
            document.addEventListener('touchstart', onTouchStart, { passive: true });
            document.addEventListener('touchmove', onTouchMove, { passive: true });
            document.addEventListener('wheel', onWheel, { passive: true });
            document.addEventListener('click', onDocumentClick);

            if (DOM.scrollIndicator) {
                DOM.scrollIndicator.addEventListener('click', function(e) { e.stopPropagation();
                    showLoginPanel(); });
            }
            if (DOM.loginPanel) {
                DOM.loginPanel.addEventListener('click', function(e) { e.stopPropagation(); });
            }

            DOM.usernameInput.addEventListener('click', function(e) { e.stopPropagation();
                showKeyboard(this); });
            DOM.passwordInput.addEventListener('click', function(e) { e.stopPropagation();
                showKeyboard(this); });

            DOM.allKeys.forEach(key => { key.addEventListener('click', onKeyClick); });
            DOM.keyboard.addEventListener('click', function(e) { e.stopPropagation(); });
            document.addEventListener('keydown', onPhysicalKeyDown);
            DOM.loginForm.addEventListener('submit', handleLogin);

            window.addEventListener('online', onOnlineStatus);
            window.addEventListener('offline', onOnlineStatus);

            // Logout elements
            const logoutElements = document.querySelectorAll('#logoutBtn, .logout-btn, [data-action="logout"]');
            logoutElements.forEach(el => { el.addEventListener('click', function(e) { e.preventDefault();
                    logout(); }); });
            if (DOM.logoutApp) {
                DOM.logoutApp.addEventListener('click', function(e) { e.stopPropagation();
                    logout(); });
            }

            // ---- Ingram popup events ----
            instagramApp.addEventListener('click', function(e) {
                e.stopPropagation();
                openPopup();
            });

            coverFocusBtn.addEventListener('click', focusPopup);
            coverCloseBtn.addEventListener('click', closePopupOnly);
            coverBackBtn.addEventListener('click', closePopupAndReturn);
            coverCloseX.addEventListener('click', closePopupAndReturn);

            sizePresets.addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const w = parseInt(btn.dataset.width);
                const h = parseInt(btn.dataset.height);
                if (w && h) {
                    customWidth.value = w;
                    customHeight.value = h;
                    resizePopup(w, h);
                }
            });

            applyCustomSizeBtn.addEventListener('click', function() {
                const w = parseInt(customWidth.value);
                const h = parseInt(customHeight.value);
                if (w >= 280 && w <= 1400 && h >= 350 && h <= 1600) {
                    resizePopup(w, h);
                } else {
                    showToast('Please enter valid dimensions (280‑1400 × 350‑1600)', '⚠️', 2500);
                }
            });

            customWidth.addEventListener('keydown', function(e) { if (e.key === 'Enter') applyCustomSizeBtn.click(); });
            customHeight.addEventListener('keydown', function(e) { if (e.key === 'Enter') applyCustomSizeBtn.click(); });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && coverOverlay.classList.contains('active')) {
                    closePopupAndReturn();
                }
            });

            coverOverlay.addEventListener('click', function(e) {
                if (e.target === coverOverlay) closePopupAndReturn();
            });

            // ================================================================
            // ─── INIT ───
            // ================================================================

            // Clock
            updateClock();
            setInterval(updateClock, CONFIG.CLOCK_INTERVAL);

            // Wallpaper
            rotateWallpaper();
            setInterval(rotateWallpaper, CONFIG.WALLPAPER_INTERVAL);

            // Session
            checkSession();

            // Popup defaults
            customWidth.value = currentWidth;
            customHeight.value = currentHeight;
            document.querySelectorAll('#sizePresets button').forEach(btn => {
                const w = parseInt(btn.dataset.width);
                const h = parseInt(btn.dataset.height);
                if (w === currentWidth && h === currentHeight) btn.classList.add('active');
            });

            updateCoverUI();

            // Expose public API
            window.vphone = window.vphone || {};
            window.vphone.logout = logout;
            window.vphone.showNotification = showNotification;
            window.vphone.getUser = function() { return STATE.sessionUser; };
            window.vphone.isLoggedIn = function() { return STATE.isLoggedIn; };
            window.vphone.openInstagram = openPopup;

            console.log('📱 Virtual Phone + Ingram Popup ready.');
            console.log('🔗 Instagram target: ' + TARGET_URL);
            console.log('📐 Default popup size: ' + currentWidth + '×' + currentHeight);
            console.log('💡 Tap the Instagram app to open the popup with cover controls.');

        })();
    </script>

</body>
</html>