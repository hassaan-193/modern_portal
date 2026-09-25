<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <svg
      v-if="loading"
      class="fts-spinner"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      ></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <i v-else-if="icon" :class="[icon, 'mr-1']"></i>
    <span>
      <slot></slot>
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'maroon', // 'maroon' | 'primary' | 'secondary' | 'success' | 'danger' | 'outline'
  },
  size: {
    type: String,
    default: 'md', // 'sm' | 'md' | 'lg'
  },
  type: {
    type: String,
    default: 'button',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
  const base = ['fts-btn', `fts-btn-${props.variant}`, `fts-btn-${props.size}`];
  if (props.loading) base.push('is-loading');
  if (props.disabled) base.push('is-disabled');
  return base.join(' ');
});

const handleClick = (e) => {
  if (!props.disabled && !props.loading) {
    emit('click', e);
  }
};
</script>

<style scoped>
.fts-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid transparent;
  gap: 0.4rem;
  line-height: 1.25;
  outline: none;
}

.fts-btn:focus-visible {
  box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.25);
}

.fts-btn-sm {
  padding: 0.35rem 0.65rem;
  font-size: 0.8125rem;
}

.fts-btn-md {
  padding: 0.45rem 1rem;
  font-size: 0.875rem;
}

.fts-btn-lg {
  padding: 0.65rem 1.35rem;
  font-size: 1rem;
}

.fts-btn-maroon {
  background-color: #800000;
  color: #ffffff;
  border-color: #6a0000;
}
.fts-btn-maroon:hover:not(:disabled) {
  background-color: #6a0000;
  box-shadow: 0 4px 6px -1px rgba(128, 0, 0, 0.2);
}

.fts-btn-primary {
  background-color: #1a56db;
  color: #ffffff;
  border-color: #1e429f;
}
.fts-btn-primary:hover:not(:disabled) {
  background-color: #1e429f;
}

.fts-btn-secondary {
  background-color: #f3f4f6;
  color: #374151;
  border-color: #d1d5db;
}
.fts-btn-secondary:hover:not(:disabled) {
  background-color: #e5e7eb;
}

.fts-btn-success {
  background-color: #057a55;
  color: #ffffff;
  border-color: #046c4e;
}
.fts-btn-success:hover:not(:disabled) {
  background-color: #046c4e;
}

.fts-btn-danger {
  background-color: #e02424;
  color: #ffffff;
  border-color: #c81e1e;
}
.fts-btn-danger:hover:not(:disabled) {
  background-color: #c81e1e;
}

.fts-btn-outline {
  background-color: transparent;
  color: #4b5563;
  border-color: #d1d5db;
}
.fts-btn-outline:hover:not(:disabled) {
  background-color: #f9fafb;
  border-color: #9ca3af;
}

.fts-btn:disabled,
.fts-btn.is-disabled {
  opacity: 0.6;
  cursor: not-allowed;
  filter: grayscale(20%);
}

.fts-spinner {
  animation: spin 1s linear infinite;
  height: 1rem;
  width: 1rem;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
