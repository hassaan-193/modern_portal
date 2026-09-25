<template>
  <div class="po-timeline">
    <div class="timeline-steps">
      <div
        v-for="(step, idx) in steps"
        :key="step.key"
        class="timeline-step"
        :class="{
          'is-complete': idx < currentStepIndex,
          'is-active': idx === currentStepIndex,
          'is-failed': isRejected && idx === currentStepIndex,
        }"
      >
        <div class="timeline-step-badge">
          <i v-if="idx < currentStepIndex" class="fa fa-check"></i>
          <i v-else-if="isRejected && idx === currentStepIndex" class="fa fa-times"></i>
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <div class="timeline-step-content">
          <div class="timeline-step-title">{{ step.label }}</div>
          <div v-if="step.date" class="timeline-step-date">{{ step.date }}</div>
          <div v-else-if="idx === currentStepIndex" class="timeline-step-status">
            {{ isRejected ? 'Rejected' : 'Current Status' }}
          </div>
        </div>
        <div v-if="idx < steps.length - 1" class="timeline-step-connector"></div>
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
  createdDate: {
    type: String,
    default: '',
  },
  submittedDate: {
    type: String,
    default: '',
  },
  approvedDate: {
    type: String,
    default: '',
  },
});

const isRejected = computed(() => {
  return (props.status || '').toLowerCase() === 'rejected';
});

const steps = computed(() => {
  return [
    { key: 'draft', label: 'Order Drafted', date: props.createdDate },
    { key: 'review', label: 'Under Review', date: props.submittedDate },
    { key: 'approved', label: isRejected.value ? 'Order Rejected' : 'Approved & Issued', date: props.approvedDate },
  ];
});

const currentStepIndex = computed(() => {
  const s = (props.status || '').toLowerCase();
  if (['draft', 'new'].includes(s)) return 0;
  if (['pending', 'submitted', 'review'].includes(s)) return 1;
  if (['approved', 'completed', 'active', 'rejected'].includes(s)) return 2;
  return 1;
});
</script>

<style scoped>
.po-timeline {
  padding: 1rem 0.5rem;
}

.timeline-steps {
  display: flex;
  align-items: flex-start;
  position: relative;
  justify-content: space-between;
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
  text-align: center;
}

.timeline-step-badge {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 50%;
  background: #f3f4f6;
  border: 2px solid #d1d5db;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  z-index: 2;
  transition: all 0.2s ease;
}

.timeline-step.is-complete .timeline-step-badge {
  background: #057a55;
  border-color: #057a55;
  color: #ffffff;
}

.timeline-step.is-active .timeline-step-badge {
  background: #800000;
  border-color: #800000;
  color: #ffffff;
  box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.15);
}

.timeline-step.is-failed .timeline-step-badge {
  background: #e02424;
  border-color: #e02424;
  color: #ffffff;
  box-shadow: 0 0 0 4px rgba(224, 36, 36, 0.15);
}

.timeline-step-content {
  margin-top: 0.5rem;
}

.timeline-step-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #1f2937;
}

.timeline-step-date {
  font-size: 0.75rem;
  color: #6b7280;
}

.timeline-step-status {
  font-size: 0.75rem;
  color: #800000;
  font-weight: 500;
}

.timeline-step-connector {
  position: absolute;
  top: 1.125rem;
  left: 50%;
  width: 100%;
  height: 2px;
  background-color: #e5e7eb;
  z-index: 1;
}

.timeline-step.is-complete .timeline-step-connector {
  background-color: #057a55;
}
</style>
