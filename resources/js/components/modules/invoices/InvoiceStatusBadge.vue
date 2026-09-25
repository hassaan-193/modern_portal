<template>
  <FtsBadge :variant="badgeVariant" :size="size">
    {{ statusLabel }}
  </FtsBadge>
</template>

<script setup>
import { computed } from 'vue';
import FtsBadge from '../../ui/FtsBadge.vue';

const props = defineProps({
  status: {
    type: String,
    default: 'Draft',
  },
  size: {
    type: String,
    default: 'md',
  },
});

const statusLabel = computed(() => {
  if (!props.status) return 'Draft';
  return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});

const badgeVariant = computed(() => {
  const s = (props.status || '').toLowerCase();
  if (['paid', 'settled', 'cleared'].includes(s)) return 'approved';
  if (['issued', 'sent', 'unpaid', 'due'].includes(s)) return 'pending';
  if (['overdue', 'cancelled', 'void'].includes(s)) return 'rejected';
  if (['draft', 'created'].includes(s)) return 'draft';
  return 'default';
});
</script>
