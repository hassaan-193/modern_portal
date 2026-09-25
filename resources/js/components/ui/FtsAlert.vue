<template>
  <div v-if="visible" class="fts-alert" :class="`fts-alert-${variant}`">
    <div class="fts-alert-icon">
      <i :class="alertIcon"></i>
    </div>
    <div class="fts-alert-content">
      <h5 v-if="title" class="fts-alert-title">{{ title }}</h5>
      <div class="fts-alert-text">
        <slot>{{ message }}</slot>
      </div>
    </div>
    <button v-if="dismissible" class="fts-alert-close" @click="dismiss">
      &times;
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'info', // 'success' | 'warning' | 'danger' | 'info'
  },
  title: {
    type: String,
    default: '',
  },
  message: {
    type: String,
    default: '',
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
});

const visible = ref(true);

const alertIcon = computed(() => {
  switch (props.variant) {
    case 'success':
      return 'fa fa-check-circle';
    case 'warning':
      return 'fa fa-exclamation-triangle';
    case 'danger':
    case 'error':
      return 'fa fa-times-circle';
    case 'info':
    default:
      return 'fa fa-info-circle';
  }
});

const dismiss = () => {
  visible.value = false;
};
</script>

<style scoped>
.fts-alert {
  display: flex;
  align-items: flex-start;
  padding: 0.85rem 1rem;
  border-radius: 8px;
  border: 1px solid transparent;
  margin-bottom: 1rem;
  gap: 0.75rem;
}

.fts-alert-icon {
  font-size: 1.15rem;
  line-height: 1;
  margin-top: 0.1rem;
}

.fts-alert-content {
  flex: 1;
}

.fts-alert-title {
  font-size: 0.875rem;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
}

.fts-alert-text {
  font-size: 0.8125rem;
  line-height: 1.4;
}

.fts-alert-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  line-height: 1;
  cursor: pointer;
  opacity: 0.7;
}
.fts-alert-close:hover {
  opacity: 1;
}

.fts-alert-success {
  background-color: #f3faf7;
  border-color: #def7ec;
  color: #03543f;
}
.fts-alert-success .fts-alert-icon {
  color: #0e9f6e;
}

.fts-alert-warning {
  background-color: #fdfdea;
  border-color: #fef08a;
  color: #723b10;
}
.fts-alert-warning .fts-alert-icon {
  color: #d97706;
}

.fts-alert-danger,
.fts-alert-error {
  background-color: #fdf2f2;
  border-color: #fde8e8;
  color: #9b1c1c;
}
.fts-alert-danger .fts-alert-icon {
  color: #e02424;
}

.fts-alert-info {
  background-color: #f0f8ff;
  border-color: #e1effe;
  color: #1e429f;
}
.fts-alert-info .fts-alert-icon {
  color: #3f83f8;
}
</style>
