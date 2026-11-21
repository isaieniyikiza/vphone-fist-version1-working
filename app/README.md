# Virtual Phone Desktop

This Electron app opens the existing Virtual Phone dashboard in a desktop window:

`http://localhost/virtual-phone/user/user_phone_dashboard.html`

## Requirements

- Windows
- Node.js and npm
- XAMPP with Apache running
- MySQL running if the Virtual Phone installation uses its database

When run from this project, the desktop app opens the existing PHP dashboard and starts the project's Node API server (`server/server.js`) if it is not already running. It does not bundle or start Apache, PHP, MySQL, or the website data. Start Apache and MySQL in XAMPP first. The API server also needs its dependencies installed in `server/`.

The app stays running in the Windows system tray when its window is closed. Select **Open Virtual Phone** from the tray icon to return to it, or **Quit Virtual Phone** to exit completely. While the app is running and you are signed in, new phone notifications are also sent to Windows.

## Google sign-in on localhost

The phone dashboard uses Google's server-side authorization-code flow. In the Google Cloud OAuth client (type **Web application**), add this exact authorized redirect URI:

`http://localhost/virtual-phone/user/api/google_callback.php`

Open the site using `http://localhost/virtual-phone/` so the callback matches. Store the OAuth client ID in `google/token.txt` and its matching client secret in `google/api.txt`. Keep the secret private; it is read only by PHP and the `google/` folder is blocked from direct web access.

## Run from the project

From the project root, run:

```powershell
npm.cmd --prefix server install
npm.cmd --prefix app install
npm.cmd --prefix app start
```

First start Apache and MySQL from the XAMPP Control Panel. The desktop app then opens `http://localhost/virtual-phone/user/user_phone_dashboard.html` and starts the Node API server. The project must remain under XAMPP's `htdocs` folder. If XAMPP is not ready when the app opens, the app displays setup steps and a retry button.

To use the phone directly in a browser instead, open `http://localhost/virtual-phone/user/user_phone_dashboard.html` after starting Apache and MySQL. No npm command is needed for the PHP website.

## Build the Windows installer

The installer packages the Electron window only; it still depends on the existing XAMPP website and local Node API server.

```powershell
npm.cmd --prefix app run dist
```

The installer is written to `app/dist/`. The NSIS installer creates a Start Menu entry and a desktop shortcut.
