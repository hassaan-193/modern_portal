<template>
  <FtsBadge :variant="badgeVariant" :size="size">
    {{ statusText }}
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

const statusText = computed(() => {
  if (!props.status) return 'Pending';
  return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});

const badgeVariant = computed(() => {
  const s = (props.status || '').toLowerCase();
  if (['approved', 'completed', 'active'].includes(s)) return 'approved';
  if (['pending', 'submitted', 'review', 'in_review'].includes(s)) return 'pending';
  if (['rejected', 'cancelled', 'declined'].includes(s)) return 'rejected';
  if (['draft', 'new'].includes(s)) return 'draft';
  return 'default';
});
</script>
