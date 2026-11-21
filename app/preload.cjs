'use strict';

const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('virtualPhoneDesktop', {
    retryConnection: () => ipcRenderer.invoke('virtual-phone:retry'),
    showNotification: details => ipcRenderer.send('virtual-phone:notification', details),
});

window.addEventListener('DOMContentLoaded', () => {
    const retryButton = document.getElementById('retry-button');
    const status = document.getElementById('connection-status');

    if (!retryButton) return;

    retryButton.addEventListener('click', async () => {
        retryButton.disabled = true;
        status.textContent = 'Connecting to your phone…';

        try {
            await window.virtualPhoneDesktop.retryConnection();
        } catch (error) {
            status.textContent = `Could not retry the connection: ${error.message}`;
            retryButton.disabled = false;
        }
    });
});
