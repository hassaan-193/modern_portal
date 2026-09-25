<template>
  <div class="nav-item dropdown notification-bell-wrapper" ref="dropdownRef">
    <a
      class="nav-link bell-link"
      href="javascript:void(0)"
      @click="toggleDropdown"
      :aria-expanded="isOpen"
    >
      <i class="far fa-bell text-maroon bell-icon"></i>
      <span
        v-if="unreadCount > 0"
        class="badge badge-danger navbar-badge bell-badge"
      >
        {{ unreadCount }}
      </span>
    </a>

    <div
      v-if="isOpen"
      class="dropdown-menu dropdown-menu-lg dropdown-menu-right show notification-dropdown"
    >
      <div class="dropdown-header d-flex justify-content-between align-items-center">
        <strong>{{ unreadCount }} Unread Notifications</strong>
        <a
          v-if="unreadCount > 0"
          href="javascript:void(0)"
          class="small text-maroon"
          @click="markAllAsRead"
        >
          Mark all as read
        </a>
      </div>
      <div class="dropdown-divider"></div>

      <div class="notification-items-scroll">
        <div v-if="notificationsList.length === 0" class="text-center py-3 text-muted small">
          <i class="fa fa-bell-slash mr-1"></i> No unread notifications
        </div>

        <a
          v-for="item in notificationsList"
          :key="item.id"
          href="javascript:void(0)"
          class="dropdown-item notification-item"
          @click="handleNotificationClick(item)"
        >
          <div class="d-flex align-items-start">
            <i class="fas fa-file-invoice text-maroon mt-1 mr-2"></i>
            <div class="notification-text">
              <div class="notification-title" v-html="item.title || item.data?.title || 'Notification'"></div>
              <small class="text-muted">{{ item.time || item.created_at || 'Just now' }}</small>
            </div>
          </div>
        </a>
      </div>

      <div class="dropdown-divider"></div>
      <a href="/notifications" class="dropdown-item dropdown-footer text-center">
        See All Notifications
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useApi } from '../../../composables/useApi';

const props = defineProps({
  initialCount: {
    type: Number,
    default: 0,
  },
  initialNotifications: {
    type: [Array, String],
    default: () => [],
  },
  markReadEndpoint: {
    type: String,
    default: '/notifications/NotifMarkAsRead',
  },
});

const api = useApi();
const isOpen = ref(false);
const dropdownRef = ref(null);
const unreadCount = ref(props.initialCount);

const parseNotifications = (data) => {
  if (Array.isArray(data)) return data;
  if (typeof data === 'string' && data.length > 2) {
    try {
      const p = JSON.parse(data);
      return Array.isArray(p) ? p : [];
    } catch (e) {}
  }
  return [];
};

const notificationsList = ref(parseNotifications(props.initialNotifications));

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const closeDropdown = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});

const handleNotificationClick = async (item) => {
  const notifId = item.id;
  const targetHref = item.action || item.data?.action || '#';

  try {
    await api.post(props.markReadEndpoint, { notif_id: notifId });
    unreadCount.value = Math.max(0, unreadCount.value - 1);
    notificationsList.value = notificationsList.value.filter(n => n.id !== notifId);
  } catch (e) {
    console.error('Failed to mark notification read', e);
  }

  if (targetHref && targetHref !== '#') {
    window.location.href = targetHref;
  }
};

const markAllAsRead = async () => {
  try {
    await api.post(props.markReadEndpoint, { mark_all: true });
    unreadCount.value = 0;
    notificationsList.value = [];
  } catch (e) {
    console.error(e);
  }
};
</script>

<style scoped>
.notification-bell-wrapper {
  position: relative;
  display: inline-block;
}

.bell-link {
  position: relative;
  display: flex;
  align-items: center;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
}

.bell-icon {
  font-size: 18px;
}

.text-maroon {
  color: #800000;
}

.bell-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  font-size: 0.65rem;
  padding: 0.2rem 0.35rem;
  border-radius: 10px;
}

.notification-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 320px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #ffffff;
  z-index: 1050;
  padding: 0;
}

.dropdown-header {
  padding: 0.75rem 1rem;
  background-color: #f9fafb;
  font-size: 0.85rem;
}

.notification-items-scroll {
  max-height: 280px;
  overflow-y: auto;
}

.notification-item {
  padding: 0.65rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  white-space: normal;
  transition: background-color 0.15s ease;
}

.notification-item:hover {
  background-color: #f8fafc;
}

.notification-title {
  font-size: 0.8125rem;
  color: #1f2937;
  line-height: 1.3;
}
</style>
