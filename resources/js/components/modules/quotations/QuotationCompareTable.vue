<template>
  <div class="quotation-compare-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="mb-0 font-weight-bold">
          <i class="fa fa-balance-scale text-maroon mr-2"></i>
          Vendor Quotation Comparison
        </h5>
        <small class="text-muted">Compare vendor pricing, terms, and delivery schedules</small>
      </div>
      <span class="badge badge-info">{{ vendors.length }} Vendors Quoted</span>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-sm text-center compare-table">
        <thead class="thead-light">
          <tr>
            <th class="text-left item-col">Item / Specification</th>
            <th v-for="v in vendors" :key="v.id" class="vendor-header">
              <div class="font-weight-bold">{{ v.name }}</div>
              <small class="text-muted">Lead Time: {{ v.leadTime || '7 days' }}</small>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, idx) in lineItems" :key="idx">
            <td class="text-left font-weight-500 item-col">
              {{ item.title }}
              <div class="small text-muted">Qty: {{ item.qty }}</div>
            </td>
            <td
              v-for="v in vendors"
              :key="v.id"
              :class="{ 'lowest-rate': isLowest(item, v.id) }"
            >
              <div class="font-weight-bold">{{ formatPrice(getPrice(item, v.id)) }}</div>
              <span v-if="isLowest(item, v.id)" class="badge badge-success small mt-1">Lowest</span>
            </td>
          </tr>
        </tbody>
        <tfoot class="bg-light">
          <tr>
            <th class="text-left font-weight-bold item-col">Total Comparison:</th>
            <th
              v-for="v in vendors"
              :key="v.id"
              class="vendor-total"
              :class="{ 'lowest-total text-success': isOverallLowest(v.id) }"
            >
              <div>{{ formatPrice(calcTotal(v.id)) }}</div>
              <span v-if="isOverallLowest(v.id)" class="badge badge-success">Best Offer</span>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  comparisonData: {
    type: Object,
    default: () => ({
      vendors: [
        { id: 1, name: 'Gulf Building Supplies', leadTime: '3-5 days' },
        { id: 2, name: 'Emirates Steel & Metal', leadTime: '7-10 days' },
        { id: 3, name: 'Al-Futtaim Industrial', leadTime: '5 days' },
      ],
      items: [
        {
          title: 'Structural Steel Beams (HEB 200)',
          qty: 20,
          quotes: { 1: 1450, 2: 1380, 3: 1520 },
        },
        {
          title: 'Reinforced Mesh Sheets (A393)',
          qty: 50,
          quotes: { 1: 120, 2: 135, 3: 115 },
        },
        {
          title: 'Standard Ready-Mix Concrete C35',
          qty: 15,
          quotes: { 1: 290, 2: 310, 3: 285 },
        },
      ],
    }),
  },
});

const vendors = computed(() => props.comparisonData?.vendors || []);
const lineItems = computed(() => props.comparisonData?.items || []);

const getPrice = (item, vendorId) => {
  return item.quotes?.[vendorId] || 0;
};

const isLowest = (item, vendorId) => {
  const prices = Object.values(item.quotes || {});
  if (!prices.length) return false;
  const min = Math.min(...prices);
  return item.quotes?.[vendorId] === min;
};

const calcTotal = (vendorId) => {
  return lineItems.value.reduce((acc, item) => {
    const rate = item.quotes?.[vendorId] || 0;
    return acc + (rate * item.qty);
  }, 0);
};

const isOverallLowest = (vendorId) => {
  const totals = vendors.value.map(v => calcTotal(v.id));
  const min = Math.min(...totals);
  return calcTotal(vendorId) === min;
};

const formatPrice = (val) => {
  return `AED ${(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};
</script>

<style scoped>
.quotation-compare-card {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.text-maroon {
  color: #800000;
}

.item-col {
  min-width: 220px;
}

.vendor-header {
  min-width: 160px;
}

.lowest-rate {
  background-color: #f3faf7 !important;
}

.lowest-total {
  background-color: #def7ec;
  font-size: 1.05rem;
}
</style>
