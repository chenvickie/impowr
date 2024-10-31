import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  totalPages: 1,
  begin: 0,
  end: computed("totalPages", function () {
    return this.get("totalPages") > 9 ? 9 : this.get("totalPages");
  }),
  paginationOnFooter: false,
  actions: {
    sendSetPage(p) {
      this.setPage(p);
    },
    updatePageBegin(p) {
      let begin = p - 9 > 0 ? p - 9 : 0;
      let end =
        begin + 9 > this.get("totalPages") ? this.get("totalPages") : begin + 9;
      this.set("begin", begin);
      this.set("end", end);
      this.setPage(p);
    },
    updatePageEnd(p) {
      let end = p + 9 > this.get("totalPages") ? this.get("totalPages") : p + 9;
      let begin = end - 9 < 0 ? 0 : end - 9;
      this.set("begin", begin);
      this.set("end", end);
      this.setPage(p);
    },
  },
  didInsertElement() {
    if (this.get("totalPages") === null || this.get("totalPages") === "") {
      this.set("totalPages", 1);
    }
  },
});
