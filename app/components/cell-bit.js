import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  display: computed("value", function () {
    return this.get("value") == 1 || this.get("value") == true ? "YES" : "NO";
  }),
});
