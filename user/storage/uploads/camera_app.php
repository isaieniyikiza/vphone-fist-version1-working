<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Virtual Phone - Pro Camera</title>
    <link rel="stylesheet" href="/virtual-phone/user/settings/theme.css" />
    <script defer src="/virtual-phone/user/settings/theme.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <style>
        /* ─── RESET & BASE ─── */
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

        /* ─── PHONE SHELL ─── */
        .phone {
            width: 320px;
            height: 520px;
            background: linear-gradient(180deg, #060606 0%, #121212 100%);
            border-radius: 48px;
            padding: 12px;
            position: relative;
            box-shadow: 0 28px 70px -20px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.1), inset 0 0 0 1px rgba(255, 255,
                    255, 0.05);
            user-select: none;
            overflow: hidden;
        }

        .phone-power-btn {
            position: absolute;
            right: -4px;
            top: 140px;
            width: 4px;
            height: 60px;
            background: #2c2c2c;
            border-radius: 0 5px 5px 0;
        }

        /* ─── SCREEN ─── */
        .screen {
            width: 100%;
            height: 100%;
            border-radius: 36px;
            overflow: hidden;
            position: relative;
            background: #000;
            display: flex;
            flex-direction: column;
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.9);
        }

        /* ─── DYNAMIC ISLAND ─── */
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
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            color: #fff;
            font-size: 10px;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }
        .dynamic-island.notify-error {
            width: 220px;
            height: 38px;
            background: #ff3b30;
            border-radius: 19px;
            box-shadow: 0 4px 15px rgba(255, 59, 48, 0.4);
            padding: 0 15px;
            pointer-events: auto;
        }
        .dynamic-island.notify-success {
            width: 220px;
            height: 38px;
            background: #34c759;
            border-radius: 19px;
            box-shadow: 0 4px 15px rgba(52, 199, 89, 0.4);
            padding: 0 15px;
            pointer-events: auto;
        }

        /* ─── STATUS BAR ─── */
        .status {
            position: absolute;
            top: 12px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            z-index: 900;
        }
        .status .time {
            pointer-events: none;
        }
        .status .icons {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .status .icons i {
            font-size: 10px;
        }

        /* ─── WALLPAPER ─── */
        .wallpaper {
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top, #1f1a3a, #0f172a, #020617);
            background-size: cover;
            background-position: center;
            z-index: 1;
            transition: background 0.8s ease;
        }

        /* ─── CLOCK ─── */
        .clock {
            position: absolute;
            top: 60px;
            width: 100%;
            text-align: center;
            color: white;
            z-index: 5;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
            pointer-events: none;
            transition: opacity 0.5s ease;
        }
        .clock h1 {
            font-size: 46px;
            font-weight: 500;
            letter-spacing: -1px;
            line-height: 1;
        }
        .clock p {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.9;
            margin-top: 4px;
        }
        .clock.hidden {
            opacity: 0;
        }

        /* ─── SCROLL INDICATOR ─── */
        .scroll-indicator {
            position: absolute;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.95);
            font-size: 11px;
            font-weight: 600;
            text-align: center;
            animation: bounce 2s infinite;
            z-index: 50;
            cursor: pointer;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.7);
            background: rgba(0, 0, 0, 0.4);
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            transition: opacity 0.3s ease;
        }
        .scroll-indicator.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .scroll-indicator i {
            display: block;
            font-size: 13px;
            margin-top: 3px;
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

        /* ─── APPS CONTAINER ─── */
        .apps-container {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 400px;
            z-index: 10;
            padding: 20px 16px 40px;
            overflow-y: auto;
            transform: translateY(100%);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.1, 0.76, 0.55, 0.94), opacity 0.3s ease;
            pointer-events: none;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 32px 32px 0 0;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.5);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
            gap: 16px 8px;
            padding-bottom: 20px;
        }
        .app {
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .app:active {
            transform: scale(0.9);
        }
        .icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 6px;
            border-radius: 13px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: 0.25s ease;
        }
        .app:hover .icon {
            transform: scale(1.1);
        }
        .app span {
            display: block;
            font-size: 10px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            margin-top: 4px;
        }

        /* App Colors */
        .camera {
            background: linear-gradient(45deg, #434343 0%, #000000 100%);
        }
        .gallery {
            background: #ff9800;
        }
        .settings {
            background: #555;
        }
        .logout-icon {
            background: #ff3b30;
        }

        /* ─── DOCK ─── */
        .dock {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 30px;
            z-index: 1001;
            cursor: pointer;
        }

        /* ===== CAMERA OVERLAY ===== */
        .camera-overlay {
            position: absolute;
            inset: 0;
            z-index: 120;
            display: none;
            background: #000;
            border-radius: 36px;
            overflow: hidden;
        }
        .camera-overlay.show {
            display: block;
        }

        #cameraContainer {
            width: 100%;
            height: 100%;
            position: relative;
            background: #000;
            overflow: hidden;
        }

        #cameraApp {
            width: 100%;
            height: 100%;
            position: relative;
            background: #000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #cameraVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #111;
            display: block;
            position: absolute;
            top: 0;
            left: 0;
        }

        #cameraCanvas {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Preview */
        #cameraPreview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 20;
            flex-direction: column;
        }
        #cameraPreview img {
            max-width: 100%;
            max-height: 80%;
            object-fit: contain;
        }
        .preview-actions {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 0 20px;
            z-index: 25;
        }
        .preview-actions button {
            padding: 12px 28px;
            border: none;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s;
            min-width: 100px;
        }
        .preview-actions button:active {
            transform: scale(0.92);
        }
        .preview-actions .btn-retake {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .preview-actions .btn-save {
            background: #34c759;
            color: #fff;
            box-shadow: 0 4px 20px rgba(52, 199, 89, 0.3);
        }
        .preview-actions .btn-save:disabled {
            opacity: 0.5;
            cursor: default;
        }

        /* Loading */
        .camera-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            font-size: 13px;
            z-index: 2;
            text-align: center;
            background: rgba(0, 0, 0, 0.6);
            padding: 24px 32px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .camera-loading i {
            display: block;
            font-size: 36px;
            margin-bottom: 10px;
            animation: spin 1.2s linear infinite;
            color: #34c759;
        }
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Status badge */
        .camera-status {
            position: absolute;
            top: 50px;
            left: 12px;
            z-index: 10;
            color: rgba(255, 255, 255, 0.8);
            font-size: 10px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .camera-status .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
            background: #34c759;
            animation: pulse-dot 1.8s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.4;
                transform: scale(0.7);
            }
        }

        /* Top controls */
        .top-controls {
            position: absolute;
            top: 50px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 12px;
            z-index: 10;
            pointer-events: none;
        }
        .top-controls>* {
            pointer-events: auto;
        }
        .top-left,
        .top-right {
            display: flex;
            gap: 8px;
        }
        .ctrl-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            transition: transform 0.15s, background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .ctrl-btn:active {
            transform: scale(0.88);
        }
        .ctrl-btn.active {
            background: rgba(52, 199, 89, 0.3);
            border-color: #34c759;
        }

        /* Bottom controls */
        .bottom-controls {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            padding: 0 20px;
            z-index: 10;
            pointer-events: none;
        }
        .bottom-controls>* {
            pointer-events: auto;
        }

        .capture-btn {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        }
        .capture-btn:active {
            transform: scale(0.88);
        }
        .capture-btn .inner-ring {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .capture-btn .inner-ring .dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
        }

        .side-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            transition: transform 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .side-btn:active {
            transform: scale(0.88);
        }

        /* Gallery thumb */
        .gallery-thumb {
            position: absolute;
            bottom: 40px;
            left: 20px;
            z-index: 10;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            border: 2px solid rgba(255, 255, 255, 0.06);
            overflow: hidden;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            transition: all 0.2s;
            pointer-events: auto;
        }
        .gallery-thumb:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }
        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Zoom indicator */
        .zoom-indicator {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            padding: 10px 4px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        .zoom-indicator.show {
            opacity: 1;
            pointer-events: auto;
        }
        .zoom-indicator .zoom-label {
            color: rgba(255, 255, 255, 0.5);
            font-size: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .zoom-indicator .zoom-value {
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }
        .zoom-indicator .zoom-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 4px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 2px;
            outline: none;
        }
        .zoom-indicator .zoom-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        /* Flash indicator */
        .flash-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 15;
            display: none;
            pointer-events: none;
            box-shadow: 0 0 60px rgba(255, 255, 255, 0.8);
            animation: flashAnim 0.3s ease-out;
        }
        @keyframes flashAnim {
            0% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 1;
            }
            100% {
                transform: translate(-50%, -50%) scale(2);
                opacity: 0;
            }
        }

        /* Grid overlay */
        .grid-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 3;
            display: none;
        }
        .grid-overlay.show {
            display: block;
        }
        .grid-overlay .h-line,
        .grid-overlay .v-line {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
        }
        .grid-overlay .h-line {
            left: 0;
            right: 0;
            height: 1px;
        }
        .grid-overlay .h-line:nth-child(1) {
            top: 33.33%;
        }
        .grid-overlay .h-line:nth-child(2) {
            top: 66.66%;
        }
        .grid-overlay .v-line {
            top: 0;
            bottom: 0;
            width: 1px;
        }
        .grid-overlay .v-line:nth-child(3) {
            left: 33.33%;
        }
        .grid-overlay .v-line:nth-child(4) {
            left: 66.66%;
        }

        /* Settings panel */
        .settings-panel {
            position: absolute;
            bottom: -450px;
            left: 0;
            right: 0;
            height: 380px;
            background: rgba(28, 28, 30, 0.95);
            backdrop-filter: blur(30px);
            border-radius: 24px 24px 0 0;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 -20px 60px rgba(0, 0, 0, 0.6);
            transition: bottom 0.4s cubic-bezier(0.32, 0.72, 0, 1);
            z-index: 30;
            padding: 16px 18px 24px;
            overflow-y: auto;
        }
        .settings-panel.open {
            bottom: 0;
        }
        .settings-panel::-webkit-scrollbar {
            width: 0;
            background: transparent;
        }
        .settings-panel .handle {
            width: 36px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            margin: 0 auto 14px;
            cursor: pointer;
        }
        .settings-panel h3 {
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 14px;
            letter-spacing: -0.2px;
        }
        .setting-group {
            margin-bottom: 14px;
        }
        .setting-group label {
            color: rgba(255, 255, 255, 0.4);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            display: block;
            margin-bottom: 6px;
        }
        .setting-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .setting-option {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 6px 14px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .setting-option:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        .setting-option.active {
            background: rgba(52, 199, 89, 0.2);
            border-color: #34c759;
            color: #fff;
        }

        .exposure-slider-container {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 2px 0;
        }
        .exposure-slider-container input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            flex: 1;
            height: 3px;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.15);
            outline: none;
        }
        .exposure-slider-container input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            cursor: pointer;
        }
        .exposure-value {
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }

        /* Toast */
        .toast {
            position: absolute;
            top: 95px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(12px);
            color: #fff;
            padding: 8px 18px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: 500;
            z-index: 50;
            opacity: 0;
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none;
            border: 1px solid rgba(255, 255, 255, 0.04);
            text-align: center;
            max-width: 80%;
            white-space: nowrap;
        }
        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        .toast.success {
            border-color: rgba(52, 199, 89, 0.2);
        }
        .toast.error {
            border-color: rgba(255, 59, 48, 0.2);
        }

        /* Zoom badge */
        .zoom-label-badge {
            position: absolute;
            bottom: 110px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            padding: 4px 14px;
            border-radius: 20px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            z-index: 6;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .zoom-label-badge.show {
            opacity: 1;
        }

        /* ─── FULL GALLERY OVERLAY ─── */
        .gallery-overlay {
            position: absolute;
            inset: 0;
            z-index: 130;
            background: rgba(0, 0, 0, 0.92);
            backdrop-filter: blur(20px);
            border-radius: 36px;
            display: none;
            flex-direction: column;
            padding: 16px;
            overflow: hidden;
        }
        .gallery-overlay.show {
            display: flex;
        }

        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            padding: 8px 4px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            flex-shrink: 0;
        }
        .gallery-header h3 {
            font-size: 16px;
            font-weight: 600;
        }
        .gallery-header button {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .gallery-header button:hover {
            background: rgba(255, 59, 48, 0.3);
        }

        .gallery-grid {
            flex: 1;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding-top: 12px;
            align-content: start;
        }
        .gallery-grid::-webkit-scrollbar {
            width: 3px;
        }
        .gallery-grid::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .gallery-item {
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.04);
            transition: transform 0.2s, border-color 0.2s;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gallery-item:hover {
            transform: scale(1.02);
            border-color: rgba(255, 255, 255, 0.15);
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-item .placeholder-icon {
            color: rgba(255, 255, 255, 0.15);
            font-size: 28px;
        }
        .gallery-item .delete-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(255, 59, 48, 0.8);
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            color: #fff;
            font-size: 10px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .gallery-item:hover .delete-btn {
            display: flex;
        }
        .gallery-item .delete-btn:hover {
            background: #ff3b30;
        }

        .gallery-empty {
            grid-column: 1 / -1;
            text-align: center;
            color: rgba(255, 255, 255, 0.3);
            padding: 60px 0;
            font-size: 14px;
        }
        .gallery-empty i {
            display: block;
            font-size: 48px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        /* ─── LOGIN OVERLAY ─── */
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

        /* ─── LOGIN PANEL ─── */
        .login-panel {
            position: absolute;
            left: 0;
            right: 0;
            bottom: -430px;
            height: 420px;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 28px 28px 0 0;
            box-shadow: 0 -12px 45px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.45);
            transition: bottom 0.4s cubic-bezier(0.1, 0.76, 0.55, 0.94);
            padding: 18px 18px 22px;
            z-index: 999;
            display: flex;
            flex-direction: column;
        }
        .login-panel.show {
            bottom: 0;
        }
        .login-panel .handle {
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity 0.2s;
        }
        .login-panel button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .login-panel button.signup {
            background: #34C759;
        }
        .spinner {
            animation: spin 1s linear infinite;
            display: none;
        }
        .login-error {
            color: #ff3b30;
            font-size: 11px;
            text-align: center;
            margin-top: -6px;
            margin-bottom: 8px;
            min-height: 18px;
            font-weight: 500;
        }

        /* ─── KEYBOARD ─── */
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
            transition: background 0.1s, transform 0.1s;
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

        /* ─── RESPONSIVE ─── */
        @media (max-width: 360px) {
            .phone {
                width: 290px;
                height: 480px;
                border-radius: 40px;
                padding: 10px;
            }
            .screen {
                border-radius: 30px;
            }
            .clock h1 {
                font-size: 38px;
            }
            .icon {
                width: 42px;
                height: 42px;
                font-size: 17px;
                border-radius: 11px;
            }
            .app span {
                font-size: 9px;
            }
            .apps-grid {
                gap: 12px 6px;
            }
            .login-panel {
                height: 380px;
                padding: 14px 14px 18px;
            }
            .login-panel h2 {
                font-size: 17px;
            }
            .login-panel input {
                padding: 10px 14px;
                font-size: 12px;
            }
            .login-panel button {
                padding: 10px;
                font-size: 12px;
            }
            .capture-btn {
                width: 56px;
                height: 56px;
            }
            .capture-btn .inner-ring {
                width: 44px;
                height: 44px;
            }
            .capture-btn .inner-ring .dot {
                width: 17px;
                height: 17px;
            }
            .side-btn {
                width: 34px;
                height: 34px;
                font-size: 13px;
            }
            .bottom-controls {
                gap: 20px;
                bottom: 24px;
            }
            .gallery-thumb {
                width: 38px;
                height: 38px;
                bottom: 32px;
                left: 14px;
            }
            .camera-status {
                top: 44px;
                left: 10px;
                font-size: 9px;
                padding: 3px 10px;
            }
            .top-controls {
                top: 44px;
                padding: 0 8px;
            }
            .ctrl-btn {
                width: 30px;
                height: 30px;
                font-size: 11px;
            }
            .zoom-indicator {
                right: 6px;
                padding: 8px 3px;
            }
            .zoom-indicator .zoom-slider {
                height: 80px;
            }
        }

        @media (max-width: 400px) {
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
            .camera-overlay {
                border-radius: 0;
            }
            .gallery-overlay {
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
                top: 55px;
            }
            .clock h1 {
                font-size: 38px;
            }
            .apps-container {
                height: 380px;
                padding: 16px 12px 30px;
            }
            .icon {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }
            .apps-grid {
                gap: 14px 6px;
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
                </div>
            </div>

            <!-- Clock -->
            <div class="clock" id="clockWidget">
                <h1 id="clock">12:00</h1>
                <p id="date">Tuesday, July 14</p>
            </div>

            <!-- Scroll Indicator -->
            <div class="scroll-indicator" id="scrollIndicator">
                <span id="indicatorText">📷 Camera ready</span>
                <i class="fas fa-chevron-up"></i>
            </div>

            <!-- Login Overlay -->
            <div class="login-overlay active" id="loginOverlay"></div>

            <!-- Apps Container -->
            <div class="apps-container" id="appsContainer">
                <div class="apps-grid" id="appsGrid">
                    <div class="app" id="cameraAppLauncher">
                        <div class="icon camera"><i class="fas fa-camera"></i></div>
                        <span>Camera</span>
                    </div>
                    <div class="app" id="galleryAppLauncher">
                        <div class="icon gallery"><i class="fas fa-image"></i></div>
                        <span>Gallery</span>
                    </div>
                    <div class="app" id="settingsAppLauncher">
                        <div class="icon settings"><i class="fas fa-cog"></i></div>
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

            <!-- ===== CAMERA OVERLAY ===== -->
            <div class="camera-overlay" id="cameraOverlay">
                <div id="cameraContainer">
                    <div id="cameraApp">
                        <!-- Loading -->
                        <div class="camera-loading" id="cameraLoading">
                            <i class="fas fa-camera"></i>
                            <span>Initializing camera...</span>
                        </div>

                        <!-- Video -->
                        <video id="cameraVideo" autoplay playsinline muted></video>
                        <canvas id="cameraCanvas"></canvas>

                        <!-- Flash effect -->
                        <div class="flash-indicator" id="flashIndicator"></div>

                        <!-- Grid -->
                        <div class="grid-overlay" id="gridOverlay">
                            <div class="h-line"></div>
                            <div class="h-line"></div>
                            <div class="v-line"></div>
                            <div class="v-line"></div>
                        </div>

                        <!-- Status -->
                        <div class="camera-status" id="cameraStatus">
                            <span class="dot"></span>
                            <span id="statusText">Live</span>
                        </div>

                        <!-- Zoom badge -->
                        <div class="zoom-label-badge" id="zoomBadge">1.0×</div>

                        <!-- Top Controls -->
                        <div class="top-controls">
                            <div class="top-left">
                                <button class="ctrl-btn" id="flashToggleBtn" title="Flash">
                                    <i class="fas fa-bolt"></i>
                                </button>
                                <button class="ctrl-btn" id="gridToggleBtn" title="Grid">
                                    <i class="fas fa-th"></i>
                                </button>
                            </div>
                            <div class="top-right">
                                <button class="ctrl-btn" id="settingsToggleBtn" title="Settings">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                                <button class="ctrl-btn" id="closeCameraBtn" title="Close Camera">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Zoom Indicator -->
                        <div class="zoom-indicator" id="zoomIndicator">
                            <span class="zoom-label">ZOOM</span>
                            <span class="zoom-value" id="zoomValue">1.0×</span>
                            <input type="range" class="zoom-slider" id="zoomSlider" min="1" max="8" step="0.1" value="1">
                        </div>

                        <!-- Bottom Controls -->
                        <div class="bottom-controls">
                            <button class="side-btn" id="switchCameraBtn" title="Switch Camera">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="capture-btn" id="captureBtn">
                                <span class="inner-ring"><span class="dot"></span></span>
                            </button>
                            <button class="side-btn" id="timerBtn" title="Timer">
                                <i class="fas fa-clock"></i>
                            </button>
                        </div>

                        <!-- Gallery Thumb -->
                        <div class="gallery-thumb" id="galleryThumb">
                            <span class="placeholder-icon"><i class="fas fa-image"></i></span>
                        </div>

                        <!-- Preview Overlay -->
                        <div id="cameraPreview">
                            <img id="previewImage" alt="Captured">
                            <div class="preview-actions">
                                <button class="btn-retake" id="retakeBtn"><i class="fas fa-undo"></i> Retake</button>
                                <button class="btn-save" id="savePhotoBtn"><i class="fas fa-check"></i> Save</button>
                            </div>
                        </div>

                        <!-- Settings Panel -->
                        <div class="settings-panel" id="settingsPanel">
                            <div class="handle" id="settingsHandle"></div>
                            <h3><i class="fas fa-sliders-h" style="margin-right:8px;"></i>Camera Settings</h3>
                            <div class="setting-group">
                                <label>Aspect Ratio</label>
                                <div class="setting-row" id="aspectGroup">
                                    <span class="setting-option active" data-aspect="4:3">4:3</span>
                                    <span class="setting-option" data-aspect="16:9">16:9</span>
                                    <span class="setting-option" data-aspect="1:1">1:1</span>
                                </div>
                            </div>
                            <div class="setting-group">
                                <label>Exposure (EV)</label>
                                <div class="exposure-slider-container">
                                    <span style="color:rgba(255,255,255,0.3);font-size:11px;">-2</span>
                                    <input type="range" id="exposureSlider" min="-2" max="2" step="0.3" value="0">
                                    <span style="color:rgba(255,255,255,0.3);font-size:11px;">+2</span>
                                    <span class="exposure-value" id="exposureValue">0.0</span>
                                </div>
                            </div>
                            <div class="setting-group">
                                <label>White Balance</label>
                                <div class="setting-row" id="wbGroup">
                                    <span class="setting-option active" data-wb="auto">Auto</span>
                                    <span class="setting-option" data-wb="daylight">☀️</span>
                                    <span class="setting-option" data-wb="cloudy">☁️</span>
                                    <span class="setting-option" data-wb="incandescent">💡</span>
                                    <span class="setting-option" data-wb="fluorescent">💡</span>
                                </div>
                            </div>
                            <div class="setting-group">
                                <label>Self-Timer</label>
                                <div class="setting-row" id="timerGroup">
                                    <span class="setting-option active" data-timer="0">Off</span>
                                    <span class="setting-option" data-timer="3">3s</span>
                                    <span class="setting-option" data-timer="5">5s</span>
                                    <span class="setting-option" data-timer="10">10s</span>
                                </div>
                            </div>
                            <div class="setting-group">
                                <label>Resolution</label>
                                <div class="setting-row" id="resolutionGroup">
                                    <span class="setting-option active" data-res="hd">HD</span>
                                    <span class="setting-option" data-res="fhd">FHD</span>
                                    <span class="setting-option" data-res="4k">4K</span>
                                </div>
                            </div>
                        </div>

                        <!-- Toast -->
                        <div class="toast" id="toast"></div>
                    </div>
                </div>
            </div>

            <!-- ===== FULL GALLERY OVERLAY ===== -->
            <div class="gallery-overlay" id="galleryOverlay">
                <div class="gallery-header">
                    <h3><i class="fas fa-images" style="margin-right:8px;"></i>My Photos</h3>
                    <button id="closeGalleryBtn"><i class="fas fa-times"></i></button>
                </div>
                <div class="gallery-grid" id="galleryGrid">
                    <div class="gallery-empty">
                        <i class="fas fa-camera"></i>
                        No photos yet.<br>Capture your first photo!
                    </div>
                </div>
            </div>

            <!-- Login Panel -->
            <div id="loginPanel" class="login-panel">
                <div class="handle" id="loginHandle"></div>
                <h2>Welcome</h2>
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

        </div><!-- /screen -->
    </div><!-- /phone -->

    <script>
        /**
         * =====================================================================
         *  VIRTUAL PHONE WITH PRO CAMERA + GALLERY
         *  - Auto-opens camera on page load
         *  - Full camera controls (zoom, flash, grid, settings)
         *  - Gallery shows all captured images from the folder
         *  - Gallery thumb opens full gallery
         *  - Close button redirects to dashboard
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

                // Camera
                cameraOverlay: $('cameraOverlay'),
                cameraContainer: $('cameraContainer'),
                cameraVideo: $('cameraVideo'),
                cameraCanvas: $('cameraCanvas'),
                cameraLoading: $('cameraLoading'),
                cameraStatus: $('cameraStatus'),
                statusText: $('statusText'),
                captureBtn: $('captureBtn'),
                retakeBtn: $('retakeBtn'),
                savePhotoBtn: $('savePhotoBtn'),
                closeCameraBtn: $('closeCameraBtn'),
                switchCameraBtn: $('switchCameraBtn'),
                flashToggleBtn: $('flashToggleBtn'),
                gridToggleBtn: $('gridToggleBtn'),
                settingsToggleBtn: $('settingsToggleBtn'),
                settingsPanel: $('settingsPanel'),
                settingsHandle: $('settingsHandle'),
                gridOverlay: $('gridOverlay'),
                flashIndicator: $('flashIndicator'),
                zoomSlider: $('zoomSlider'),
                zoomValue: $('zoomValue'),
                zoomBadge: $('zoomBadge'),
                zoomIndicator: $('zoomIndicator'),
                exposureSlider: $('exposureSlider'),
                exposureValue: $('exposureValue'),
                galleryThumb: $('galleryThumb'),
                toast: $('toast'),
                preview: $('cameraPreview'),
                previewImage: $('previewImage'),
                cameraAppLauncher: $('cameraAppLauncher'),
                galleryAppLauncher: $('galleryAppLauncher'),
                settingsAppLauncher: $('settingsAppLauncher'),

                // Gallery
                galleryOverlay: $('galleryOverlay'),
                galleryGrid: $('galleryGrid'),
                closeGalleryBtn: $('closeGalleryBtn'),
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

                // Camera
                stream: null,
                ready: false,
                capturing: false,
                facingMode: 'environment',
                flashOn: false,
                gridOn: false,
                timerSeconds: 0,
                aspect: '4:3',
                exposure: 0,
                wb: 'auto',
                resolution: 'hd',
                zoom: 1,
                capturedImageData: null,
                lastSavedImage: null,
                isPreview: false,
                settingsOpen: false,
                timerActive: false,
                isCameraOpen: false,
            };

            const CONFIG = {
                CLOCK_INTERVAL: 1000,
                NOTIFICATION_DURATION: 3000,
                LOGIN_TIMEOUT: 15000,
                STORAGE_KEY: 'vphone_user',
                UPLOAD_ENDPOINT: 'save.php',
                GALLERY_ENDPOINT: 'get_images.php',
                DEMO_USERNAME: 'admin',
                DEMO_PASSWORD: 'admin',
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

            function showToast(msg, type = '') {
                if (!DOM.toast) return;
                DOM.toast.textContent = msg;
                DOM.toast.className = 'toast ' + type + ' show';
                clearTimeout(DOM.toast._timer);
                DOM.toast._timer = setTimeout(() => { DOM.toast.classList.remove('show'); }, 2500);
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
                if (DOM.logoutApp) DOM.logoutApp.classList.toggle('show', show);
            }

            function updateIndicatorText() {
                if (DOM.indicatorText) {
                    DOM.indicatorText.textContent = STATE.isLoggedIn ?
                        '📷 Tap to open camera' :
                        'Login to access your apps';
                }
            }

            function onLoginSuccess(user) {
                STATE.isLoggedIn = true;
                STATE.sessionUser = user;
                localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(user));
                showNotification('✓ Welcome, ' + user.username + '!', true);
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
                // Auto-open camera after login
                setTimeout(openCameraApp, 500);
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
                    showNotification('Login failed: ' + msg, false);

                } catch (err) {
                    if (username === CONFIG.DEMO_USERNAME && password === CONFIG.DEMO_PASSWORD) {
                        onLoginSuccess({ id: 1, username: username, profile_image: null });
                        showNotification('✓ Offline login (demo)', true);
                        return;
                    }
                    setLoginError('⚠ Connection error. Please try again.');
                    showNotification('⚠ Login failed. Check your connection.', false);
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
                showNotification('⏳ Logging out...', false);

                try {
                    await fetch('/virtual-phone/user/api/logout_api.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({}),
                        signal: AbortSignal.timeout(5000),
                    });
                } catch (_) {}

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
                closeCameraApp();
                closeGallery();
                showLoginPanel();
                showNotification('✓ Logged out.', true);
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
                    openCameraApp();
                }
            });

            DOM.homeIndicator.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleApps(false);
            });

            DOM.loginHandle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showNotification('Please login to continue', false);
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
                showNotification('Create account feature coming soon!', true);
            });

            // ─── LOGOUT ───
            DOM.logoutApp.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                toggleApps(false);
                logout();
            });

            // ================================================================
            //  CAMERA ENGINE
            // ================================================================

            let cameraVideo = null,
                cameraCanvas = null,
                cameraStream = null,
                cameraReady = false;

            function buildCameraUI() {
                // UI is already built in HTML, just get refs
                cameraVideo = DOM.cameraVideo;
                cameraCanvas = DOM.cameraCanvas;
            }

            async function startCamera(facing = 'environment') {
                try {
                    if (STATE.stream) {
                        STATE.stream.getTracks().forEach(t => t.stop());
                        STATE.stream = null;
                    }

                    if (DOM.cameraLoading) DOM.cameraLoading.style.display = 'block';
                    if (DOM.statusText) DOM.statusText.textContent = 'Starting...';

                    let width = 1280,
                        height = 720;
                    if (STATE.resolution === 'fhd') { width = 1920;
                        height = 1080; }
                    if (STATE.resolution === '4k') { width = 3840;
                        height = 2160; }

                    let aspectRatio = 4 / 3;
                    if (STATE.aspect === '16:9') aspectRatio = 16 / 9;
                    if (STATE.aspect === '1:1') aspectRatio = 1;

                    const constraints = {
                        video: {
                            facingMode: facing,
                            width: { ideal: width },
                            height: { ideal: height },
                            aspectRatio: { ideal: aspectRatio },
                        },
                        audio: false,
                    };

                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    STATE.stream = stream;
                    cameraVideo.srcObject = stream;

                    await new Promise((resolve) => {
                        cameraVideo.onloadedmetadata = () => {
                            cameraVideo.play();
                            resolve();
                        };
                        if (cameraVideo.readyState >= 3) resolve();
                    });

                    cameraCanvas.width = cameraVideo.videoWidth || 640;
                    cameraCanvas.height = cameraVideo.videoHeight || 480;

                    if (DOM.cameraLoading) DOM.cameraLoading.style.display = 'none';
                    if (DOM.statusText) DOM.statusText.textContent = 'Live';
                    STATE.ready = true;
                    STATE.facingMode = facing;
                    cameraReady = true;

                    applyZoom();
                    showToast('📷 Camera ready', 'success');

                } catch (err) {
                    console.error('Camera error:', err);
                    if (DOM.cameraLoading) {
                        DOM.cameraLoading.innerHTML =
                            `<i class="fas fa-exclamation-triangle" style="color:#ff6b6b;"></i><span style="color:#ff6b6b;">Camera unavailable</span><span style="font-size:11px;display:block;margin-top:6px;color:#aaa;">${err.message}</span>`;
                    }
                    if (DOM.statusText) DOM.statusText.textContent = 'Error';
                    showToast('⚠ Camera access denied', 'error');
                }
            }

            function applyZoom() {
                if (!STATE.stream) return;
                const track = STATE.stream.getVideoTracks()[0];
                if (track && track.getCapabilities && track.getCapabilities().zoom) {
                    const cap = track.getCapabilities();
                    const min = cap.zoom.min || 1;
                    const max = cap.zoom.max || 8;
                    const val = Math.min(Math.max(STATE.zoom, min), max);
                    track.applyConstraints({ advanced: [{ zoom: val }] }).catch(() => {});
                }
            }

            // ─── CAPTURE ───
            function capturePhoto() {
                if (!STATE.ready || !cameraVideo || cameraVideo.readyState !== 4) {
                    showToast('⚠ Wait for camera', 'error');
                    return;
                }
                if (STATE.capturing) return;
                if (STATE.timerActive) return;

                if (STATE.timerSeconds > 0) {
                    STATE.timerActive = true;
                    let count = STATE.timerSeconds;
                    showToast(`⏱ ${count}s`, '');
                    if (DOM.statusText) DOM.statusText.textContent = `${count}s`;
                    const interval = setInterval(() => {
                        count--;
                        if (count <= 0) {
                            clearInterval(interval);
                            STATE.timerActive = false;
                            if (DOM.statusText) DOM.statusText.textContent = 'Live';
                            doCapture();
                        } else {
                            showToast(`⏱ ${count}s`, '');
                            if (DOM.statusText) DOM.statusText.textContent = `${count}s`;
                        }
                    }, 1000);
                    return;
                }
                doCapture();
            }

            function doCapture() {
                if (STATE.capturing) return;
                STATE.capturing = true;

                const ctx = cameraCanvas.getContext('2d');
                const w = cameraVideo.videoWidth || 640;
                const h = cameraVideo.videoHeight || 480;
                cameraCanvas.width = w;
                cameraCanvas.height = h;
                ctx.drawImage(cameraVideo, 0, 0, w, h);

                const imageData = cameraCanvas.toDataURL('image/jpeg', 0.95);
                STATE.capturedImageData = imageData;

                if (DOM.previewImage) DOM.previewImage.src = imageData;
                if (DOM.preview) DOM.preview.style.display = 'flex';
                STATE.isPreview = true;
                if (DOM.statusText) DOM.statusText.textContent = 'Captured';

                if (DOM.flashIndicator) {
                    DOM.flashIndicator.style.display = 'block';
                    setTimeout(() => { DOM.flashIndicator.style.display = 'none'; }, 300);
                }

                cameraVideo.pause();
                STATE.capturing = false;
                showToast('📸 Photo captured!', 'success');
            }

            // ─── RETAKE ───
            function retakePhoto() {
                if (DOM.preview) DOM.preview.style.display = 'none';
                STATE.isPreview = false;
                STATE.capturedImageData = null;
                cameraVideo.play();
                if (DOM.statusText) DOM.statusText.textContent = 'Live';
                showToast('🔄 Retake', '');
            }

            // ─── SAVE PHOTO ───
            async function savePhoto() {
                if (!STATE.capturedImageData) {
                    showToast('⚠ No photo to save', 'error');
                    return;
                }

                let userId = null;
                try {
                    const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
                    if (stored) {
                        const user = JSON.parse(stored);
                        if (user && user.id) userId = user.id;
                    }
                } catch (_) {}

                if (!userId) {
                    showToast('⚠ Please login first', 'error');
                    return;
                }

                if (DOM.savePhotoBtn) {
                    DOM.savePhotoBtn.disabled = true;
                    DOM.savePhotoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                }

                try {
                    const fd = new FormData();
                    fd.append('user_id', userId);
                    fd.append('image', STATE.capturedImageData);

                    const response = await fetch(CONFIG.UPLOAD_ENDPOINT, {
                        method: 'POST',
                        body: fd,
                    });

                    const result = await response.text();
                    console.log('Upload result:', result);

                    STATE.lastSavedImage = STATE.capturedImageData;
                    updateGalleryThumb();
                    localStorage.setItem('vphone_last_photo', STATE.lastSavedImage);

                    showToast('✅ Photo saved!', 'success');
                    if (DOM.statusText) DOM.statusText.textContent = 'Saved';

                    // Refresh gallery if open
                    if (DOM.galleryOverlay.classList.contains('show')) {
                        loadGalleryImages();
                    }

                    setTimeout(() => {
                        retakePhoto();
                        showToast('📁 Photo saved to gallery', 'success');
                    }, 1200);

                } catch (error) {
                    console.error('Save error:', error);
                    showToast('⚠ Failed to save photo', 'error');
                    if (DOM.statusText) DOM.statusText.textContent = 'Error';
                } finally {
                    if (DOM.savePhotoBtn) {
                        DOM.savePhotoBtn.disabled = false;
                        DOM.savePhotoBtn.innerHTML = '<i class="fas fa-check"></i> Save';
                    }
                }
            }

            // ─── GALLERY THUMB ───
            function updateGalleryThumb() {
                if (STATE.lastSavedImage) {
                    DOM.galleryThumb.innerHTML =
                        `<img src="${STATE.lastSavedImage}" alt="Last" style="width:100%;height:100%;object-fit:cover;">`;
                } else {
                    DOM.galleryThumb.innerHTML = `<span class="placeholder-icon"><i class="fas fa-image"></i></span>`;
                }
            }

            // ─── SWITCH CAMERA ───
            function switchCamera() {
                if (STATE.capturing || STATE.isPreview) {
                    showToast('⚠ Retake or save first', 'error');
                    return;
                }
                const newFacing = STATE.facingMode === 'environment' ? 'user' : 'environment';
                startCamera(newFacing);
                showToast(`🔄 Switched to ${newFacing === 'environment' ? 'rear' : 'front'}`, '');
            }

            // ─── FLASH TOGGLE ───
            function toggleFlash() {
                STATE.flashOn = !STATE.flashOn;
                DOM.flashToggleBtn.classList.toggle('active', STATE.flashOn);
                showToast(STATE.flashOn ? '💡 Flash on' : '💡 Flash off', '');
            }

            // ─── GRID TOGGLE ───
            function toggleGrid() {
                STATE.gridOn = !STATE.gridOn;
                DOM.gridOverlay.classList.toggle('show', STATE.gridOn);
                DOM.gridToggleBtn.classList.toggle('active', STATE.gridOn);
                showToast(STATE.gridOn ? '📐 Grid on' : '📐 Grid off', '');
            }

            // ─── SETTINGS TOGGLE ───
            function toggleSettings() {
                STATE.settingsOpen = !STATE.settingsOpen;
                DOM.settingsPanel.classList.toggle('open', STATE.settingsOpen);
                DOM.settingsToggleBtn.classList.toggle('active', STATE.settingsOpen);
                if (STATE.settingsOpen) {
                    DOM.zoomIndicator.classList.add('show');
                } else {
                    DOM.zoomIndicator.classList.remove('show');
                }
            }

            // ─── ZOOM ───
            function updateZoom(val) {
                STATE.zoom = parseFloat(val);
                DOM.zoomValue.textContent = STATE.zoom.toFixed(1) + '×';
                DOM.zoomBadge.textContent = STATE.zoom.toFixed(1) + '×';
                DOM.zoomBadge.classList.add('show');
                clearTimeout(DOM.zoomBadge._hide);
                DOM.zoomBadge._hide = setTimeout(() => { DOM.zoomBadge.classList.remove('show'); }, 1200);
                applyZoom();
            }

            // ─── EXPOSURE ───
            function updateExposure(val) {
                STATE.exposure = parseFloat(val);
                DOM.exposureValue.textContent = STATE.exposure.toFixed(1);
                const track = STATE.stream?.getVideoTracks()[0];
                if (track && track.getCapabilities && track.getCapabilities().exposureCompensation) {
                    track.applyConstraints({ advanced: [{ exposureCompensation: STATE.exposure }] }).catch(() => {});
                } else {
                    const val2 = Math.pow(2, STATE.exposure);
                    cameraVideo.style.filter = `brightness(${val2})`;
                }
            }

            // ─── ASPECT ───
            function setAspect(aspect) {
                STATE.aspect = aspect;
                document.querySelectorAll('#aspectGroup .setting-option').forEach(el => {
                    el.classList.toggle('active', el.dataset.aspect === aspect);
                });
                startCamera(STATE.facingMode);
                showToast(`📐 Aspect: ${aspect}`, '');
            }

            // ─── WB ───
            function setWB(wb) {
                STATE.wb = wb;
                document.querySelectorAll('#wbGroup .setting-option').forEach(el => {
                    el.classList.toggle('active', el.dataset.wb === wb);
                });
                const track = STATE.stream?.getVideoTracks()[0];
                if (track && track.getCapabilities && track.getCapabilities().whiteBalanceMode) {
                    track.applyConstraints({ advanced: [{ whiteBalanceMode: wb }] }).catch(() => {});
                }
                showToast(`WB: ${wb}`, '');
            }

            // ─── TIMER ───
            function setTimer(seconds) {
                STATE.timerSeconds = parseInt(seconds);
                document.querySelectorAll('#timerGroup .setting-option').forEach(el => {
                    el.classList.toggle('active', parseInt(el.dataset.timer) === seconds);
                });
                showToast(STATE.timerSeconds > 0 ? `⏱ Timer: ${STATE.timerSeconds}s` : '⏱ Timer off', '');
            }

            // ─── RESOLUTION ───
            function setResolution(res) {
                STATE.resolution = res;
                document.querySelectorAll('#resolutionGroup .setting-option').forEach(el => {
                    el.classList.toggle('active', el.dataset.res === res);
                });
                startCamera(STATE.facingMode);
                showToast(`📹 Resolution: ${res.toUpperCase()}`, '');
            }

            // ─── OPEN / CLOSE CAMERA ───
            function openCameraApp() {
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', false);
                    showLoginPanel();
                    return;
                }

                if (!DOM.cameraOverlay) return;

                // Build UI if needed
                if (!cameraVideo) {
                    buildCameraUI();
                }

                DOM.cameraOverlay.classList.add('show');
                DOM.clockWidget.classList.add('hidden');
                STATE.isCameraOpen = true;

                // If camera not ready, start it
                if (!cameraReady || !STATE.stream) {
                    startCamera(STATE.facingMode);
                } else {
                    // Resume video
                    if (cameraVideo && cameraVideo.paused) {
                        cameraVideo.play().catch(() => {});
                    }
                    if (DOM.statusText) DOM.statusText.textContent = 'Live';
                    if (DOM.cameraLoading) DOM.cameraLoading.style.display = 'none';
                }

                // Hide preview
                if (DOM.preview) DOM.preview.style.display = 'none';
                STATE.isPreview = false;

                showNotification('📷 Camera opened', true);
                // Load last photo for thumb
                try {
                    const last = localStorage.getItem('vphone_last_photo');
                    if (last) {
                        STATE.lastSavedImage = last;
                        updateGalleryThumb();
                    }
                } catch (_) {}
            }

            function closeCameraApp() {
                if (STATE.stream) {
                    STATE.stream.getTracks().forEach(t => t.stop());
                    STATE.stream = null;
                }
                STATE.ready = false;
                cameraReady = false;
                STATE.isCameraOpen = false;

                if (DOM.cameraOverlay) DOM.cameraOverlay.classList.remove('show');
                if (DOM.clockWidget) DOM.clockWidget.classList.remove('hidden');
                if (DOM.preview) DOM.preview.style.display = 'none';
                STATE.isPreview = false;

                showNotification('📱 Back to home', true);
            }

            // ================================================================
            //  GALLERY
            // ================================================================

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
                        DOM.galleryGrid.innerHTML =
                            `<div class="gallery-empty"><i class="fas fa-lock"></i>Please login to view photos</div>`;
                        return;
                    }

                    // Try to load from server
                    const response = await fetch(`get_images.php?user_id=${userId}`);
                    const data = await response.json();

                    if (data && data.success && data.images && data.images.length > 0) {
                        renderGallery(data.images);
                        return;
                    }

                    // Fallback: show local last photo
                    const last = localStorage.getItem('vphone_last_photo');
                    if (last) {
                        renderGallery([last]);
                        return;
                    }

                    DOM.galleryGrid.innerHTML =
                        `<div class="gallery-empty"><i class="fas fa-camera"></i>No photos yet.<br>Capture your first photo!</div>`;

                } catch (error) {
                    console.error('Gallery load error:', error);
                    // Fallback: show local last photo
                    const last = localStorage.getItem('vphone_last_photo');
                    if (last) {
                        renderGallery([last]);
                        return;
                    }
                    DOM.galleryGrid.innerHTML =
                        `<div class="gallery-empty"><i class="fas fa-wifi"></i>Could not load photos</div>`;
                }
            }

            function renderGallery(images) {
                if (!images || images.length === 0) {
                    DOM.galleryGrid.innerHTML =
                        `<div class="gallery-empty"><i class="fas fa-camera"></i>No photos yet.<br>Capture your first photo!</div>`;
                    return;
                }

                let html = '';
                images.forEach((img, index) => {
                    const src = typeof img === 'string' ? img : img.url || img.path || img;
                    html += `
                        <div class="gallery-item" data-index="${index}">
                            <img src="${src}" alt="Photo ${index+1}" loading="lazy" 
                                 onclick="window.vphone.viewPhoto('${src}')">
                            <button class="delete-btn" onclick="event.stopPropagation(); window.vphone.deletePhoto('${src}', this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                });

                DOM.galleryGrid.innerHTML = html;

                // Update last saved image
                if (images.length > 0) {
                    const lastSrc = typeof images[0] === 'string' ? images[0] : images[0].url || images[0].path || images[0];
                    STATE.lastSavedImage = lastSrc;
                    updateGalleryThumb();
                    localStorage.setItem('vphone_last_photo', lastSrc);
                }
            }

            function openGallery() {
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', false);
                    showLoginPanel();
                    return;
                }

                // If camera is open, close it first
                if (STATE.isCameraOpen) {
                    closeCameraApp();
                }

                DOM.galleryOverlay.classList.add('show');
                loadGalleryImages();
                showNotification('📁 Gallery opened', true);
            }

            function closeGallery() {
                DOM.galleryOverlay.classList.remove('show');
            }

            // ─── VIEW PHOTO (full screen preview) ───
            function viewPhoto(src) {
                // Simple preview - open in new tab
                window.open(src, '_blank');
            }

            // ─── DELETE PHOTO ───
            async function deletePhoto(src, btnElement) {
                if (!confirm('Delete this photo?')) return;

                try {
                    const userId = STATE.sessionUser?.id || 
                        (() => { try { return JSON.parse(localStorage.getItem(CONFIG.STORAGE_KEY))?.id; } catch(_){} })();

                    if (!userId) {
                        showToast('⚠ Please login first', 'error');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('user_id', userId);
                    formData.append('image_path', src);

                    const response = await fetch('delete_image.php', {
                        method: 'POST',
                        body: formData,
                    });

                    const result = await response.json();

                    if (result && result.success) {
                        showToast('🗑️ Photo deleted', 'success');
                        // Remove from UI
                        if (btnElement) {
                            const item = btnElement.closest('.gallery-item');
                            if (item) item.remove();
                        }
                        // Reload gallery
                        setTimeout(loadGalleryImages, 500);
                    } else {
                        showToast('⚠ Failed to delete', 'error');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    showToast('⚠ Error deleting photo', 'error');
                }
            }

            // ─── CAMERA EVENT BINDINGS ───
            function bindCameraEvents() {
                DOM.captureBtn.addEventListener('click', capturePhoto);
                DOM.retakeBtn.addEventListener('click', retakePhoto);
                DOM.savePhotoBtn.addEventListener('click', savePhoto);
                
                // Close camera button: redirect to dashboard
                DOM.closeCameraBtn.addEventListener('click', function() {
                    window.location.href = '/virtual-phone/user/user_phone_dashboard.html';
                });

                DOM.switchCameraBtn.addEventListener('click', switchCamera);
                DOM.flashToggleBtn.addEventListener('click', toggleFlash);
                DOM.gridToggleBtn.addEventListener('click', toggleGrid);
                DOM.settingsToggleBtn.addEventListener('click', toggleSettings);
                DOM.settingsHandle.addEventListener('click', toggleSettings);
                DOM.zoomSlider.addEventListener('input', function() { updateZoom(this.value); });
                DOM.exposureSlider.addEventListener('input', function() { updateExposure(this.value); });

                document.querySelectorAll('#aspectGroup .setting-option').forEach(el => {
                    el.addEventListener('click', function() { setAspect(this.dataset.aspect); });
                });
                document.querySelectorAll('#wbGroup .setting-option').forEach(el => {
                    el.addEventListener('click', function() { setWB(this.dataset.wb); });
                });
                document.querySelectorAll('#timerGroup .setting-option').forEach(el => {
                    el.addEventListener('click', function() { setTimer(parseInt(this.dataset.timer)); });
                });
                document.querySelectorAll('#resolutionGroup .setting-option').forEach(el => {
                    el.addEventListener('click', function() { setResolution(this.dataset.res); });
                });

                // Gallery thumb click: open full gallery
                DOM.galleryThumb.addEventListener('click', function() {
                    openGallery();
                });

                // Keyboard shortcuts for camera
                document.addEventListener('keydown', function onCamKey(e) {
                    if (!DOM.cameraOverlay || !DOM.cameraOverlay.classList.contains('show')) return;
                    if (e.key === 'Enter') { capturePhoto();
                        e.preventDefault(); }
                    if (e.key === 'Escape') { 
                        // Redirect to dashboard on escape
                        window.location.href = '/virtual-phone/user/user_phone_dashboard.html';
                        e.preventDefault();
                    }
                    if (e.key === 'r' || e.key === 'R') { retakePhoto();
                        e.preventDefault(); }
                    if (e.key === 's' || e.key === 'S') { savePhoto();
                        e.preventDefault(); }
                });
            }

            // ─── APP LAUNCHERS ───
            DOM.cameraAppLauncher.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', false);
                    showLoginPanel();
                    return;
                }
                toggleApps(false);
                openCameraApp();
            });

            DOM.galleryAppLauncher.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', false);
                    showLoginPanel();
                    return;
                }
                toggleApps(false);
                openGallery();
            });

            DOM.settingsAppLauncher.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!STATE.isLoggedIn) {
                    showNotification('⚠ Please login first', false);
                    showLoginPanel();
                    return;
                }
                toggleApps(false);
                showNotification('⚙️ Settings coming soon!', true);
            });

            DOM.closeGalleryBtn.addEventListener('click', closeGallery);

            // ─── KEYBOARD ESCAPE FOR GALLERY ───
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && DOM.galleryOverlay.classList.contains('show')) {
                    closeGallery();
                }
            });

            // ─── EXPOSE PUBLIC API ───
            window.vphone = window.vphone || {};
            window.vphone.logout = logout;
            window.vphone.showNotification = showNotification;
            window.vphone.getUser = function() { return STATE.sessionUser; };
            window.vphone.isLoggedIn = function() { return STATE.isLoggedIn; };
            window.vphone.openCamera = openCameraApp;
            window.vphone.closeCamera = closeCameraApp;
            window.vphone.openGallery = openGallery;
            window.vphone.closeGallery = closeGallery;
            window.vphone.viewPhoto = viewPhoto;
            window.vphone.deletePhoto = deletePhoto;
            window.vphone.loadGallery = loadGalleryImages;

            // ─── INIT ───
            function init() {
                updateClock();
                setInterval(updateClock, CONFIG.CLOCK_INTERVAL);

                const hasSession = checkSession();
                if (!hasSession) {
                    showLoginPanel();
                }

                startWallpaperRotation();

                // Bind camera events
                buildCameraUI();
                bindCameraEvents();

                // Load last photo
                try {
                    const last = localStorage.getItem('vphone_last_photo');
                    if (last) {
                        STATE.lastSavedImage = last;
                        updateGalleryThumb();
                    }
                } catch (_) {}

                // Auto-open camera if logged in
                if (STATE.isLoggedIn) {
                    setTimeout(openCameraApp, 800);
                }

                // Network status
                window.addEventListener('online', () => showNotification('📶 Back online.', true));
                window.addEventListener('offline', () => showNotification('📡 Disconnected.', false));

                console.log('[Virtual Phone] Initialized with Pro Camera + Gallery.');
            }

            // ─── START ───
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

        })();
    </script>

</body>
</html>