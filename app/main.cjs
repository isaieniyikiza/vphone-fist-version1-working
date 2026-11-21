'use strict';

const { app, BrowserWindow, ipcMain, Menu, net: electronNet, Notification, nativeImage, shell, Tray } = require('electron');
const { spawn } = require('node:child_process');
const net = require('node:net');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

const APP_URL = 'http://localhost/virtual-phone/user/user_phone_dashboard.html';
const APP_ORIGIN = new URL(APP_URL).origin;
const API_PORT = 3001;
const API_SERVER = path.join(__dirname, '..', 'server', 'server.js');
const OFFLINE_PAGE = path.join(__dirname, 'offline.html');
const OFFLINE_PAGE_URL = pathToFileURL(OFFLINE_PAGE).href;
const APP_ICON = path.join(__dirname, 'appicon', 'logo.png');   
const PROFILE_IMAGE_PREFIX = `${APP_ORIGIN}/virtual-phone/uploads/profile/`;

let mainWindow;
let apiProcess;
let tray;
let isAppQuitting = false;

function isApiAvailable() {
    return new Promise(resolve => {
        const socket = net.createConnection({ host: '127.0.0.1', port: API_PORT });
        const finish = available => {
            socket.destroy();
            resolve(available);
        };

        socket.setTimeout(1000);
        socket.once('connect', () => finish(true));
        socket.once('timeout', () => finish(false));
        socket.once('error', () => finish(false));
    });
}

async function startApiServer() {
    if (await isApiAvailable()) {
        console.info(`Virtual Phone API is already available on port ${API_PORT}.`);
        return;
    }

    try {
        apiProcess = spawn('node', [API_SERVER], {
            cwd: path.dirname(API_SERVER),
            env: process.env,
            stdio: ['ignore', 'pipe', 'pipe'],
        });
    } catch (error) {
        console.error('Could not start the Virtual Phone API:', error);
        return;
    }

    apiProcess.stdout.on('data', data => console.info(`[Virtual Phone API] ${data.toString().trimEnd()}`));
    apiProcess.stderr.on('data', data => console.error(`[Virtual Phone API] ${data.toString().trimEnd()}`));
    apiProcess.once('error', error => {
        console.error('Could not start the Virtual Phone API:', error);
        apiProcess = null;
    });
    apiProcess.once('exit', (code, signal) => {
        if (code !== 0 && code !== null) {
            console.error(`Virtual Phone API exited with code ${code}.`);
        } else if (signal && !isAppQuitting) {
            console.error(`Virtual Phone API exited after signal ${signal}.`);
        }
        apiProcess = null;
    });
}

function isVirtualPhoneUrl(value) {
    try {
        const url = new URL(value);
        return url.origin === APP_ORIGIN && url.pathname.startsWith('/virtual-phone/');
    } catch {
        return false;
    }
}

async function openOfflinePage(window) {
    if (window.isDestroyed()) return;

    try {
        await window.loadFile(OFFLINE_PAGE);
    } catch (error) {
        console.error('Could not display the connection help page:', error);
    }
}

async function loadVirtualPhone(window) {
    if (window.isDestroyed()) return;

    try {
        await window.loadURL(APP_URL);
    } catch (error) {
        console.error(`Could not connect to ${APP_URL}:`, error.message);
        await openOfflinePage(window);
    }
}

function createWindow() {
    if (mainWindow && !mainWindow.isDestroyed()) {
        showMainWindow();
        return;
    }

    mainWindow = new BrowserWindow({
        width: 1280,
        height: 900,
        minWidth: 360,
        minHeight: 600,
        show: false,
        autoHideMenuBar: true,
        backgroundColor: '#111827',
        title: 'Virtual Phone',
        icon: APP_ICON,
        webPreferences: {
            preload: path.join(__dirname, 'preload.cjs'),
            contextIsolation: true,
            nodeIntegration: false,
            sandbox: true,
            webSecurity: true,
            backgroundThrottling: false,
        },
    });

    mainWindow.once('ready-to-show', () => mainWindow.show());

    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        if (url.startsWith('https://')) {
            shell.openExternal(url).catch(error => {
                console.error('Could not open external link:', error.message);
            });
        }
        return { action: 'deny' };
    });

    mainWindow.webContents.on('will-navigate', (event, url) => {
        if (url.startsWith(OFFLINE_PAGE_URL) || isVirtualPhoneUrl(url)) return;

        event.preventDefault();
        if (url.startsWith('https://')) {
            shell.openExternal(url).catch(error => {
                console.error('Could not open external link:', error.message);
            });
        }
    });

    mainWindow.webContents.on('did-fail-load', (_event, errorCode, description, validatedURL, isMainFrame) => {
        if (!isMainFrame || errorCode === -3 || !isVirtualPhoneUrl(validatedURL)) return;
        console.error(`Virtual Phone page failed to load (${errorCode}): ${description}`);
        openOfflinePage(mainWindow);
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });

    mainWindow.on('close', event => {
        if (isAppQuitting) return;
        event.preventDefault();
        mainWindow.hide();
    });

    loadVirtualPhone(mainWindow);
}

function showMainWindow() {
    if (!mainWindow || mainWindow.isDestroyed()) return;
    if (mainWindow.isMinimized()) mainWindow.restore();
    mainWindow.show();
    mainWindow.focus();
}

function createTray() {
    tray = new Tray(nativeImage.createFromPath(APP_ICON));
    tray.setToolTip('Virtual Phone is running in the background');
    tray.setContextMenu(Menu.buildFromTemplate([
        { label: 'Open Virtual Phone', click: showMainWindow },
        { type: 'separator' },
        { label: 'Quit Virtual Phone', click: () => app.quit() },
    ]));
    tray.on('click', showMainWindow);
}

ipcMain.handle('virtual-phone:retry', event => {
    if (event.senderFrame?.url !== OFFLINE_PAGE_URL) {
        throw new Error('Retry is only available from the connection help page.');
    }
    if (mainWindow && !mainWindow.isDestroyed()) {
        return loadVirtualPhone(mainWindow);
    }
});

ipcMain.on('virtual-phone:notification', (event, details) => {
    if (!isVirtualPhoneUrl(event.senderFrame?.url || '')) return;
    if (!Notification.isSupported()) {
        console.warn('Native notifications are not supported on this system.');
        return;
    }

    const title = typeof details?.title === 'string' ? details.title.slice(0, 120) : 'Virtual Phone';
    const body = typeof details?.body === 'string' ? details.body.slice(0, 240) : '';
    const senderId = typeof details?.senderId === 'string' && /^\d{1,12}$/.test(details.senderId)
        ? details.senderId
        : '';
    const show = icon => {
        try {
            const notification = new Notification({ title, body, icon: icon.isEmpty() ? nativeImage.createFromPath(APP_ICON) : icon });
            notification.on('click', () => {
                showMainWindow();
                if (!senderId || !mainWindow || mainWindow.isDestroyed()) return;
                const conversationUrl = `${APP_ORIGIN}/virtual-phone/user/chat_room/vphone_chat.html?user=${encodeURIComponent(senderId)}`;
                mainWindow.loadURL(conversationUrl).catch(error => {
                    console.error('Could not open the conversation from its notification:', error);
                });
            });
            notification.show();
        } catch (error) {
            console.error('Could not show a Virtual Phone notification:', error);
        }
    };

    const imageUrl = typeof details?.profileImage === 'string' ? details.profileImage : '';
    if (!imageUrl) {
        show(nativeImage.createEmpty());
        return;
    }

    try {
        const parsedImageUrl = new URL(imageUrl, APP_ORIGIN);
        if (parsedImageUrl.origin !== APP_ORIGIN || !parsedImageUrl.href.startsWith(PROFILE_IMAGE_PREFIX)) {
            show(nativeImage.createEmpty());
            return;
        }

        electronNet.fetch(parsedImageUrl.href).then(async response => {
            if (!response.ok) throw new Error(`Profile image request failed (${response.status}).`);
            const imageBuffer = Buffer.from(await response.arrayBuffer());
            if (imageBuffer.length > 2 * 1024 * 1024) throw new Error('Profile image exceeds the notification size limit.');
            show(nativeImage.createFromBuffer(imageBuffer).resize({ width: 64, height: 64 }));
        }).catch(error => {
            console.warn('Could not load the notification sender profile image:', error);
            show(nativeImage.createEmpty());
        });
    } catch (error) {
        console.warn('Could not prepare the notification sender profile image:', error);
        show(nativeImage.createEmpty());
    }
});

app.whenReady().then(() => {
    app.setAppUserModelId('com.virtualphone.desktop');
    createTray();
    startApiServer().catch(error => {
        console.error('Could not check or start the Virtual Phone API:', error);
    });
    createWindow();

    app.on('activate', () => {
        if (mainWindow && !mainWindow.isDestroyed()) showMainWindow();
        else createWindow();
    });
});

app.on('before-quit', () => {
    isAppQuitting = true;
    if (tray) {
        tray.destroy();
        tray = null;
    }
    if (apiProcess && !apiProcess.killed) {
        apiProcess.kill();
    }
});

app.on('window-all-closed', () => {
    if (isAppQuitting && process.platform !== 'darwin') app.quit();
});
