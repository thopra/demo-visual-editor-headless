<template>
  <div class="t3-ce-textpic">
    <T3MediaGallery :gallery="gallery">
      <T3CeHeader v-bind="props" />
      <T3VeField field="bodytext" :record="visualEditor">
        <T3HtmlParser
          v-if="bodytext"
          :content="bodytext"
        />
      </T3VeField>
    </T3MediaGallery>
  </div>
</template>

<script setup>
const props = defineProps({
  bodytext: { type: String, required: false },
  gallery: { type: Object, required: true },
  header: { type: String, required: false },
  headerLayout: { type: Number, required: false },
  headerPosition: { type: String, required: false },
  headerLink: { type: [Object, String], required: false },
  subheader: { type: String, required: false },
  uid: { type: Number, required: false },
  index: { type: Number, required: false },
  appearance: { type: Object, required: false },
  visualEditor: { type: Object, required: false }
});
</script>
