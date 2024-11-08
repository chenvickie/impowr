import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  key: computed("column", function () {
    return this.get("column.key") ? this.get("column.key") : "name"; // use name by default
  }),
  list: computed("value", function () {
    return !this.get("value") ? [] : this.getList();
  }),
  getList() {
    let items = this.get("value");
    if (typeof items == "string") {
      this.set("key", null);
      return items.split(",");
    } else {
      return items;
    }
  },
});
