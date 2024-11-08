import Component from "@ember/component";
import { observer } from "@ember/object";

export default Component.extend({
  expandedIcon: "fa-chevron-right",
  onToggle: observer("row.expanded", function () {
    const icon =
      this.get("row.expanded") == true ? "fa-chevron-down" : "fa-chevron-right";
    this.set("expandedIcon", icon);
  }),
});
