<template>

  <div>
    <DraggableTree
            :data="originalData"
            draggable
            crossTree
            ref="tree1"
            @drag="ondrag"
    >
      <!--<div slot-scope="{ data, store }">-->
      <!--<b-->
      <!--v-if="data.children && data.children.length"-->
      <!--@click="store.toggleOpen(data)"-->
      <!--&gt;-->
      <!--{{ data.open ? "-" : "+" }}&nbsp;</b-->
      <!--&gt;-->
      <!--<span> {{ data.text }}-droppable:{{ data.droppable }}</span>-->
      <!--</div>-->
    </DraggableTree>
    <v-select
            :value="value"
            :disabled="readonly"
            :id="name"
            :options="choices"
            :placeholder="options.placeholder"
            @input="$emit('input', $event)"
    ></v-select>
  </div>


</template>

<script>
import mixin from "../../../mixins/interface";
import {DraggableTree}  from "vue-draggable-nested-tree/dist/vue-draggable-nested-tree";

export default {
  mixins: [mixin],
    components: {
        DraggableTree
    },
  computed: {
    choices() {
      const collections = this.$store.state.collections || {};
      const includeSystem = this.options.include_system;

      let choices = {};

      Object.keys(collections).forEach(key => {
        if (includeSystem === false && key.startsWith("directus_")) return;

        choices[key] = this.$helpers.formatTitle(key);
      });

      return choices;
    }
  }
};
</script>

<style lang="scss" scoped>
.v-select {
  margin-top: 0;
  max-width: var(--width-medium);
}
</style>
