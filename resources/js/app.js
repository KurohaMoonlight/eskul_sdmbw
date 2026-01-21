// import './bootstrap';
// import { createApp, h } from 'vue'
// import { createInertiaApp } from '@inertiajs/vue3'
// import Vcalendar from 'v-calendar';
// import 'v-calendar/dist/style.css';

// // createInertiaApp({
// //     resolve: name => {
// //         const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
// //         return pages[`./Pages/${name}.vue`]
// //     },
// //     setup({ el, App, props, plugin }) {
// //         createApp({ render: () => h(App, props) })
// //             .use(plugin)
// //             .mount(el)
// //     },
// // })

// // createInertiaApp({
// //     // ...
// //     setup({ el, App, props, plugin }) {
// //         return createApp({ render: () => h(App, props) })
// //             .use(plugin)
// //             .use(ZiggyVue)
// //             .use(VCalendar, {}) // <-- TAMBAHKAN BARIS INI
// //             .mount(el);
// //     },
// //     // ...
// // });

import './bootstrap';
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
// import { ZiggyVue } from '../../vendor/tightenco/ziggy'; // Pastikan ZiggyVue diimport jika digunakan
import VCalendar from 'v-calendar';
import 'v-calendar/style.css';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            // .use(ZiggyVue) // Opsional, tapi disarankan untuk route() helper
            .use(VCalendar, {}) // Mendaftarkan VCalendar secara global
            .mount(el);
    },
})