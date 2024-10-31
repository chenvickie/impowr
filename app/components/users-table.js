import Component from "@ember/component";
import TableCommon from "../mixins/table-common";
import { computed } from "@ember/object";

export default Component.extend(TableCommon, {
  sort: "id",
  showActionCell: true,
  columns: computed(function () {
    let columns = [
      //   {
      //     label: "ID",
      //     valuePath: "id",
      //     width: "80px",
      //     breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
      //     searchable: true,
      //     sortable: true,
      //   },
      {
        label: "Orcid ID",
        valuePath: "user_name",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
    ];
    if (this.get("showActionCell")) {
      columns.push({
        label: "",
        valuePath: "id",
        cellComponent: "cell-edit",
        actionName: "editRecord",
        width: "60px",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: false,
        sortable: false,
      });
    }
    return columns;
  }),
  actions: {
    getData() {
      this.set("data", []);
      this.set("isLoading", true);
      let params = this.getAPIParams();
      let self = this;
      this.get("api")
        .getUsers(params)
        .then(function (res) {
          self.set("data", res["data"] ? res["data"] : []);
          self.updateSortColumn();
          self.updateTotalPages(res["total"] ? res["total"] : 1);
          self.set("isLoading", false);
        })
        .catch(function (error) {
          self.toast.error(error);
          self.set("isLoading", false);
        });
    },
  },
});
