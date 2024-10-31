import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  hasError: computed("value", function () {
    return this.get("value").match(/Failed/);
  }),
});
