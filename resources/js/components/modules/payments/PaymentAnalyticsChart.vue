<template>
  <div class="payment-analytics-card">
    <div class="analytics-header">
      <div>
        <h4 class="analytics-title">
          <i class="fa fa-chart-line text-maroon mr-2"></i>
          {{ title }}
        </h4>
        <p class="analytics-subtitle">Monthly financial overview & disbursement trends</p>
      </div>
      <div class="analytics-actions">
        <select v-model="selectedYear" class="form-control form-control-sm year-select">
          <option v-for="yr in availableYears" :key="yr" :value="yr">{{ yr }}</option>
        </select>
      </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row kpi-grid mb-3">
      <div class="col-md-4">
        <div class="kpi-box">
          <span class="kpi-label">Total Spend</span>
          <span class="kpi-value text-maroon">{{ formatCurrency(totalSpend) }}</span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-box">
          <span class="kpi-label">Monthly Average</span>
          <span class="kpi-value text-dark">{{ formatCurrency(averageMonthly) }}</span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-box">
          <span class="kpi-label">Peak Month</span>
          <span class="kpi-value text-success">{{ peakMonth.month }} ({{ formatCurrency(peakMonth.amount) }})</span>
        </div>
      </div>
    </div>

    <!-- Reactive SVG Bar Chart -->
    <div class="chart-container">
      <svg
        class="analytics-svg"
        viewBox="0 0 600 240"
        preserveAspectRatio="xMidYMid meet"
      >
        <!-- Horizontal Gridlines -->
        <line
          v-for="i in 4"
          :key="i"
          x1="40"
          :y1="40 + (i - 1) * 45"
          x2="580"
          :y2="40 + (i - 1) * 45"
          stroke="#f0f2f5"
          stroke-dasharray="4"
        />

        <!-- Bars -->
        <g v-for="(item, idx) in chartData" :key="idx">
          <rect
            :x="55 + idx * 43"
            :y="getY(item.amount)"
            width="26"
            :height="getHeight(item.amount)"
            rx="4"
            class="bar-rect"
            :class="{ 'is-hovered': activeIndex === idx }"
            @mouseenter="activeIndex = idx"
            @mouseleave="activeIndex = null"
          />
          <!-- Month Labels -->
          <text
            :x="68 + idx * 43"
            y="230"
            text-anchor="middle"
            class="chart-label"
          >
            {{ item.month.slice(0, 3) }}
          </text>
        </g>
      </svg>

      <!-- Active Tooltip -->
      <div
        v-if="activeIndex !== null && chartData[activeIndex]"
        class="chart-tooltip"
        :style="tooltipStyle"
      >
        <div class="tooltip-month">{{ chartData[activeIndex].month }}</div>
        <div class="tooltip-val">{{ formatCurrency(chartData[activeIndex].amount) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Payment Disbursement Analytics',
  },
  currency: {
    type: String,
    default: 'AED',
  },
  initialData: {
    type: [Array, String],
    default: () => [],
  },
});

const defaultMonthlyData = [
  { month: 'Jan', amount: 45000 },
  { month: 'Feb', amount: 52000 },
  { month: 'Mar', amount: 61000 },
  { month: 'Apr', amount: 48000 },
  { month: 'May', amount: 73000 },
  { month: 'Jun', amount: 89000 },
  { month: 'Jul', amount: 95000 },
  { month: 'Aug', amount: 82000 },
  { month: 'Sep', amount: 67000 },
  { month: 'Oct', amount: 78000 },
  { month: 'Nov', amount: 84000 },
  { month: 'Dec', amount: 91000 },
];

const selectedYear = ref(new Date().getFullYear());
const availableYears = [2026, 2025, 2024];
const activeIndex = ref(null);

const chartData = computed(() => {
  if (Array.isArray(props.initialData) && props.initialData.length > 0) {
    return props.initialData;
  }
  if (typeof props.initialData === 'string' && props.initialData.length > 2) {
    try {
      const parsed = JSON.parse(props.initialData);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    } catch (e) {
      // fallback
    }
  }
  return defaultMonthlyData;
});

const maxAmount = computed(() => {
  const max = Math.max(...chartData.value.map(d => d.amount || 0));
  return max > 0 ? max * 1.15 : 100000;
});

const getY = (amount) => {
  const h = (amount / maxAmount.value) * 170;
  return 210 - h;
};

const getHeight = (amount) => {
  const h = (amount / maxAmount.value) * 170;
  return Math.max(h, 4);
};

const totalSpend = computed(() => {
  return chartData.value.reduce((acc, d) => acc + (parseFloat(d.amount) || 0), 0);
});

const averageMonthly = computed(() => {
  return chartData.value.length ? totalSpend.value / chartData.value.length : 0;
});

const peakMonth = computed(() => {
  if (!chartData.value.length) return { month: 'N/A', amount: 0 };
  return chartData.value.reduce((max, cur) => cur.amount > max.amount ? cur : max, chartData.value[0]);
});

const tooltipStyle = computed(() => {
  if (activeIndex.value === null) return {};
  const leftPercent = ((68 + activeIndex.value * 43) / 600) * 100;
  return {
    left: `${leftPercent}%`,
    top: '25px',
  };
});

const formatCurrency = (val) => {
  const n = parseFloat(val) || 0;
  return `${props.currency} ${n.toLocaleString('en-US', { maximumFractionDigits: 0 })}`;
};
</script>

<style scoped>
.payment-analytics-card {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
}

.analytics-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.analytics-subtitle {
  font-size: 0.8125rem;
  color: #6b7280;
  margin: 0.25rem 0 0 0;
}

.text-maroon {
  color: #800000;
}

.year-select {
  width: 90px;
  font-weight: 500;
}

.kpi-grid {
  display: flex;
  gap: 1rem;
}

.kpi-box {
  background: #f9fafb;
  border: 1px solid #f3f4f6;
  border-radius: 8px;
  padding: 0.85rem 1rem;
  display: flex;
  flex-direction: column;
}

.kpi-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.kpi-value {
  font-size: 1.25rem;
  font-weight: 700;
  margin-top: 0.2rem;
}

.chart-container {
  position: relative;
  width: 100%;
}

.analytics-svg {
  width: 100%;
  height: 240px;
}

.bar-rect {
  fill: #800000;
  opacity: 0.85;
  cursor: pointer;
  transition: all 0.2s ease;
}

.bar-rect:hover,
.bar-rect.is-hovered {
  fill: #a00000;
  opacity: 1;
  filter: drop-shadow(0 4px 6px rgba(128, 0, 0, 0.25));
}

.chart-label {
  font-size: 11px;
  fill: #6b7280;
  font-weight: 500;
}

.chart-tooltip {
  position: absolute;
  transform: translateX(-50%);
  background: #1f2937;
  color: #ffffff;
  padding: 0.4rem 0.65rem;
  border-radius: 6px;
  font-size: 0.75rem;
  pointer-events: none;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  text-align: center;
  z-index: 10;
}

.tooltip-month {
  font-weight: 600;
  color: #d1d5db;
}

.tooltip-val {
  color: #34d399;
  font-weight: 700;
}
</style>
