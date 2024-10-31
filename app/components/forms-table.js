import Component from "@ember/component";
import TableCommon from "../mixins/table-common";
import { computed } from "@ember/object";

export default Component.extend(TableCommon, {
  sort: "job_id",
  showActionCell: true,
  editData: null,
  allowPull: false,
  columns: computed(function () {
    let columns = [
      {
        label: "Form Name",
        valuePath: "form_name",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Import Neeed?",
        valuePath: "import_need",
        width: "120px",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Date Activated",
        valuePath: "date_activated",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "JOB ID",
        valuePath: "job_id",
        width: "80px",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: false,
        sortable: true,
      },
      {
        label: "Source Project Name",
        valuePath: "source_project_name",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      // {
      //   label: "Source Project ID",
      //   valuePath: "source_project_id",
      //   width: "120px",
      //   breakpoints: ["tablet", "desktop", "jumbo"],
      //   searchable: true,
      //   sortable: true,
      // },
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
        .getFormControls(params)
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
