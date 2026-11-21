# Virtual Phone Mobile App

This folder provides installable PWA packaging for Android and iPhone.

## Install in a browser

Open `http://localhost/virtual-phone/mobile%20app/` on the same device or a device that can reach the computer.

- Android Chrome: open the install menu and choose **Install app**.
- iPhone Safari: tap Share, then **Add to Home Screen**.

The PWA uses the existing PHP account, database, and API services. The XAMPP server must remain reachable.

## Native Android/iOS project

The folder includes Capacitor configuration for native packaging:

1. Android Studio and the JDK are required to build Android.
2. macOS and Xcode/CocoaPods are required to build iPhone.
3. Run `npm install` inside this folder.
4. Run `npx cap sync`.
5. Open Android Studio or Xcode with `npm run android` or `npm run ios`.

Before packaging for a real phone, change `server.url` in `capacitor.config.json` to the computer's LAN IP or a public HTTPS URL. iOS and Android require HTTPS or cleartext configuration for remote HTTP servers.
