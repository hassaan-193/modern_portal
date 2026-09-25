<template>
  <div class="po-approval-actions">
    <div v-if="canApprove && isPending" class="d-flex align-items-center gap-2">
      <FtsButton
        variant="success"
        size="sm"
        icon="fa fa-check"
        :loading="loadingAction === 'approve'"
        @click="openConfirmModal('approve')"
      >
        Approve PO
      </FtsButton>
      <FtsButton
        variant="danger"
        size="sm"
        icon="fa fa-times"
        :loading="loadingAction === 'reject'"
        @click="openConfirmModal('reject')"
      >
        Reject
      </FtsButton>
    </div>

    <FtsModal
      v-model="showModal"
      :title="modalAction === 'approve' ? 'Approve Purchase Order' : 'Reject Purchase Order'"
    >
      <div class="p-1">
        <p>
          Are you sure you want to <strong>{{ modalAction }}</strong> Purchase Order
          <code>#{{ poNumber }}</code>?
        </p>

        <FtsInput
          v-if="modalAction === 'reject'"
          v-model="rejectionReason"
          label="Reason for Rejection"
          placeholder="Please explain why this order is being rejected..."
          required
          :error="reasonError"
        />

        <FtsInput
          v-else
          v-model="approvalRemarks"
          label="Approval Remarks (Optional)"
          placeholder="e.g. Approved per budget review..."
        />
      </div>

      <template #actions>
        <button class="btn btn-secondary btn-sm" @click="showModal = false">
          Cancel
        </button>
        <FtsButton
          :variant="modalAction === 'approve' ? 'success' : 'danger'"
          size="sm"
          :loading="submitting"
          @click="submitDecision"
        >
          Confirm {{ modalAction === 'approve' ? 'Approval' : 'Rejection' }}
        </FtsButton>
      </template>
    </FtsModal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import FtsButton from '../../ui/FtsButton.vue';
import FtsModal from '../../ui/FtsModal.vue';
import FtsInput from '../../ui/FtsInput.vue';
import { useApi } from '../../../composables/useApi';
import { useToast } from '../../../composables/useToast';

const props = defineProps({
  poId: {
    type: [Number, String],
    required: true,
  },
  poNumber: {
    type: String,
    default: '',
  },
  status: {
    type: String,
    default: 'Pending',
  },
  canApprove: {
    type: Boolean,
    default: true,
  },
  approveEndpoint: {
    type: String,
    default: '',
  },
  rejectEndpoint: {
    type: String,
  },
});

const emit = defineEmits(['statusUpdated']);

const api = useApi();
const toast = useToast();

const showModal = ref(false);
const modalAction = ref('approve');
const rejectionReason = ref('');
const approvalRemarks = ref('');
const reasonError = ref('');
const submitting = ref(false);
const loadingAction = ref(null);

const isPending = computed(() => {
  return ['pending', 'submitted', 'review'].includes((props.status || '').toLowerCase());
});

const openConfirmModal = (action) => {
  modalAction.value = action;
  rejectionReason.value = '';
  approvalRemarks.value = '';
  reasonError.value = '';
  showModal.value = true;
};

const submitDecision = async () => {
  if (modalAction.value === 'reject' && !rejectionReason.value.trim()) {
    reasonError.value = 'Rejection reason is required.';
    return;
  }

  submitting.value = true;
  loadingAction.value = modalAction.value;

  try {
    const url = modalAction.value === 'approve'
      ? (props.approveEndpoint || `/purchase-orders/${props.poId}/approve`)
      : (props.rejectEndpoint || `/purchase-orders/${props.poId}/reject`);

    const payload = {
      remarks: modalAction.value === 'approve' ? approvalRemarks.value : rejectionReason.value,
      action: modalAction.value,
    };

    const res = await api.post(url, payload);
    toast.success(`Purchase Order successfully ${modalAction.value}d!`);
    showModal.value = false;
    emit('statusUpdated', modalAction.value === 'approve' ? 'Approved' : 'Rejected');

    setTimeout(() => {
      window.location.reload();
    }, 800);
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || `Failed to ${modalAction.value} Purchase Order.`);
  } finally {
    submitting.value = false;
    loadingAction.value = null;
  }
};
</script>

<style scoped>
.gap-2 {
  gap: 0.5rem;
}
</style>
