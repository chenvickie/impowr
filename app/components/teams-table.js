import Component from "@ember/component";
import TableCommon from "../mixins/table-common";
import { computed } from "@ember/object";

export default Component.extend(TableCommon, {
  sort: "id",
  showActionCell: true,
  allowAdd: false,
  columns: computed(function () {
    let columns = [
      {
        width: "40px",
        sortable: false,
        cellComponent: "row-toggle",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
      },
      {
        label: "ID",
        valuePath: "id",
        width: "80px",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Name",
        valuePath: "team_name",
        width: "300px",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },

      {
        label: "description",
        valuePath: "description",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },

      {
        label: "Last updated",
        valuePath: "updated_on",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Users",
        valuePath: "team_users",
        key: "user_name",
        cellComponent: "cell-list",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
    ];
    if (this.get("showActionCell")) {
      columns.push({
        label: "",
        valuePath: "is_editable",
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
        .getTeams(params)
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
