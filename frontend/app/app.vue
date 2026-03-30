<template>
  <header v-if="navigation">
    <NuxtLink v-for="{ link, title } in navigation" :key="link" :to="link">
      {{ title }}
    </NuxtLink>
  </header>
  <NuxtPage />
</template>
<script setup lang="ts">
import {watch,computed} from "vue";
const { initialData } = useT3Api()
const navigation = computed(
  () => initialData.value?.navigation?.[0]?.children ?? []
)

/*
    This only works if ssr is enabled: add the importmap to head so that ES modules can be resolved
 */
watch(initialData, (d) => {

  if (!d.visualEditor) {
    return;
  }

  const scripts : Script[] = [
    {
      innerHTML: JSON.stringify(d.visualEditor.importmap),
      type: "importmap",
      nonce: null
    },
  ];

  const styles : Link[] = [];

  d.visualEditor.styles?.forEach((cssFile: string) => {
    styles.push({
      href: cssFile,
      rel: "stylesheet",
      nonce: null
    });
  });

  //if (import.meta.server) {
  useHead({
        script: scripts,
        link: styles
      }
  );

  //}
}, {
  immediate: true,
  once: true
})

/*
    Add the ES modules after app has been mounted
 */
onMounted(() => {

  watch(initialData, (d) => {

    if (!d.visualEditor) {
      return;
    }

    const scripts : Script[] = [];


    console.log(d.visualEditor.javascript);
    d.visualEditor.javascript?.forEach((jsFile: string) => {
      scripts.push({
        src: jsFile,
        nonce: null,
        type: "module"
      });
    });

    //if (import.meta.server) {
    useHead({
          script: scripts
        }
    );

    window.TYPO3 = window.TYPO3 || {};
    Object.assign(window.TYPO3, {lang: d.visualEditor.language});

    //}
  }, {
    immediate: true,
    once: true
  })

});
</script>
<style scoped>
header a {
  display: inline-block;
  margin-right: 10px;
}
</style>
