<template>
  <div class="fts-pagination">
    <button
      class="fts-page-btn"
      :disabled="currentPage <= 1"
      @click="setPage(currentPage - 1)"
    >
      <i class="fa fa-chevron-left"></i>
    </button>

    <template v-for="page in pages" :key="page">
      <span v-if="page === '...'" class="fts-page-ellipsis">...</span>
      <button
        v-else
        class="fts-page-btn"
        :class="{ 'is-active': page === currentPage }"
        @click="setPage(page)"
      >
        {{ page }}
      </button>
    </template>

    <button
      class="fts-page-btn"
      :disabled="currentPage >= totalPages"
      @click="setPage(currentPage + 1)"
    >
      <i class="fa fa-chevron-right"></i>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
  },
  totalPages: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(['update:currentPage', 'page-change']);

const setPage = (p) => {
  if (p >= 1 && p <= props.totalPages && p !== props.currentPage) {
    emit('update:currentPage', p);
    emit('page-change', p);
  }
};

const pages = computed(() => {
  const current = props.currentPage;
  const total = props.totalPages;
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }
  if (current <= 4) {
    return [1, 2, 3, 4, 5, '...', total];
  }
  if (current >= total - 3) {
    return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
  }
  return [1, '...', current - 1, current, current + 1, '...', total];
});
</script>

<style scoped>
.fts-pagination {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.fts-page-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2rem;
  height: 2rem;
  padding: 0 0.4rem;
  font-size: 0.8125rem;
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}

.fts-page-btn:hover:not(:disabled) {
  background-color: #f3f4f6;
  border-color: #9ca3af;
}

.fts-page-btn.is-active {
  background-color: #800000;
  border-color: #800000;
  color: #ffffff;
  font-weight: 600;
}

.fts-page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.fts-page-ellipsis {
  padding: 0 0.35rem;
  color: #9ca3af;
}
</style>
