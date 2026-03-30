<template>
  <ve-content-area
      :target="pageUid"
      :colPos="colPos"
      :allowedContentTypes="(allowedContentTypes || []).join(',')"
      :disallowedContentTypes="(disallowedContentTypes || []).join(',')"
      :columnName="columnName"
  >
    <T3Renderer
        v-if="contentToRender"
        :content="contentToRender"
    />
  </ve-content-area>

</template>

<script setup lang="ts">
const props = defineProps<{
  colPos: number,
  content?: Object[]
  allowedContentTypes?: string[]
  disallowedContentTypes?: string[]
}>();

const {pageData} = await useT3Page();
const pageUid = computed(() => pageData.value.visualEditor.pageUid);
const columnName = computed(() => 'TODO: column name');
const contentToRender = computed(() => props.content !== undefined ? props.content : (pageData.value.content['colPos' + props.colPos || []]));
</script>

<style scoped>

</style>