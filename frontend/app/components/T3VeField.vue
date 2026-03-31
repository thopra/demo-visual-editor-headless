<template>
  <ve-editable-rich-text
      v-if="field && field.richtext"
      :table="field.table"
      :uid="field.uid"
      :field="field.field"
      :name="field.name"
      :title="field.title"
      :options="field.richtextOptions ? JSON.stringify(field.richtextOptions) : null"
      :key="`ve_${field.id}`"
      v-html="field.value"
  >
  </ve-editable-rich-text>
  <ve-editable-text
      v-else-if="field"
      :table="field.table"
      :uid="field.uid"
      :field="field.field"
      :name="field.name"
      :title="field.title"
      :allowNewlines="field.allowNewlines"
      :key="`ve_${field.id}`"
      :value="field.value"
  >
    <slot />
  </ve-editable-text>
  <slot v-else />
</template>

<script setup lang="ts">
type VeRecordInformation = {
  record: VeRecord,
  fields: VeField[],
};
type VeField = {
  table: string,
  uid: number,
  id: string,
  field: string,
  name: string,
  title: string,
  allowNewlines: string,
  value: string,
  richtext: boolean
  richtextOptions: Object
};
type VeRecord = {
  elementName: string,
  CType: string,
  table: string,
  id: string,
  uid: string,
  pid: string,
  colPos: number,
  hiddenFieldName: string,
  canModifyRecord: boolean,
  canBeMoved: boolean
};

const field = computed(() => {
   return props.record?.fields.filter((item: VeField) => item.field === props.field)[0] || null;
});

const props = defineProps<{
  field: string,
  record: VeRecordInformation
}>();
</script>

<style>
.ve-editable-text {
  box-shadow: 0 0 2px cadetblue;
}
</style>