import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';

// Import UI Base Components
import FtsButton from './components/ui/FtsButton.vue';
import FtsCard from './components/ui/FtsCard.vue';
import FtsBadge from './components/ui/FtsBadge.vue';
import FtsTable from './components/ui/FtsTable.vue';
import FtsModal from './components/ui/FtsModal.vue';
import FtsInput from './components/ui/FtsInput.vue';
import FtsAlert from './components/ui/FtsAlert.vue';
import FtsPagination from './components/ui/FtsPagination.vue';

// Import Feature Modules
import PoStatusBadge from './components/modules/purchase-orders/PoStatusBadge.vue';
import PoItemsTable from './components/modules/purchase-orders/PoItemsTable.vue';
import PoApprovalActions from './components/modules/purchase-orders/PoApprovalActions.vue';
import PoTimeline from './components/modules/purchase-orders/PoTimeline.vue';
import PaymentStatusBadge from './components/modules/payments/PaymentStatusBadge.vue';
import PaymentAnalyticsChart from './components/modules/payments/PaymentAnalyticsChart.vue';
import PaymentBookingStatusTracker from './components/modules/payments/PaymentBookingStatusTracker.vue';
import AttendanceGrid from './components/modules/attendance/AttendanceGrid.vue';
import QrScannerWidget from './components/modules/attendance/QrScannerWidget.vue';
import AttendancePendingList from './components/modules/attendance/AttendancePendingList.vue';
import InvoiceStatusBadge from './components/modules/invoices/InvoiceStatusBadge.vue';
import InvoicePdfButton from './components/modules/invoices/InvoicePdfButton.vue';
import QuotationCompareTable from './components/modules/quotations/QuotationCompareTable.vue';
import NotificationBell from './components/modules/notifications/NotificationBell.vue';

const pinia = createPinia();

/**
 * Island Mounter Registry
 * Automatically discovers data-island elements or specific IDs in Blade views
 * and mounts Vue 3 reactive components with their dataset props.
 */
const islands = [
    { selector: '#notification-bell-island', component: NotificationBell },
    { selector: '#po-items-island', component: PoItemsTable },
    { selector: '#po-approval-island', component: PoApprovalActions },
    { selector: '#po-timeline-island', component: PoTimeline },
    { selector: '#payment-analytics-island', component: PaymentAnalyticsChart },
    { selector: '#attendance-grid-island', component: AttendanceGrid },
    { selector: '#qr-scanner-island', component: QrScannerWidget },
    { selector: '#quotation-compare-island', component: QuotationCompareTable },
    { selector: '#invoice-pdf-island', component: InvoicePdfButton },
];

function parseDataset(dataset) {
    const props = {};
    for (const key in dataset) {
        let val = dataset[key];
        try {
            // Parse JSON if valid (arrays/objects/booleans/numbers)
            val = JSON.parse(val);
        } catch (e) {
            // keep raw string
        }
        props[key] = val;
    }
    return props;
}

function mountIslands() {
    islands.forEach(({ selector, component }) => {
        const elements = document.querySelectorAll(selector);
        elements.forEach((el) => {
            if (!el.dataset.vMounted) {
                const props = parseDataset(el.dataset);
                const app = createApp(component, props);
                app.use(pinia);
                app.mount(el);
                el.dataset.vMounted = 'true';
            }
        });
    });

    // Check for generic <div id="app"></div> if someone builds an SPA section
    const fullAppEl = document.getElementById('app');
    if (fullAppEl && !fullAppEl.dataset.vMounted) {
        const app = createApp({});
        app.use(pinia);
        // Register global components
        app.component('FtsButton', FtsButton);
        app.component('FtsCard', FtsCard);
        app.component('FtsBadge', FtsBadge);
        app.component('FtsTable', FtsTable);
        app.component('FtsModal', FtsModal);
        app.component('FtsInput', FtsInput);
        app.component('FtsAlert', FtsAlert);
        app.component('FtsPagination', FtsPagination);
        app.mount(fullAppEl);
        fullAppEl.dataset.vMounted = 'true';
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountIslands);
} else {
    mountIslands();
}

export { mountIslands, pinia };
