<template>
  <div class="booking-tracker">
    <div class="tracker-steps">
      <div
        v-for="(st, idx) in stages"
        :key="st.key"
        class="tracker-step"
        :class="{
          'is-done': idx < activeStageIndex,
          'is-current': idx === activeStageIndex,
        }"
      >
        <div class="tracker-icon">
          <i v-if="idx < activeStageIndex" class="fa fa-check"></i>
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <div class="tracker-label">{{ st.label }}</div>
        <div v-if="idx < stages.length - 1" class="tracker-bar"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: 'Pending',
  },
});

const stages = [
  { key: 'created', label: 'Booking Created' },
  { key: 'verified', label: 'Accounts Verified' },
  { key: 'issued', label: 'Payment Prepared' },
  { key: 'paid', label: 'Disbursed / Paid' },
];

const activeStageIndex = computed(() => {
  const s = (props.status || '').toLowerCase();
  if (['draft', 'new'].includes(s)) return 0;
  if (['pending', 'verified', 'review'].includes(s)) return 1;
  if (['prepared', 'issued', 'cheque_issued'].includes(s)) return 2;
  if (['paid', 'completed', 'cleared'].includes(s)) return 3;
  return 1;
});
</script>

<style scoped>
.booking-tracker {
  padding: 0.75rem 0;
}

.tracker-steps {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.tracker-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
}

.tracker-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: #e5e7eb;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.8125rem;
  z-index: 2;
}

.tracker-step.is-done .tracker-icon {
  background: #057a55;
  color: #ffffff;
}

.tracker-step.is-current .tracker-icon {
  background: #800000;
  color: #ffffff;
  box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.15);
}

.tracker-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #4b5563;
  margin-top: 0.35rem;
  text-align: center;
}

.tracker-bar {
  position: absolute;
  top: 1rem;
  left: 50%;
  width: 100%;
  height: 2px;
  background-color: #e5e7eb;
  z-index: 1;
}

.tracker-step.is-done .tracker-bar {
  background-color: #057a55;
}
</style>
