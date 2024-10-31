import Component from "@ember/component";
import TableCommon from "../mixins/table-common";
import { computed } from "@ember/object";

export default Component.extend(TableCommon, {
  showActionCell: true,
  editData: null,
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
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Name",
        valuePath: "job_name",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Source Name",
        valuePath: "source_institution",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Source Project ID",
        valuePath: "source_project_id",
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
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
      {
        label: "Source Project Path",
        valuePath: "source_project_url",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Source Project Token",
        valuePath: "source_project_token",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Source Contact",
        valuePath: "source_contact_name",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Source Contact Email",
        valuePath: "source_contact_email",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Dest Project ID",
        valuePath: "project_id",
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Dest Project Name",
        valuePath: "project_name",
        ascending: true,
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Dest Project Path",
        valuePath: "project_url",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Dest Project Token",
        valuePath: "project_token",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Dest Contact",
        valuePath: "project_contact_name",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Dest Contact Email",
        valuePath: "project_contact_email",
        breakpoints: [""],
        searchable: true,
        sortable: true,
      },
      {
        label: "Transfer Frequency",
        valuePath: "transfer_frequency",
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Scheduled On",
        valuePath: "scheduled_on",
        breakpoints: ["tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Allows Teams",
        valuePath: "job_teams",
        key: "name",
        breakpoints: [""],
        cellComponent: "cell-list",
        searchable: false,
        sortable: false,
      },
      {
        label: "Date Activated",
        valuePath: "date_activated",
        breakpoints: ["jumbo"],
        searchable: false,
        sortable: false,
      },
      {
        label: "Date Deactivated",
        valuePath: "date_deactivated",
        breakpoints: ["jumbo"],
        searchable: false,
        sortable: false,
      },
      {
        label: "Note",
        valuePath: "note",
        breakpoints: ["jumbo"],
        searchable: false,
        sortable: false,
      },
      {
        label: "Job Admin",
        valuePath: "job_admin",
        breakpoints: [""],
        searchable: false,
        sortable: false,
      },
      {
        label: "Updated On",
        valuePath: "updated_on",
        breakpoints: [""],
        searchable: false,
        sortable: false,
      },
      {
        label: "Updated By",
        valuePath: "updated_by",
        breakpoints: [""],
        searchable: false,
        sortable: false,
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
      columns.push({
        label: "",
        valuePath: "is_editable",
        cellComponent: "cell-delete",
        actionName: "deleteRecord",
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
        .getJobs(params)
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
