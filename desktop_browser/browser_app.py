import json
import sys
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import quote_plus

from PySide6.QtCore import QStandardPaths, Qt, QUrl, Signal
from PySide6.QtGui import QColor, QFont, QIcon, QKeySequence, QPainter, QPixmap, QShortcut
from PySide6.QtWidgets import (
    QApplication,
    QDialog,
    QDialogButtonBox,
    QFileDialog,
    QHBoxLayout,
    QLabel,
    QLineEdit,
    QListWidget,
    QListWidgetItem,
    QMainWindow,
    QMessageBox,
    QMenu,
    QPushButton,
    QProgressBar,
    QTabWidget,
    QToolBar,
    QVBoxLayout,
    QWidget,
)
from PySide6.QtWebEngineCore import (
    QWebEnginePage,
    QWebEngineProfile,
    QWebEngineSettings,
)
from PySide6.QtWebEngineWidgets import QWebEngineView


APP_NAME = "Virtual Phone Browser"
HOME_URL = "https://www.google.com/"
MAX_HISTORY_ITEMS = 1000


def create_browser_icon(size):
    pixmap = QPixmap(size, size)
    pixmap.fill(Qt.GlobalColor.transparent)
    painter = QPainter(pixmap)
    painter.setRenderHint(QPainter.RenderHint.Antialiasing)
    painter.setPen(Qt.PenStyle.NoPen)
    margin = max(1, size // 16)
    diameter = size - margin * 2
    for start, color in ((30, "#ea4335"), (150, "#fbbc05"), (270, "#34a853")):
        painter.setBrush(QColor(color))
        painter.drawPie(margin, margin, diameter, diameter, start * 16, 120 * 16)
    center = size * 0.34
    inner = size * 0.32
    painter.setBrush(QColor("#ffffff"))
    painter.drawEllipse(int(center), int(center), int(inner), int(inner))
    inner_margin = size * 0.39
    inner_size = size * 0.22
    painter.setBrush(QColor("#4285f4"))
    painter.drawEllipse(int(inner_margin), int(inner_margin), int(inner_size), int(inner_size))
    painter.end()
    return QIcon(pixmap)


def app_data_directory() -> Path:
    root = Path(QStandardPaths.writableLocation(QStandardPaths.StandardLocation.AppLocalDataLocation))
    root.mkdir(parents=True, exist_ok=True)
    return root


class BrowserLogo(QWidget):
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setFixedSize(30, 30)

    def paintEvent(self, event):
        painter = QPainter(self)
        painter.setRenderHint(QPainter.RenderHint.Antialiasing)
        painter.translate(2, 2)
        painter.setPen(Qt.PenStyle.NoPen)
        for start, color in ((30, "#ea4335"), (150, "#fbbc05"), (270, "#34a853")):
            painter.setBrush(QColor(color))
            painter.drawPie(1, 1, 25, 25, start * 16, 120 * 16)
        painter.setBrush(QColor("#f7f9fc"))
        painter.drawEllipse(7, 7, 13, 13)
        painter.setBrush(QColor("#4285f4"))
        painter.drawEllipse(9, 9, 9, 9)


class BrowserPage(QWebEnginePage):
    def __init__(self, profile, new_tab_callback, parent=None):
        super().__init__(profile, parent)
        self.new_tab_callback = new_tab_callback

    def createWindow(self, window_type):
        view = self.new_tab_callback()
        return view.page()


class ListDialog(QDialog):
    open_url = Signal(str)

    def __init__(self, title, entries, parent=None, removable=False):
        super().__init__(parent)
        self.setWindowTitle(title)
        self.resize(680, 460)
        self.setStyleSheet("QDialog { background: #f5f7fb; }")
        layout = QVBoxLayout(self)
        heading = QLabel(title)
        heading.setObjectName("dialogHeading")
        layout.addWidget(heading)
        self.items = QListWidget()
        self.items.setAlternatingRowColors(True)
        for entry in entries:
            title_text = entry.get("title") or entry.get("url", "")
            when = entry.get("visited_at")
            if when:
                try:
                    time_label = datetime.fromisoformat(when).astimezone().strftime("%b %d, %Y · %I:%M %p")
                    title_text = f"{title_text}   —   {time_label}"
                except ValueError:
                    pass
            item = QListWidgetItem(title_text)
            item.setData(Qt.ItemDataRole.UserRole, entry.get("url", ""))
            item.setToolTip(entry.get("url", ""))
            self.items.addItem(item)
        layout.addWidget(self.items, 1)
        buttons = QDialogButtonBox()
        if removable:
            self.remove_button = buttons.addButton("Remove selected", QDialogButtonBox.ButtonRole.ActionRole)
        buttons.addButton(QDialogButtonBox.StandardButton.Close)
        buttons.rejected.connect(self.reject)
        buttons.button(QDialogButtonBox.StandardButton.Close).clicked.connect(self.accept)
        layout.addWidget(buttons)
        self.items.itemDoubleClicked.connect(self._open_selected)
        self.items.itemActivated.connect(self._open_selected)
        if removable:
            self.remove_button.clicked.connect(self._remove_selected)

    def _open_selected(self, item=None):
        item = item or self.items.currentItem()
        if item and item.data(Qt.ItemDataRole.UserRole):
            self.open_url.emit(item.data(Qt.ItemDataRole.UserRole))
            self.accept()

    def _remove_selected(self):
        row = self.items.currentRow()
        if row >= 0:
            self.items.takeItem(row)

    def entries(self):
        result = []
        for index in range(self.items.count()):
            item = self.items.item(index)
            result.append({"title": item.text(), "url": item.data(Qt.ItemDataRole.UserRole)})
        return result


class BrowserWindow(QMainWindow):
    def __init__(self):
        super().__init__()
        self.data_path = app_data_directory()
        self.history_path = self.data_path / "history.json"
        self.bookmarks_path = self.data_path / "bookmarks.json"
        self.history = self._read_json(self.history_path, [])
        self.bookmarks = self._read_json(self.bookmarks_path, [])
        self.download_directory = Path.home() / "Downloads"
        self._tabs_created = 0

        self.profile = QWebEngineProfile("VirtualPhoneBrowser", self)
        profile_path = self.data_path / "profile"
        cache_path = self.data_path / "cache"
        profile_path.mkdir(parents=True, exist_ok=True)
        cache_path.mkdir(parents=True, exist_ok=True)
        self.profile.setPersistentStoragePath(str(profile_path))
        self.profile.setCachePath(str(cache_path))
        self.profile.setHttpCacheType(QWebEngineProfile.HttpCacheType.DiskHttpCache)
        self.profile.setPersistentCookiesPolicy(
            QWebEngineProfile.PersistentCookiesPolicy.ForcePersistentCookies
        )
        self.profile.downloadRequested.connect(self._download_requested)

        self.setWindowTitle(APP_NAME)
        self.setWindowIcon(create_browser_icon(64))
        self.resize(1280, 820)
        self.setMinimumSize(820, 560)
        self._build_ui()
        self._build_actions()
        self._apply_style()
        self._add_tab(QUrl(HOME_URL), "New tab")
        self.statusBar().showMessage(f"Profile data is stored locally in {self.data_path}", 6000)

    def _read_json(self, path, default):
        if not path.exists():
            return default
        try:
            with path.open("r", encoding="utf-8") as file:
                data = json.load(file)
            if not isinstance(data, type(default)):
                raise ValueError(f"{path.name} has an unexpected format.")
            return data
        except (OSError, json.JSONDecodeError, ValueError) as error:
            QMessageBox.warning(
                self,
                "Browser data could not be loaded",
                f"{path.name} could not be read. The browser will start with an empty list.\n\n{error}",
            )
            return default

    def _write_json(self, path, data):
        temporary = path.with_suffix(path.suffix + ".tmp")
        try:
            with temporary.open("w", encoding="utf-8") as file:
                json.dump(data, file, ensure_ascii=False, indent=2)
            temporary.replace(path)
        except OSError as error:
            QMessageBox.critical(self, "Could not save browser data", str(error))

    def _build_ui(self):
        root = QWidget()
        root_layout = QVBoxLayout(root)
        root_layout.setContentsMargins(0, 0, 0, 0)
        root_layout.setSpacing(0)
        self.setCentralWidget(root)

        brand = QWidget()
        brand.setObjectName("brandBar")
        brand_layout = QHBoxLayout(brand)
        brand_layout.setContentsMargins(18, 8, 18, 6)
        brand_layout.setSpacing(10)
        brand_layout.addWidget(BrowserLogo())
        brand_label = QLabel("Virtual Phone")
        brand_label.setObjectName("brandLabel")
        brand_layout.addWidget(brand_label)
        brand_layout.addStretch(1)
        subtitle = QLabel("Private, local browser profile")
        subtitle.setObjectName("subtitleLabel")
        brand_layout.addWidget(subtitle)
        root_layout.addWidget(brand)

        self.toolbar = QToolBar("Browser controls")
        self.toolbar.setObjectName("navigationToolbar")
        self.toolbar.setMovable(False)
        self.toolbar.setToolButtonStyle(Qt.ToolButtonStyle.ToolButtonTextOnly)
        self.addToolBar(self.toolbar)

        self.back_button = self._tool_button("←", "Go back", self._go_back)
        self.forward_button = self._tool_button("→", "Go forward", self._go_forward)
        self.reload_button = self._tool_button("↻", "Reload page", self._reload)
        self.home_button = self._tool_button("⌂", "Open home page", lambda: self._navigate(QUrl(HOME_URL)))
        self.toolbar.addWidget(self.back_button)
        self.toolbar.addWidget(self.forward_button)
        self.toolbar.addWidget(self.reload_button)
        self.toolbar.addWidget(self.home_button)

        self.address_bar = QLineEdit()
        self.address_bar.setObjectName("addressBar")
        self.address_bar.setPlaceholderText("Search or enter a website address")
        self.address_bar.setClearButtonEnabled(True)
        self.address_bar.returnPressed.connect(self._submit_address)
        self.toolbar.addWidget(self.address_bar)
        self.bookmark_button = self._tool_button("☆", "Bookmark this page", self._toggle_bookmark)
        self.toolbar.addWidget(self.bookmark_button)
        self.menu_button = self._tool_button("⋯", "Browser menu", self._show_menu)
        self.toolbar.addWidget(self.menu_button)

        self.tabs = QTabWidget()
        self.tabs.setObjectName("browserTabs")
        self.tabs.setDocumentMode(True)
        self.tabs.setTabsClosable(True)
        self.tabs.setMovable(True)
        self.tabs.tabCloseRequested.connect(self._close_tab)
        self.tabs.currentChanged.connect(self._current_tab_changed)
        self.tabs.tabBarDoubleClicked.connect(self._new_tab)
        root_layout.addWidget(self.tabs, 1)

        self.progress = QProgressBar()
        self.progress.setObjectName("loadProgress")
        self.progress.setTextVisible(False)
        self.progress.setFixedHeight(3)
        self.progress.hide()
        root_layout.addWidget(self.progress)

        self._install_shortcuts()

    def _build_actions(self):
        self.browser_menu = QMenu(self)
        self.browser_menu.addAction("New tab", self._new_tab)
        self.browser_menu.addAction("Bookmarks", self._show_bookmarks)
        self.browser_menu.addAction("History", self._show_history)
        self.browser_menu.addAction("Downloads folder", self._open_downloads_folder)
        self.browser_menu.addSeparator()
        self.browser_menu.addAction("Clear browsing data…", self._clear_browsing_data)
        self.browser_menu.addAction("About Virtual Phone Browser", self._show_about)

    def _apply_style(self):
        self.setStyleSheet("""
            QMainWindow { background: #f4f6fb; }
            #brandBar { background: #ffffff; border-bottom: 1px solid #e8ebf2; }
            #brandLabel { color: #202638; font-size: 15px; font-weight: 700; }
            #subtitleLabel { color: #838b9d; font-size: 11px; }
            #navigationToolbar { background: #ffffff; border: 0; padding: 8px 14px; spacing: 6px; }
            QPushButton#navButton { min-width: 34px; min-height: 34px; color: #35405a; background: transparent; border: 0; border-radius: 9px; padding: 3px 8px; font-size: 16px; }
            QPushButton#navButton:hover { background: #eef1f8; }
            QPushButton#navButton:pressed { background: #e1e6f2; }
            QPushButton#navButton:disabled { color: #b7bdca; }
            #addressBar { min-height: 22px; border: 1px solid #e2e6ef; border-radius: 18px; background: #f5f7fb; padding: 7px 15px; color: #252b3a; font-size: 13px; selection-background-color: #5275e8; }
            #addressBar:focus { background: #ffffff; border: 1px solid #879cf1; }
            #browserTabs::pane { border: 0; background: #ffffff; }
            QTabBar::tab { background: #edf0f6; color: #677086; padding: 10px 18px; min-width: 90px; border-top-left-radius: 9px; border-top-right-radius: 9px; margin-right: 3px; }
            QTabBar::tab:selected { background: #ffffff; color: #273149; }
            QTabBar::close-button { image: none; }
            QStatusBar { background: #ffffff; color: #727b8f; border-top: 1px solid #e8ebf2; }
            QProgressBar { background: #e8ebf3; border: 0; }
            QProgressBar::chunk { background: #5875e8; }
            #dialogHeading { color: #202638; font-size: 18px; font-weight: 700; padding: 8px 0; }
            QListWidget { background: white; border: 1px solid #e3e7ef; border-radius: 10px; padding: 6px; color: #31394d; }
            QPushButton { background: #e9edfb; border: 0; border-radius: 7px; color: #3349a8; padding: 8px 13px; }
            QPushButton:hover { background: #dce3fa; }
        """)

    def _tool_button(self, text, tooltip, callback):
        button = QPushButton(text)
        button.setObjectName("navButton")
        button.setToolTip(tooltip)
        button.setCursor(Qt.CursorShape.PointingHandCursor)
        button.clicked.connect(callback)
        return button

    def _install_shortcuts(self):
        shortcuts = [
            ("Ctrl+L", lambda: (self.address_bar.setFocus(), self.address_bar.selectAll())),
            ("Ctrl+T", self._new_tab),
            ("Ctrl+W", lambda: self._close_tab(self.tabs.currentIndex())),
            ("Ctrl+R", self._reload),
            ("Alt+Left", self._go_back),
            ("Alt+Right", self._go_forward),
        ]
        for sequence, callback in shortcuts:
            shortcut = QShortcut(QKeySequence(sequence), self)
            shortcut.activated.connect(callback)

    def _add_tab(self, url=None, title="New tab"):
        view = QWebEngineView()
        page = BrowserPage(self.profile, self._new_tab_view)
        view.setPage(page)
        settings = page.settings()
        settings.setAttribute(QWebEngineSettings.WebAttribute.JavascriptEnabled, True)
        settings.setAttribute(QWebEngineSettings.WebAttribute.LocalStorageEnabled, True)
        settings.setAttribute(QWebEngineSettings.WebAttribute.FullScreenSupportEnabled, True)
        index = self.tabs.addTab(view, title[:24])
        self.tabs.setCurrentIndex(index)
        view.urlChanged.connect(lambda value, current=view: self._url_changed(current, value))
        view.titleChanged.connect(lambda value, current=view: self._title_changed(current, value))
        view.loadProgress.connect(self._load_progress)
        view.loadFinished.connect(lambda ok, current=view: self._load_finished(current, ok))
        target_url = url if url is not None else QUrl(HOME_URL)
        view.setUrl(target_url)
        self.address_bar.setText(target_url.toString())
        self._update_navigation_buttons()
        return view

    def _new_tab_view(self):
        return self._add_tab(QUrl(HOME_URL))

    def _new_tab(self, *_):
        self._add_tab(QUrl(HOME_URL))

    def _current_view(self):
        widget = self.tabs.currentWidget()
        return widget if isinstance(widget, QWebEngineView) else None

    def _navigate(self, url):
        view = self._current_view()
        if view:
            view.setUrl(url)
            view.setFocus()

    def _submit_address(self):
        text = self.address_bar.text().strip()
        if not text:
            return
        if any(character.isspace() for character in text) or "." not in text and "://" not in text:
            url = QUrl("https://www.google.com/search?q=" + quote_plus(text))
        elif "://" not in text:
            url = QUrl("https://" + text)
        else:
            url = QUrl.fromUserInput(text)
        self._navigate(url)

    def _url_changed(self, view, url):
        if view is self._current_view():
            display_url = url.toString()
            if display_url:
                self.address_bar.setText(display_url)
                self.address_bar.setCursorPosition(0)
                self._update_bookmark_button()
                self._update_navigation_buttons()
        if url.scheme() in ("http", "https") and url.host():
            self._record_history(view, url)

    def _record_history(self, view, url):
        title = view.title().strip() or url.host()
        url_text = url.toString()
        if self.history and self.history[0].get("url") == url_text:
            self.history[0]["title"] = title
            return
        self.history.insert(0, {
            "title": title,
            "url": url_text,
            "visited_at": datetime.now(timezone.utc).isoformat(),
        })
        del self.history[MAX_HISTORY_ITEMS:]
        self._write_json(self.history_path, self.history)

    def _title_changed(self, view, title):
        index = self.tabs.indexOf(view)
        if index >= 0:
            self.tabs.setTabText(index, (title or "New tab")[:24])
        url_text = view.url().toString()
        if self.history and self.history[0].get("url") == url_text and title:
            self.history[0]["title"] = title
            self._write_json(self.history_path, self.history)
        if view is self._current_view():
            self.setWindowTitle(f"{title or 'New tab'} — {APP_NAME}")
            self._update_bookmark_button()

    def _load_progress(self, progress):
        if progress < 100:
            self.progress.setValue(progress)
            self.progress.show()
        else:
            self.progress.hide()

    def _load_finished(self, view, succeeded):
        if view is self._current_view():
            self.progress.hide()
            self._update_navigation_buttons()
            if not succeeded:
                self.statusBar().showMessage("This page could not be loaded. Check the address or your connection.", 6000)

    def _current_tab_changed(self, _index):
        view = self._current_view()
        if not view:
            return
        self.address_bar.setText(view.url().toString())
        self.setWindowTitle(f"{view.title() or 'New tab'} — {APP_NAME}")
        self._update_navigation_buttons()
        self._update_bookmark_button()

    def _update_navigation_buttons(self):
        view = self._current_view()
        self.back_button.setEnabled(bool(view and view.history().canGoBack()))
        self.forward_button.setEnabled(bool(view and view.history().canGoForward()))

    def _update_bookmark_button(self):
        view = self._current_view()
        if not view:
            return
        url = view.url().toString()
        saved = any(item.get("url") == url for item in self.bookmarks)
        self.bookmark_button.setText("★" if saved else "☆")
        self.bookmark_button.setToolTip("Remove bookmark" if saved else "Bookmark this page")

    def _toggle_bookmark(self):
        view = self._current_view()
        if not view or view.url().scheme() not in ("http", "https"):
            return
        url = view.url().toString()
        existing = next((item for item in self.bookmarks if item.get("url") == url), None)
        if existing:
            self.bookmarks.remove(existing)
            self.statusBar().showMessage("Bookmark removed", 2500)
        else:
            self.bookmarks.insert(0, {"title": view.title() or url, "url": url})
            self.statusBar().showMessage("Page bookmarked", 2500)
        self._write_json(self.bookmarks_path, self.bookmarks)
        self._update_bookmark_button()

    def _go_back(self):
        view = self._current_view()
        if view and view.history().canGoBack():
            view.back()

    def _go_forward(self):
        view = self._current_view()
        if view and view.history().canGoForward():
            view.forward()

    def _reload(self):
        view = self._current_view()
        if view:
            view.reload()

    def _close_tab(self, index):
        if index < 0:
            return
        if self.tabs.count() == 1:
            self.tabs.widget(index).setUrl(QUrl(HOME_URL))
            return
        widget = self.tabs.widget(index)
        self.tabs.removeTab(index)
        widget.deleteLater()

    def _show_menu(self):
        self.browser_menu.exec(self.menu_button.mapToGlobal(self.menu_button.rect().bottomLeft()))

    def _open_list_entry(self, url):
        self._navigate(QUrl.fromUserInput(url))

    def _show_history(self):
        dialog = ListDialog("Browsing history", self.history, self)
        dialog.open_url.connect(self._open_list_entry)
        dialog.exec()

    def _show_bookmarks(self):
        dialog = ListDialog("Bookmarks", self.bookmarks, self, removable=True)
        dialog.open_url.connect(self._open_list_entry)
        dialog.exec()
        self.bookmarks = dialog.entries()
        self._write_json(self.bookmarks_path, self.bookmarks)
        self._update_bookmark_button()

    def _download_requested(self, download):
        suggested = download.suggestedFileName() or "download"
        default_path = str(self.download_directory / suggested)
        selected_path, _ = QFileDialog.getSaveFileName(self, "Save download", default_path)
        if not selected_path:
            download.cancel()
            return
        target = Path(selected_path)
        self.download_directory = target.parent
        download.setDownloadDirectory(str(target.parent))
        download.setDownloadFileName(target.name)
        download.stateChanged.connect(
            lambda state, item=download, name=target.name: self._download_state_changed(item, state, name)
        )
        download.accept()
        self.statusBar().showMessage(f"Downloading {target.name}…")

    def _download_state_changed(self, download, state, name):
        state_type = type(state)
        if state == state_type.DownloadCompleted:
            self.statusBar().showMessage(f"Downloaded {name}", 5000)
        elif state == state_type.DownloadInterrupted:
            reason = download.interruptReasonString() or "Unknown error"
            self.statusBar().showMessage(f"Download failed: {reason}", 8000)

    def _open_downloads_folder(self):
        path = self.download_directory
        if not path.exists():
            path.mkdir(parents=True, exist_ok=True)
        import os
        os.startfile(str(path))

    def _clear_browsing_data(self):
        answer = QMessageBox.question(
            self,
            "Clear browsing data",
            "Remove saved browsing history, cookies, and cached files?\n\n"
            "This signs you out of websites. Bookmarks will be kept.",
            QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.Cancel,
            QMessageBox.StandardButton.Cancel,
        )
        if answer != QMessageBox.StandardButton.Yes:
            return
        self.history.clear()
        self._write_json(self.history_path, self.history)
        self.profile.cookieStore().deleteAllCookies()
        self.profile.clearHttpCache()
        self.profile.clearAllVisitedLinks()
        self.statusBar().showMessage("Browsing history, cookies, and cache cleared.", 5000)

    def _show_about(self):
        QMessageBox.about(
            self,
            "About Virtual Phone Browser",
            "<b>Virtual Phone Browser</b><br>"
            "A standalone browser powered by Qt WebEngine (Chromium).<br><br>"
            "It is not Google Chrome. Your browsing profile is stored locally on this Windows account.",
        )

    def closeEvent(self, event):
        self._write_json(self.history_path, self.history)
        self._write_json(self.bookmarks_path, self.bookmarks)
        super().closeEvent(event)


def main():
    QApplication.setOrganizationName("VirtualPhone")
    QApplication.setApplicationName("Browser")
    app = QApplication(sys.argv)
    app.setFont(QFont("Segoe UI", 10))
    window = BrowserWindow()
    window.show()
    sys.exit(app.exec())


if __name__ == "__main__":
    main()
