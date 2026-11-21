# Virtual Phone apps

## Admin and publishing

Open `http://localhost/virtual-phone/user/admin/` in a browser. On the first visit from this computer, create the separate admin username and password. Use a unique password with at least 10 characters; the admin account is stored separately from phone users.

The admin app uploader uses the browser's folder picker. Select an app folder containing `index.php`, `index.html`, or `index.htm` at its root, choose an icon, and publish it. The folder is copied to `user/apps/published/<folder-name>/`. Project uploads are capped at 500 files and 100 MB total; the active PHP `max_file_uploads`, `upload_max_filesize`, and `post_max_size` settings may impose lower limits. The admin page displays the configured file-count limit.

## Phone users

Sign in to the Virtual Phone, open **Play Store**, and install an app. Installed apps appear on the home screen and in **App Manager**. Uninstalling removes that user's install record; it does not delete the published project. App launches include **Close** and **Home** controls that return to the phone dashboard. A custom app can also close its wrapper with `window.parent.postMessage('vphone-close-app', window.location.origin)` or a link back to the phone dashboard.

User install records require the normal PHP login session. The dashboard's offline demo login does not create that server session.

## Chat server on Windows

From `server`, run `start-server.cmd` to launch `server.js` directly with Node. The global `npm` command on the current workstation points to an NVM installation under a different Windows profile (`C:\Users\twahi\AppData`), so `npm start` fails before it reads this project's `package.json`. Repair/reinstall the Node version with NVM under the current Windows account to restore `npm start`; the project launcher bypasses that broken global npm link.
