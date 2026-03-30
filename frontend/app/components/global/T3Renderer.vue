<script setup>
import { h } from "vue";
// import { useT3DynamicCe, useT3DynamicComponent } from "../../composables/useT3DynamicComponent";
defineProps({
  content: { type: Array, required: false, default: () => [] },
  frame: { type: Boolean, required: false, default: true }
});
const renderComponent = (element, index) => {
  const { id, type, appearance, content, visualEditor } = element;
  const component = useT3DynamicCe(type);
  return h(component, {
    ...{
      uid: id,
      appearance,
      visualEditor,
      index
    },
    id: appearance.frameClass === "none" ? `c${id}` : null,
    ...content
  });
};
const renderFrame = (element, index) => {
  const component = useT3DynamicComponent({
    prefix: "T3",
    type: "Frame",
    mode: ""
  });
  return h(
      component,
      {
        ...element.appearance,
        id: `c${element.id}`
      },
      {
        default: () => renderComponent(element, index)
      }
  );
};
</script>
<template>
  <template v-for="(component, index) in content" :key="index">
    <ve-content-element
        v-if="component.visualEditor?.record"
        v-bind="component.visualEditor?.record"
    >
      <component
          :is="frame && component.appearance.frameClass !== 'none' ? renderFrame(component, index) : renderComponent(component, index)"
      />
    </ve-content-element>
    <component
        v-else
        :is="frame && component.appearance.frameClass !== 'none' ? renderFrame(component, index) : renderComponent(component, index)"
    />
  </template>
</template>

<style>
.ve-content-element {
  box-shadow: 0 0 2px magenta;
}
</style>
