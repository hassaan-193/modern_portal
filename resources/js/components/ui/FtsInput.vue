<template>
  <div class="fts-form-group" :class="{ 'has-error': !!error }">
    <label v-if="label" class="fts-label">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <div class="fts-input-wrapper">
      <i v-if="icon" :class="[icon, 'fts-input-icon']"></i>
      <input
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        class="fts-input"
        :class="{ 'has-icon': !!icon }"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </div>
    <span v-if="error" class="fts-error-msg">{{ error }}</span>
    <span v-else-if="hint" class="fts-hint-msg">{{ hint }}</span>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
.fts-form-group {
  margin-bottom: 1rem;
}

.fts-label {
  display: block;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.35rem;
}

.fts-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.fts-input-icon {
  position: absolute;
  left: 0.75rem;
  color: #9ca3af;
  font-size: 0.875rem;
}

.fts-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  line-height: 1.25;
  color: #1f2937;
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.fts-input.has-icon {
  padding-left: 2.25rem;
}

.fts-input:focus {
  border-color: #800000;
  box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.15);
}

.fts-input:disabled {
  background-color: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
}

.fts-form-group.has-error .fts-input {
  border-color: #e02424;
}

.fts-form-group.has-error .fts-input:focus {
  box-shadow: 0 0 0 3px rgba(224, 36, 36, 0.15);
}

.fts-error-msg {
  display: block;
  font-size: 0.75rem;
  color: #e02424;
  margin-top: 0.25rem;
}

.fts-hint-msg {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.25rem;
}
</style>
