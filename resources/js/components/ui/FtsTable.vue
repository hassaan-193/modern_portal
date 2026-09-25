<template>
  <div class="fts-table-wrapper">
    <div v-if="searchable" class="fts-table-toolbar">
      <div class="fts-table-search">
        <i class="fa fa-search fts-search-icon"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Filter table rows..."
          class="fts-table-search-input"
        />
        <button
          v-if="searchQuery"
          class="fts-clear-search"
          @click="searchQuery = ''"
        >
          &times;
        </button>
      </div>
      <div class="fts-table-count">
        Showing {{ filteredRows.length }} of {{ rows.length }} records
      </div>
    </div>

    <div class="fts-table-responsive">
      <table class="fts-table">
        <thead>
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              :style="{ width: col.width, textAlign: col.align || 'left' }"
              :class="{ 'is-sortable': col.sortable }"
              @click="col.sortable ? handleSort(col.key) : null"
            >
              <div class="fts-th-content" :style="{ justifyContent: col.align === 'right' ? 'flex-end' : (col.align === 'center' ? 'center' : 'flex-start') }">
                <span>{{ col.label }}</span>
                <span v-if="col.sortable" class="fts-sort-icon">
                  <i v-if="sortKey !== col.key" class="fa fa-sort text-muted"></i>
                  <i v-else-if="sortOrder === 'asc'" class="fa fa-sort-up"></i>
                  <i v-else class="fa fa-sort-down"></i>
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredRows.length === 0">
            <td :colspan="columns.length" class="fts-table-empty">
              <div class="fts-empty-state">
                <i class="fa fa-folder-open fts-empty-icon"></i>
                <p>{{ emptyMessage }}</p>
              </div>
            </td>
          </tr>
          <tr v-for="(row, idx) in paginatedRows" :key="row.id || idx" class="fts-table-row">
            <td
              v-for="col in columns"
              :key="col.key"
              :style="{ textAlign: col.align || 'left' }"
            >
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]" :index="idx">
                {{ row[col.key] ?? '-' }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination && totalPages > 1" class="fts-table-pagination">
      <button
        class="fts-page-btn"
        :disabled="currentPage === 1"
        @click="currentPage--"
      >
        <i class="fa fa-chevron-left"></i> Prev
      </button>
      <span class="fts-page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button
        class="fts-page-btn"
        :disabled="currentPage === totalPages"
        @click="currentPage++"
      >
        Next <i class="fa fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  rows: {
    type: Array,
    default: () => [],
  },
  searchable: {
    type: Boolean,
    default: false,
  },
  pagination: {
    type: Boolean,
    default: false,
  },
  pageSize: {
    type: Number,
    default: 10,
  },
  emptyMessage: {
    type: String,
    default: 'No records found.',
  },
});

const searchQuery = ref('');
const sortKey = ref('');
const sortOrder = ref('asc'); // 'asc' | 'desc'
const currentPage = ref(1);

const handleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const filteredRows = computed(() => {
  let list = [...props.rows];

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((row) => {
      return Object.values(row).some((val) => {
        if (val === null || val === undefined) return false;
        return String(val).toLowerCase().includes(q);
      });
    });
  }

  if (sortKey.value) {
    list.sort((a, b) => {
      let va = a[sortKey.value] ?? '';
      let vb = b[sortKey.value] ?? '';
      if (typeof va === 'number' && typeof vb === 'number') {
        return sortOrder.value === 'asc' ? va - vb : vb - va;
      }
      va = String(va).toLowerCase();
      vb = String(vb).toLowerCase();
      if (va < vb) return sortOrder.value === 'asc' ? -1 : 1;
      if (va > vb) return sortOrder.value === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return list;
});

const totalPages = computed(() => {
  if (!props.pagination) return 1;
  return Math.ceil(filteredRows.value.length / props.pageSize) || 1;
});

const paginatedRows = computed(() => {
  if (!props.pagination) return filteredRows.value;
  const start = (currentPage.value - 1) * props.pageSize;
  return filteredRows.value.slice(start, start + props.pageSize);
});
</script>

<style scoped>
.fts-table-wrapper {
  background: #ffffff;
  border-radius: 8px;
  overflow: hidden;
}

.fts-table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.fts-table-search {
  position: relative;
  display: flex;
  align-items: center;
  width: 260px;
}

.fts-search-icon {
  position: absolute;
  left: 0.75rem;
  color: #9ca3af;
  font-size: 0.85rem;
}

.fts-table-search-input {
  width: 100%;
  padding: 0.4rem 2rem 0.4rem 2rem;
  font-size: 0.8125rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
}

.fts-table-search-input:focus {
  border-color: #800000;
  box-shadow: 0 0 0 2px rgba(128, 0, 0, 0.15);
}

.fts-clear-search {
  position: absolute;
  right: 0.5rem;
  background: none;
  border: none;
  font-size: 1.1rem;
  color: #9ca3af;
  cursor: pointer;
}

.fts-table-count {
  font-size: 0.8125rem;
  color: #6b7280;
}

.fts-table-responsive {
  width: 100%;
  overflow-x: auto;
}

.fts-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.fts-table th {
  background-color: #f9fafb;
  color: #374151;
  font-weight: 600;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  user-select: none;
}

.fts-table th.is-sortable {
  cursor: pointer;
}

.fts-table th.is-sortable:hover {
  background-color: #f3f4f6;
}

.fts-th-content {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.fts-sort-icon {
  font-size: 0.75rem;
}

.fts-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  color: #1f2937;
}

.fts-table-row:hover {
  background-color: #fbfbfb;
}

.fts-table-empty {
  text-align: center;
  padding: 2.5rem 1rem !important;
}

.fts-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: #9ca3af;
}

.fts-empty-icon {
  font-size: 2rem;
  color: #d1d5db;
}

.fts-table-pagination {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid #e5e7eb;
  background-color: #fafafa;
}

.fts-page-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.75rem;
  font-size: 0.8125rem;
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  color: #374151;
}

.fts-page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.fts-page-btn:hover:not(:disabled) {
  background-color: #f3f4f6;
}

.fts-page-info {
  font-size: 0.8125rem;
  color: #6b7280;
}
</style>
