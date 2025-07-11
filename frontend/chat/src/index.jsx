import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import { ChatProvider } from './context/ChatContext';

document.addEventListener('DOMContentLoaded', () => {
    const rootEl = document.getElementById('slc_chat_root');
    if (rootEl) {
        const root = createRoot(rootEl);
        root.render(
            <React.StrictMode>
                <ChatProvider>
                    <App />
                </ChatProvider>
            </React.StrictMode>
        );
    }
});