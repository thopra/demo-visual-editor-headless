<template>
  <T3VeContentArea :colPos="0" :content="content.colPos0">

  </T3VeContentArea>
</template>

<script setup>
defineProps({
  content: { type: Object, required: true }
});
</script>
