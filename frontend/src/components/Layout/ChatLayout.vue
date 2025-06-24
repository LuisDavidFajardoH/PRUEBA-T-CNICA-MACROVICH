<template>
  <div class="chat-layout">
    <slot name="sidebar" />
    <div class="chat-main">
      <slot name="header" />
      <div class="messages-container">
        <slot name="messages" />
      </div>
      <slot name="error" />
      <slot name="input" />
    </div>
  </div>
</template>

<script setup lang="ts">
// Layout component for chat interface
</script>

<style scoped>
.chat-layout {
  display: flex;
  height: 100vh;
  background-color: #f8fafc;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

@media (max-width: 768px) {
  .messages-container {
    padding: 1rem;
  }
}
</style>
