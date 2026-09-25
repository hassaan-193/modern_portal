<template>
  <span :class="badgeClasses">
    <span v-if="dot" class="fts-badge-dot"></span>
    <slot>{{ label }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: {
    type: String,
    default: '',
  },
  variant: {
    type: String,
    default: 'default', // 'approved' | 'pending' | 'rejected' | 'draft' | 'info' | 'maroon'
  },
  size: {
    type: String,
    default: 'md', // 'sm' | 'md'
  },
  dot: {
    type: Boolean,
    default: true,
  },
});

const badgeClasses = computed(() => {
  return [
    'fts-badge',
    `fts-badge-${props.variant.toLowerCase()}`,
    `fts-badge-${props.size}`,
  ].join(' ');
});
</script>

<style scoped>
.fts-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-weight: 500;
  border-radius: 9999px;
  line-height: 1;
  text-transform: capitalize;
  white-space: nowrap;
}

.fts-badge-sm {
  padding: 0.2rem 0.5rem;
  font-size: 0.75rem;
}

.fts-badge-md {
  padding: 0.3rem 0.75rem;
  font-size: 0.8125rem;
}

.fts-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}

/* Status: Approved / Completed / Success */
.fts-badge-approved,
.fts-badge-success {
  background-color: #def7ec;
  color: #03543f;
}
.fts-badge-approved .fts-badge-dot,
.fts-badge-success .fts-badge-dot {
  background-color: #0e9f6e;
}

/* Status: Pending / Under Review / Warning */
.fts-badge-pending,
.fts-badge-warning {
  background-color: #fef08a;
  color: #854d0e;
}
.fts-badge-pending .fts-badge-dot,
.fts-badge-warning .fts-badge-dot {
  background-color: #d97706;
}

/* Status: Rejected / Cancelled / Danger */
.fts-badge-rejected,
.fts-badge-danger {
  background-color: #fde8e8;
  color: #9b1c1c;
}
.fts-badge-rejected .fts-badge-dot,
.fts-badge-danger .fts-badge-dot {
  background-color: #e02424;
}

/* Status: Draft / Info / Primary */
.fts-badge-draft,
.fts-badge-info,
.fts-badge-primary {
  background-color: #e1effe;
  color: #1e429f;
}
.fts-badge-draft .fts-badge-dot,
.fts-badge-info .fts-badge-dot,
.fts-badge-primary .fts-badge-dot {
  background-color: #3f83f8;
}

/* Status: Maroon / Brand */
.fts-badge-maroon {
  background-color: #fce8e8;
  color: #800000;
}
.fts-badge-maroon .fts-badge-dot {
  background-color: #800000;
}

/* Status: Default / Neutral */
.fts-badge-default {
  background-color: #f3f4f6;
  color: #374151;
}
.fts-badge-default .fts-badge-dot {
  background-color: #9ca3af;
}
</style>
