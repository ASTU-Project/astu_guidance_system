import { createInertiaApp } from '@inertiajs/react';
import { createElement } from 'react';
import { createRoot } from 'react-dom/client';
import Layout from './Layouts/Layout';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
    let page = pages[`./Pages/${name}.jsx`];
    page.default.layout = page.default.layout || (page => createElement(Layout, null, page));
    return page;
  },
  setup({ el, App, props }) {
    createRoot(el).render(createElement(App, props));
  },
});