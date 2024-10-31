import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  show: computed("value", function () {
    return (
      this.get("value") == true ||
      this.get("value") == 1 ||
      this.get("value") == "Yes" ||
      this.get("column.valuePath") == "show"
    );
  }),
});
