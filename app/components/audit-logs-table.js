import Component from "@ember/component";
import TableCommon from "../mixins/table-common";
import { computed } from "@ember/object";

export default Component.extend(TableCommon, {
  sort: "process_start",
  allowPull: false,
  columns: computed(function () {
    let columns = [
      {
        width: "40px",
        sortable: false,
        cellComponent: "row-toggle",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
      },
      {
        label: "Job ID",
        valuePath: "job_id",
        width: "80px",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: false,
        sortable: true,
      },
      {
        label: "Name",
        valuePath: "project_name",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Process Start",
        valuePath: "process_start",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Process End",
        valuePath: "process_end",
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Status",
        valuePath: "status",
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
        cellComponent: "cell-status",
        width: "300px",
      },
      {
        label: "Forms Count",
        valuePath: "forms_count",
        breakpoints: ["jumbo", "desktop"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Records Count",
        valuePath: "records_count",
        breakpoints: ["jumbo", "desktop"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Fields Count",
        valuePath: "fields_count",
        breakpoints: ["jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Pulled By",
        valuePath: "triggered_by",
        breakpoints: ["jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Note",
        valuePath: "note",
        breakpoints: ["jumbo"],
        searchable: false,
        sortable: false,
      },
      {
        label: "Souce Institution",
        valuePath: "source_institution",
        breakpoints: [""],
        searchable: true,
        sortable: false,
      },
      {
        label: "Project Name",
        valuePath: "source_project_name",
        breakpoints: [""],
        searchable: true,
        sortable: false,
      },
      {
        label: "Contact Name",
        valuePath: "source_contact_name",
        breakpoints: [""],
        searchable: false,
        sortable: false,
      },
      {
        label: "Contact Email",
        valuePath: "source_contact_email",
        breakpoints: [""],
        searchable: false,
        sortable: false,
      },
    ];
    return columns;
  }),
  actions: {
    dataTransfer() {
      this.onPull();
    },
    getData() {
      this.set("data", []);
      this.set("isLoading", true);
      let params = this.getAPIParams();
      let self = this;
      this.get("api")
        .getAuditLogs(params)
        .then(function (res) {
          if (res["data"]) {
            self.set("data", res["data"]);
            self.updateSortColumn();
          }
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
