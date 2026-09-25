<template>
  <div class="qr-scanner-card">
    <div class="scanner-header">
      <h5 class="mb-0">
        <i class="fa fa-qrcode text-maroon mr-2"></i>
        Staff QR Attendance Terminal
      </h5>
      <span class="badge" :class="scanning ? 'badge-success' : 'badge-secondary'">
        {{ scanning ? 'Camera Active' : 'Standby' }}
      </span>
    </div>

    <div class="scanner-viewport">
      <div v-if="!scanning" class="scanner-placeholder">
        <i class="fa fa-camera scanner-icon"></i>
        <p class="mb-2">Camera is inactive</p>
        <button class="btn btn-sm btn-outline-primary" @click="startScanner">
          <i class="fa fa-play mr-1"></i> Start Scanner
        </button>
      </div>

      <div v-else class="scanner-active">
        <div class="scanner-laser"></div>
        <p class="scanner-guide-text">Align Staff QR Code within viewfinder</p>
        <div class="scanner-controls mt-3">
          <button class="btn btn-sm btn-danger" @click="stopScanner">
            <i class="fa fa-stop mr-1"></i> Stop Camera
          </button>
          <button class="btn btn-sm btn-secondary ml-2" @click="simulateScan">
            <i class="fa fa-vial mr-1"></i> Test Scan (Demo)
          </button>
        </div>
      </div>
    </div>

    <!-- Recent Scans Feed -->
    <div v-if="recentScans.length > 0" class="recent-scans mt-3">
      <h6 class="text-muted small font-weight-bold">Recent Clock-ins</h6>
      <ul class="list-group list-group-flush">
        <li
          v-for="(scan, idx) in recentScans"
          :key="idx"
          class="list-group-item d-flex justify-content-between align-items-center py-2 px-1"
        >
          <div>
            <strong>{{ scan.staffName }}</strong>
            <small class="text-muted d-block">{{ scan.code }}</small>
          </div>
          <span class="badge badge-success">{{ scan.time }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useToast } from '../../../composables/useToast';

const toast = useToast();
const scanning = ref(false);
const recentScans = ref([]);

const startScanner = () => {
  scanning.value = true;
};

const stopScanner = () => {
  scanning.value = false;
};

const simulateScan = () => {
  const sampleStaff = [
    { name: 'Kareem Mansoor', code: 'STAFF-1049' },
    { name: 'Ravi Shankar', code: 'STAFF-2081' },
    { name: 'Zaid Al-Harbi', code: 'STAFF-3310' },
  ];
  const picked = sampleStaff[Math.floor(Math.random() * sampleStaff.length)];
  const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });

  recentScans.value.unshift({
    staffName: picked.name,
    code: picked.code,
    time: now,
  });

  if (recentScans.value.length > 5) recentScans.value.pop();
  toast.success(`Clock-in verified for ${picked.name}`);
};
</script>

<style scoped>
.qr-scanner-card {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  padding: 1.25rem;
  max-width: 480px;
}

.scanner-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.text-maroon {
  color: #800000;
}

.scanner-viewport {
  background: #111827;
  border-radius: 8px;
  min-height: 240px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  color: #ffffff;
  text-align: center;
}

.scanner-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.scanner-icon {
  font-size: 2.5rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.scanner-active {
  width: 100%;
  height: 100%;
  padding: 1.5rem;
}

.scanner-laser {
  height: 2px;
  background: #ef4444;
  box-shadow: 0 0 8px #ef4444;
  animation: laserSweep 2s infinite ease-in-out;
  margin: 1.5rem 0;
}

.scanner-guide-text {
  font-size: 0.8125rem;
  color: #9ca3af;
}

@keyframes laserSweep {
  0% { transform: translateY(-40px); }
  50% { transform: translateY(40px); }
  100% { transform: translateY(-40px); }
}
</style>
