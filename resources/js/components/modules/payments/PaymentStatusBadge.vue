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
    default: 'Pending',
  },
  size: {
    type: String,
    default: 'md',
  },
});

const statusLabel = computed(() => {
  if (!props.status) return 'Pending';
  return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});

const badgeVariant = computed(() => {
  const s = (props.status || '').toLowerCase();
  if (['paid', 'completed', 'approved', 'cleared'].includes(s)) return 'approved';
  if (['pending', 'processing', 'scheduled'].includes(s)) return 'pending';
  if (['rejected', 'failed', 'bounced', 'cancelled'].includes(s)) return 'rejected';
  if (['draft', 'hold'].includes(s)) return 'draft';
  return 'default';
});
</script>
