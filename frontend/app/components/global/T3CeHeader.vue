<script setup>
const props = defineProps({
  header: { type: String, required: false, default: "" },
  headerLayout: { type: Number, required: false, default: 0 },
  headerPosition: { type: String, required: false, default: "" },
  headerLink: { type: [Object, String], required: false, default: () => ({
    additionalAttributes: [],
    class: "",
    href: "",
    linkText: "",
    target: "",
    title: ""
  }) },
  subheader: { type: String, required: false, default: "" },
  uid: { type: Number, required: false },
  index: { type: Number, required: false },
  appearance: { type: Object, required: false },
  visualEditor: { type: Object, required: false }
});
const { headerLevel, headerClass } = useT3CeHeader(props);
</script>

<script>
export default {
  inheritAttrs: false
};
</script>

<template>
  <div
    v-if="props.header && props.headerLayout !== 100"
    :class="headerClass"
    class="t3-ce-header"
  >
    <component
      :is="`h${headerLevel}`"
      v-if="props.headerLayout >= 0 && props.headerLayout !== 100"
      :class="props.headerPosition"
    >
      <T3Link
        v-if="props.headerLink"
        :link="props.headerLink"
      >
        <T3VeField field="header" :record="props.visualEditor">
          {{ props.header }}
        </T3VeField>
      </T3Link>
      <template v-else>
        <T3VeField field="header" :record="props.visualEditor">
        {{ props.header }}
        </T3VeField>
      </template>
    </component>
    <component
      :is="`h${headerLevel + 1}`"
      v-if="props.subheader"
    >
      <T3VeField field="subheader" :record="props.visualEditor">
        {{ props.subheader }}
      </T3VeField>
    </component>
  </div>
</template>
