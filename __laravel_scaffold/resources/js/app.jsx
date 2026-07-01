import '../css/app.css';
import './bootstrap';

import AppProviders from '@/app/providers/AppProviders';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'LabOps';
const pages = {
    ...import.meta.glob('./Pages/**/*.jsx'),
    ...import.meta.glob('./pages/**/*.jsx'),
    ...import.meta.glob('./features/**/pages/**/*.jsx'),
};

function resolvePage(name) {
    const path =
        pages[`./pages/${name}.jsx`] ??
        pages[`./features/${name}.jsx`] ??
        pages[`./Pages/${name}.jsx`];

    if (!path) {
        throw new Error(`Page not found: ${name}`);
    }

    return path();
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: resolvePage,
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <AppProviders>
                <App {...props} />
            </AppProviders>,
        );
    },
    progress: {
        color: '#0f766e',
    },
});
