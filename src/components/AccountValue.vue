<template>
  <div class="accountValue">
    <span><slot></slot></span>
  </div>
</template>

<style scoped>
.accountValue {
  display: flex;
  flex: 1;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
}

.accountValue:first-of-type {
  flex: 1.5;
}
</style>
