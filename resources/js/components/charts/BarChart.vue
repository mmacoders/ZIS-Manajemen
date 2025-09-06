<template>
  <div class="chart-container">
    <canvas :id="canvasId" :width="width" :height="height"></canvas>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { Bar } from 'vue-chartjs'
import '../chartjs'

interface Props {
  data: any
  options?: any
  width?: number
  height?: number
}

const props = withDefaults(defineProps<Props>(), {
  width: 400,
  height: 200,
  options: () => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top' as const,
      },
      title: {
        display: true,
        text: 'ZIS Collection by Type'
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value: any) {
            return new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
            }).format(value)
          }
        }
      }
    },
  })
})

const canvasId = ref(`bar-chart-${Math.random().toString(36).substr(2, 9)}`)
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 300px;
  width: 100%;
}

canvas {
  max-width: 100%;
  height: auto;
}
</style>