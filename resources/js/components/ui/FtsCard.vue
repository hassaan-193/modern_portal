<template>
  <div class="fts-card" :class="{ 'has-accent': accent }">
    <div v-if="$slots.header || title" class="fts-card-header">
      <div class="fts-card-title-group">
        <i v-if="icon" :class="[icon, 'fts-card-icon']"></i>
        <h3 v-if="title" class="fts-card-title">{{ title }}</h3>
        <p v-if="subtitle" class="fts-card-subtitle">{{ subtitle }}</p>
      </div>
      <div class="fts-card-actions">
        <slot name="actions"></slot>
      </div>
    </div>
    <div class="fts-card-body" :class="{ 'no-padding': noPadding }">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="fts-card-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: '',
  },
  accent: {
    type: Boolean,
    default: false,
  },
  noPadding: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped>
.fts-card {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
  margin-bottom: 1.25rem;
  overflow: hidden;
  transition: box-shadow 0.2s ease;
}

.fts-card.has-accent {
  border-top: 3px solid #800000;
}

.fts-card-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #fafafa;
}

.fts-card-title-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.fts-card-icon {
  color: #800000;
  font-size: 1.1rem;
}

.fts-card-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.fts-card-subtitle {
  margin: 0;
  font-size: 0.8125rem;
  color: #6b7280;
}

.fts-card-body {
  padding: 1.25rem;
}

.fts-card-body.no-padding {
  padding: 0;
}

.fts-card-footer {
  padding: 0.75rem 1.25rem;
  background-color: #f9fafb;
  border-top: 1px solid #f3f4f6;
  display: flex;
  justify-content: flex-end;
}
</style>
