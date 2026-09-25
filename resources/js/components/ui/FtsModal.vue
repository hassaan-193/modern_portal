<template>
  <Teleport to="body">
    <Transition name="fts-fade">
      <div v-if="modelValue" class="fts-modal-backdrop" @click="handleBackdropClick">
        <div class="fts-modal-dialog" :style="{ maxWidth }" @click.stop>
          <div class="fts-modal-header">
            <h4 class="fts-modal-title">
              <slot name="title">{{ title }}</slot>
            </h4>
            <button class="fts-modal-close" @click="close">&times;</button>
          </div>
          <div class="fts-modal-body">
            <slot></slot>
          </div>
          <div v-if="$slots.actions" class="fts-modal-footer">
            <slot name="actions">
              <button class="btn btn-secondary btn-sm" @click="close">Cancel</button>
            </slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirmation',
  },
  maxWidth: {
    type: String,
    default: '520px',
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['update:modelValue', 'close']);

const close = () => {
  emit('update:modelValue', false);
  emit('close');
};

const handleBackdropClick = () => {
  if (props.closeOnBackdrop) {
    close();
  }
};
</script>

<style scoped>
.fts-modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(17, 24, 39, 0.6);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.fts-modal-dialog {
  width: 100%;
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  animation: ftsModalIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.fts-modal-header {
  padding: 1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f3f4f6;
}

.fts-modal-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #111827;
}

.fts-modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: #9ca3af;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
}
.fts-modal-close:hover {
  color: #111827;
  background: #f3f4f6;
}

.fts-modal-body {
  padding: 1.25rem;
  font-size: 0.875rem;
  color: #4b5563;
  max-height: calc(85vh - 120px);
  overflow-y: auto;
}

.fts-modal-footer {
  padding: 0.85rem 1.25rem;
  background-color: #f9fafb;
  border-top: 1px solid #f3f4f6;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.fts-fade-enter-active,
.fts-fade-leave-active {
  transition: opacity 0.2s ease;
}

.fts-fade-enter-from,
.fts-fade-leave-to {
  opacity: 0;
}

@keyframes ftsModalIn {
  from {
    transform: scale(0.96) translateY(8px);
    opacity: 0;
  }
  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}
</style>
