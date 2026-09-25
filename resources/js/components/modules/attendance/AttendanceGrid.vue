<template>
  <div class="attendance-grid-card">
    <div class="grid-header">
      <div class="d-flex align-items-center">
        <i class="fa fa-calendar-alt text-maroon mr-2"></i>
        <h4 class="mb-0 font-weight-bold">Staff Attendance Matrix</h4>
      </div>
      <div class="d-flex align-items-center gap-2">
        <select v-model="selectedMonth" class="form-control form-control-sm">
          <option v-for="(m, idx) in months" :key="idx" :value="idx + 1">{{ m }}</option>
        </select>
        <input
          v-model="employeeSearch"
          type="text"
          placeholder="Filter staff..."
          class="form-control form-control-sm"
          style="width: 150px;"
        />
      </div>
    </div>

    <!-- Attendance Legend -->
    <div class="attendance-legend">
      <span class="legend-item"><span class="legend-dot bg-success"></span> Present</span>
      <span class="legend-item"><span class="legend-dot bg-danger"></span> Absent</span>
      <span class="legend-item"><span class="legend-dot bg-warning"></span> Late / Early Exit</span>
      <span class="legend-item"><span class="legend-dot bg-info"></span> Leave / Off</span>
    </div>

    <!-- Matrix Table -->
    <div class="table-responsive attendance-table-wrapper">
      <table class="table table-bordered table-sm text-center attendance-table">
        <thead class="thead-light">
          <tr>
            <th class="text-left staff-col">Staff Member</th>
            <th v-for="d in daysInMonth" :key="d" class="day-col">
              {{ d }}
            </th>
            <th class="summary-col bg-light">Pres</th>
            <th class="summary-col bg-light">Abs</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredStaff.length === 0">
            <td :colspan="daysInMonth + 3" class="py-4 text-muted">
              No staff members found.
            </td>
          </tr>
          <tr v-for="staff in filteredStaff" :key="staff.id">
            <td class="text-left font-weight-bold staff-col">
              {{ staff.name }}
              <div class="text-muted small">{{ staff.designation || 'Staff' }}</div>
            </td>
            <td
              v-for="d in daysInMonth"
              :key="d"
              class="day-cell"
              :class="getCellClass(staff.records[d])"
              :title="`Day ${d}: ${staff.records[d] || 'No Record'}`"
            >
              {{ getCellLabel(staff.records[d]) }}
            </td>
            <td class="font-weight-bold text-success summary-col">
              {{ countStatus(staff.records, 'P') }}
            </td>
            <td class="font-weight-bold text-danger summary-col">
              {{ countStatus(staff.records, 'A') }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  staffData: {
    type: [Array, String],
    default: () => [],
  },
});

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const selectedMonth = ref(new Date().getMonth() + 1);
const employeeSearch = ref('');

const daysInMonth = computed(() => {
  const yr = new Date().getFullYear();
  return new Date(yr, selectedMonth.value, 0).getDate();
});

const defaultStaffList = [
  { id: 1, name: 'Ahmed Khan', designation: 'Supervisor', records: { 1: 'P', 2: 'P', 3: 'P', 4: 'P', 5: 'A', 6: 'P', 7: 'O' } },
  { id: 2, name: 'Suresh Kumar', designation: 'Foreman', records: { 1: 'P', 2: 'L', 3: 'P', 4: 'P', 5: 'P', 6: 'P', 7: 'O' } },
  { id: 3, name: 'Mohammed Ali', designation: 'Electrician', records: { 1: 'P', 2: 'P', 3: 'A', 4: 'P', 5: 'P', 6: 'P', 7: 'O' } },
];

const staffList = computed(() => {
  if (Array.isArray(props.staffData) && props.staffData.length > 0) return props.staffData;
  if (typeof props.staffData === 'string' && props.staffData.length > 2) {
    try {
      const p = JSON.parse(props.staffData);
      if (Array.isArray(p) && p.length > 0) return p;
    } catch (e) {}
  }
  return defaultStaffList;
});

const filteredStaff = computed(() => {
  if (!employeeSearch.value.trim()) return staffList.value;
  const q = employeeSearch.value.toLowerCase();
  return staffList.value.filter(s => s.name.toLowerCase().includes(q));
});

const getCellLabel = (status) => {
  if (!status) return '-';
  return status;
};

const getCellClass = (status) => {
  switch (status) {
    case 'P': return 'cell-present';
    case 'A': return 'cell-absent';
    case 'L': return 'cell-late';
    case 'O': return 'cell-off';
    default: return '';
  }
};

const countStatus = (records = {}, status) => {
  return Object.values(records).filter(s => s === status).length;
};
</script>

<style scoped>
.attendance-grid-card {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.grid-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.text-maroon {
  color: #800000;
}

.gap-2 {
  gap: 0.5rem;
}

.attendance-legend {
  display: flex;
  gap: 1.25rem;
  font-size: 0.8125rem;
  color: #4b5563;
  margin-bottom: 0.75rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.attendance-table-wrapper {
  max-height: 480px;
}

.attendance-table {
  font-size: 0.75rem;
}

.staff-col {
  min-width: 140px;
  position: sticky;
  left: 0;
  background: #ffffff;
  z-index: 1;
}

.day-col {
  min-width: 26px;
  padding: 4px 2px !important;
}

.day-cell {
  padding: 4px 2px !important;
  font-weight: 600;
}

.summary-col {
  min-width: 45px;
}

.cell-present {
  background-color: #def7ec;
  color: #03543f;
}

.cell-absent {
  background-color: #fde8e8;
  color: #9b1c1c;
}

.cell-late {
  background-color: #fef08a;
  color: #723b10;
}

.cell-off {
  background-color: #e1effe;
  color: #1e429f;
}
</style>
