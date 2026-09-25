<template>
  <div class="pending-attendance-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 font-weight-bold">
        <i class="fa fa-user-clock text-maroon mr-2"></i>
        Pending Attendance Approvals
      </h5>
      <span class="badge badge-warning">{{ pendingItems.length }} pending</span>
    </div>

    <div v-if="pendingItems.length === 0" class="text-center py-4 text-muted">
      <i class="fa fa-check-double text-success fa-2x mb-2"></i>
      <p class="mb-0">All attendance records are up to date!</p>
    </div>

    <div v-else class="list-group list-group-flush">
      <div
        v-for="item in pendingItems"
        :key="item.id"
        class="list-group-item d-flex justify-content-between align-items-center px-0 py-3"
      >
        <div>
          <div class="font-weight-bold">{{ item.staffName }}</div>
          <div class="small text-muted">
            <span class="mr-3"><i class="fa fa-calendar-day mr-1"></i>{{ item.date }}</span>
            <span><i class="fa fa-clock mr-1"></i>{{ item.time }} ({{ item.type }})</span>
          </div>
          <div v-if="item.reason" class="small text-info mt-1">
            <em>Note: {{ item.reason }}</em>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button
            class="btn btn-sm btn-success"
            :disabled="processingId === item.id"
            @click="handleAction(item.id, 'approve')"
          >
            <i class="fa fa-check mr-1"></i> Approve
          </button>
          <button
            class="btn btn-sm btn-outline-danger"
            :disabled="processingId === item.id"
            @click="handleAction(item.id, 'reject')"
          >
            <i class="fa fa-times mr-1"></i> Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useToast } from '../../../composables/useToast';

const props = defineProps({
  items: {
    type: Array,
    default: () => [
      { id: 101, staffName: 'Tariq Mehmood', date: '2026-09-24', time: '08:15 AM', type: 'Late Arrival', reason: 'Metro delay' },
      { id: 102, staffName: 'Bilal Hassan', date: '2026-09-24', time: '05:30 PM', type: 'Manual Checkout', reason: 'Field visit to Site B' },
    ],
  },
});

const toast = useToast();
const pendingItems = ref([...props.items]);
const processingId = ref(null);

const handleAction = async (id, action) => {
  processingId.value = id;
  setTimeout(() => {
    pendingItems.value = pendingItems.value.filter(i => i.id !== id);
    processingId.value = null;
    toast.success(`Record #${id} ${action}d successfully.`);
  }, 400);
};
</script>

<style scoped>
.pending-attendance-card {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  padding: 1.25rem;
}

.text-maroon {
  color: #800000;
}

.gap-2 {
  gap: 0.5rem;
}
</style>
