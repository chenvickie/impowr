import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  show: computed("value", function () {
    return this.get("value") ? this.get("value").includes("1") : false;
  }),
});
