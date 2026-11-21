# Virtual Phone Browser

A standalone Windows desktop browser built with Qt WebEngine (Chromium engine). It is not Google Chrome and opens in its own desktop window, not inside the web-based Virtual Phone screen.

## Install

Python 3.15 is already installed on this computer. From this folder, install the pinned dependencies with:

```powershell
py -3.15 -m pip install -r requirements.txt
```

## Run

Double-click `run_browser.bat`, or run:

```powershell
py -3.15 browser_app.py
```

Cookies, site storage, bookmarks, and browsing history are kept in the current Windows user's local application data folder. They are not uploaded or synchronized with a Virtual Phone account. Sites decide whether their login sessions remain valid; the browser does not save or collect passwords itself.

Use the browser's History and Bookmarks menus to review saved data. The Clear Browsing Data command removes history, cookies, cache, and site storage.
