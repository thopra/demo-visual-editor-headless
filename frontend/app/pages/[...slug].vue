<template>
  <T3BackendLayout
      v-if="pageData?.content"
      :name="backendLayout"
      :content="pageData.content"
  />
</template>

<script setup>
import { useHead } from "#app";
const { headData, pageData, backendLayout } = await useT3Page();
useHead(headData);

onMounted(() => {
  if (!pageData.value?.visualEditor) {
    return;
  }
  window.TYPO3 = window.TYPO3 || {};
  window.veInfo = JSON.parse(JSON.stringify(pageData.value.visualEditor));
});
</script>
