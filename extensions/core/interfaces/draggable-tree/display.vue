<template>
  <div>
    <span class="no-wrap">{{ displayValue }}</span>
    <h2>Max Level</h2>
    <input v-model="maxLevel" type="number" />
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
  </div>
</template>

<script>
import mixin from "../../../mixins/interface";
import {DraggableTree}  from "./node_modules/vue-draggable-nested-tree/dist/vue-draggable-nested-tree.min";

export default {
  components: {
    DraggableTree
  },
  mixins: [mixin],
  computed: {
    displayValue() {
      if (!this.value) return;
      return this.$helpers.formatTitle(this.value);
    }
  },
  // data() {
  //   return {
  //     originalData: [
  //       { text: "node 1" },
  //       { text: "node 2" },
  //       { text: "node 3" },
  //       { text: "node 4" },
  //       { text: "node 4 undroppable", droppable: false },
  //       {
  //         text: "node 5",
  //         children: [
  //           { text: "node 1" },
  //           {
  //             text: "node 2",
  //             children: [{ text: "node 3" }, { text: "node 4" }]
  //           },
  //           {
  //             text: "node 2 undroppable",
  //             droppable: false,
  //             children: [{ text: "node 3" }, { text: "node 4" }]
  //           },
  //           {
  //             text: "node 2",
  //             children: [
  //               { text: "node 3" },
  //               { text: "node 4 undroppable", droppable: false }
  //             ]
  //           },
  //           { text: "node 3" },
  //           { text: "node 4" },
  //           { text: "node 3" },
  //           { text: "node 4" },
  //           { text: "node 3" },
  //           { text: "node 4" },
  //           { text: "node 3" },
  //           { text: "node 4" }
  //         ]
  //       }
  //     ],
  //     data: null,
  //     maxLevel: 2
  //   };
  // },
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
