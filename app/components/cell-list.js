import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  key: computed("column", function () {
    if (this.get("column.key")) {
      return this.get("column.key");
    }
    return "name"; // use name by default
  }),
  list: computed("value", function () {
    if (!this.get("value")) return [];
    let items = this.get("value");
    if (typeof items == "string") {
      this.set("key", null);
      return items.split(",");
    } else {
      return items;
    }
  }),
});
