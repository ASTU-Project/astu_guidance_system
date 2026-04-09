import { createInertiaApp } from '@inertiajs/react';
import { createElement } from 'react';
import { createRoot } from 'react-dom/client';
import Layout from './Layouts/Layout';

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./{Pages,Auth}/**/*.jsx', { eager: true });
    let page = pages[`./${name}.jsx`] || pages[`./Pages/${name}.jsx`] || pages[`./Auth/${name}.jsx`];
    if (!page) {
      throw new Error(`Page not found: ${name}`);
    }
    page.default.layout = page.default.layout || (page => createElement(Layout, null, page));
    return page;
  },
  setup({ el, App, props }) {
    createRoot(el).render(createElement(App, props));
  },
});