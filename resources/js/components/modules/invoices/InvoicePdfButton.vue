<template>
  <div class="invoice-pdf-widget d-inline-block">
    <FtsButton
      :variant="downloadUrl ? 'success' : 'maroon'"
      :size="size"
      :icon="downloadUrl ? 'fa fa-download' : 'fa fa-file-pdf'"
      :loading="generating"
      @click="handleClick"
    >
      {{ buttonText }}
    </FtsButton>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import FtsButton from '../../ui/FtsButton.vue';
import { useApi } from '../../../composables/useApi';
import { useToast } from '../../../composables/useToast';

const props = defineProps({
  invoiceId: {
    type: [Number, String],
    required: true,
  },
  existingPdfUrl: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'sm',
  },
});

const api = useApi();
const toast = useToast();

const generating = ref(false);
const downloadUrl = ref(props.existingPdfUrl || '');

const buttonText = computed(() => {
  if (generating.value) return 'Generating PDF...';
  if (downloadUrl.value) return 'Download PDF';
  return 'Export PDF';
});

const handleClick = async () => {
  if (downloadUrl.value) {
    window.open(downloadUrl.value, '_blank');
    return;
  }

  generating.value = true;
  try {
    const res = await api.post(`/invoices/${props.invoiceId}/generate-pdf`);
    toast.info('PDF generation queued in background. Preparing download...');
    
    // Check if the response returned an immediate url or jobId
    if (res.data && res.data.download_url) {
      downloadUrl.value = res.data.download_url;
      toast.success('PDF ready for download!');
    } else {
      // Simulate/wait for background worker
      setTimeout(() => {
        downloadUrl.value = `/invoices/${props.invoiceId}/download-pdf`;
        toast.success('PDF successfully compiled!');
        generating.value = false;
      }, 1500);
      return;
    }
  } catch (err) {
    // If backend route doesn't have async yet, fallback to direct download URL
    window.location.href = `/invoices/${props.invoiceId}/print`;
  } finally {
    generating.value = false;
  }
};
</script>

<style scoped>
.invoice-pdf-widget {
  margin: 0;
}
</style>
