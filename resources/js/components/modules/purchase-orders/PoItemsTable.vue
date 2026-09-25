<template>
  <div class="po-items-container">
    <div class="po-items-header">
      <div class="po-items-title">
        <i class="fa fa-boxes text-maroon mr-2"></i>
        <span>Purchase Order Line Items</span>
        <span class="badge badge-secondary ml-2">{{ parsedItems.length }} items</span>
      </div>
      <div class="po-items-search">
        <input
          v-model="searchFilter"
          type="text"
          placeholder="Filter materials or codes..."
          class="form-control form-control-sm"
        />
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width: 50px;" class="text-center">#</th>
            <th v-if="showItemCode" style="width: 140px;">Item Code</th>
            <th>Material / Description</th>
            <th style="width: 110px;" class="text-right">Qty</th>
            <th v-if="!isLumpSum" style="width: 150px;" class="text-right">Cost Per Unit ({{ currency }})</th>
            <th v-if="!isLumpSum" style="width: 160px;" class="text-right">Total Cost ({{ currency }})</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredItems.length === 0">
            <td :colspan="columnSpan" class="text-center text-muted py-4">
              <i class="fa fa-info-circle mr-1"></i> No items found matching the filter.
            </td>
          </tr>
          <tr v-for="(item, idx) in filteredItems" :key="idx">
            <td class="text-center text-muted">{{ idx + 1 }}</td>
            <td v-if="showItemCode" class="font-weight-bold">
              <code>{{ item.item_code || '-' }}</code>
            </td>
            <td>
              <div class="font-weight-500">{{ item.material_name || '-' }}</div>
              <small v-if="item.description" class="text-muted">{{ item.description }}</small>
            </td>
            <td class="text-right font-weight-bold">
              {{ item.quantity ?? '-' }}
            </td>
            <td v-if="!isLumpSum" class="text-right">
              {{ formatNumber(item.cost) }}
            </td>
            <td v-if="!isLumpSum" class="text-right font-weight-bold text-dark">
              {{ formatNumber(item.total || (item.cost * item.quantity)) }}
            </td>
          </tr>
        </tbody>
        <tfoot class="bg-light">
          <tr v-if="isLumpSum">
            <td :colspan="showItemCode ? 3 : 2" class="text-right font-weight-bold">
              Lump-Sum Order Total:
            </td>
            <td :colspan="2" class="text-right font-weight-bold text-maroon" style="font-size: 1.05rem;">
              {{ formatNumber(lumpSumTotal) }} {{ currency }}
            </td>
          </tr>
          <tr v-else>
            <td :colspan="showItemCode ? 4 : 3" class="text-right font-weight-bold">
              Total Estimated ({{ currency }}):
            </td>
            <td class="text-right font-weight-bold text-maroon" style="font-size: 1.05rem;">
              {{ formatNumber(computedGrandTotal) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  items: {
    type: [Array, String],
    default: () => [],
  },
  isLumpSum: {
    type: Boolean,
    default: false,
  },
  showItemCode: {
    type: Boolean,
    default: true,
  },
  lumpSumTotal: {
    type: [Number, String],
    default: 0,
  },
  currency: {
    type: String,
    default: 'AED',
  },
});

const searchFilter = ref('');

const parsedItems = computed(() => {
  if (Array.isArray(props.items)) return props.items;
  if (typeof props.items === 'string') {
    try {
      const parsed = JSON.parse(props.items);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }
  return [];
});

const filteredItems = computed(() => {
  if (!searchFilter.value.trim()) return parsedItems.value;
  const q = searchFilter.value.toLowerCase();
  return parsedItems.value.filter((item) => {
    return (
      (item.material_name && item.material_name.toLowerCase().includes(q)) ||
      (item.item_code && item.item_code.toLowerCase().includes(q)) ||
      (item.description && item.description.toLowerCase().includes(q))
    );
  });
});

const columnSpan = computed(() => {
  let count = 3; // #, Material, Qty
  if (props.showItemCode) count += 1;
  if (!props.isLumpSum) count += 2;
  return count;
});

const computedGrandTotal = computed(() => {
  return parsedItems.value.reduce((acc, it) => {
    const total = parseFloat(it.total) || (parseFloat(it.cost) * parseFloat(it.quantity)) || 0;
    return acc + total;
  }, 0);
});

const formatNumber = (val) => {
  if (val === null || val === undefined || val === '') return '-';
  const num = parseFloat(val);
  if (isNaN(num)) return '-';
  return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<style scoped>
.po-items-container {
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  overflow: hidden;
  margin-top: 0.5rem;
}

.po-items-header {
  padding: 0.65rem 1rem;
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.po-items-title {
  font-weight: 600;
  font-size: 0.95rem;
  color: #343a40;
  display: flex;
  align-items: center;
}

.text-maroon {
  color: #800000;
}

.po-items-search {
  width: 220px;
}

.font-weight-500 {
  font-weight: 500;
}
</style>
