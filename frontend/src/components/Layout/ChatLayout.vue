<template>
  <div class="chat-layout">
    <div class="floating-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
      <div class="shape shape-4"></div>
      <div class="shape shape-5"></div>
    </div>
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

/* Floating shapes animation */
.floating-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 80px;
  height: 80px;
  top: 15%;
  left: 8%;
  animation-delay: 0s;
}

.shape-2 {
  width: 50px;
  height: 50px;
  top: 70%;
  right: 12%;
  animation-delay: 2s;
}

.shape-3 {
  width: 65px;
  height: 65px;
  bottom: 20%;
  left: 15%;
  animation-delay: 4s;
}

.shape-4 {
  width: 40px;
  height: 40px;
  top: 8%;
  right: 20%;
  animation-delay: 1s;
}

.shape-5 {
  width: 90px;
  height: 90px;
  top: 45%;
  left: 3%;
  animation-delay: 3s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-25px) rotate(180deg);
  }
}

.chat-layout::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.02);
  pointer-events: none;
  z-index: 1;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  margin: 0.5rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
  position: relative;
  z-index: 2;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.2rem;
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(10px);
}

.messages-container::-webkit-scrollbar {
  width: 6px;
}

.messages-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb {
  background: rgba(79, 70, 229, 0.3);
  border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
  background: rgba(79, 70, 229, 0.5);
}

@media (max-width: 768px) {
  .chat-layout {
    flex-direction: column;
  }
  
  .chat-main {
    border-radius: 20px;
    margin: 0.5rem;
  }
  
  .messages-container {
    padding: 1.5rem;
  }
  
  .shape {
    display: none;
  }
}

@media (max-width: 640px) {
  .chat-main {
    border-radius: 16px;
    margin: 0.25rem;
  }
  
  .messages-container {
    padding: 1rem;
  }
}
</style>
