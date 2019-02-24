<template>
  <div>
    <span class="no-wrap">{{ displayValue }}</span>
    <h2>Max Level</h2>
    <input v-model="maxLevel" type="number" />

  </div>
</template>

<script>
import mixin from "../../../mixins/interface";


//import DraggableTree  from "vue-draggable-nested-tree/src/components/DraggableTree";

export default {
    name: 'DraggableTree',
  components: {
    DraggableTree,
    Store
  },
  mixins: [mixin],
  computed: {
    displayValue() {
      if (!this.value) return;
      return this.$helpers.formatTitle(this.value);
    }
  },

  methods: {
    ondrag(node) {
      const { maxLevel } = this;
      let nodeLevels = 1;
      th.depthFirstSearch(node, childNode => {
        if (childNode._vm.level > nodeLevels) {
          nodeLevels = childNode._vm.level;
        }
      });
      nodeLevels = nodeLevels - node._vm.level + 1;
      const childNodeMaxLevel = maxLevel - nodeLevels;
      //
      th.depthFirstSearch(this.originalData, childNode => {
        if (childNode === node) {
          return "skip children";
        }
        if (!childNode._vm) {
          console.log(childNode);
        }
        this.$set(
          childNode,
          "droppable",
          childNode._vm.level <= childNodeMaxLevel
        );
      });
    }
  }
};
</script>
