<script setup lang="ts">
import { RouterView } from 'vue-router'
import { SimpleErrorBoundary } from '@/components'
</script>

<template>
  <SimpleErrorBoundary :globalErrorHandler="true" :showReportButton="true">
    <div id="app" class="min-h-screen bg-gray-50">
      <RouterView />
    </div>
  </SimpleErrorBoundary>
</template>

<style>
#app {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
    'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>