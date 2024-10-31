import Controller from "@ember/controller";
import { inject as service } from "@ember/service";
import { computed } from "@ember/object";

export default Controller.extend({
  queryParams: ["searchValue"],
  toast: service("toast"),
  api: service("api"),
  searchValue: null,
  reloadTable: false,
  isShowingModal: false,
  selectedTeam: null,
  isEdit: false,
  allowAdd: false,
  editData: computed(function () {
    return {};
  }),
  newTeam: computed(function () {
    return {
      team_name: "",
      description: "",
      updated_on: moment().format("YYYY-MM-DD"),
      updated_by: this.get("stats.userID"),
      team_users: [],
    };
  }),
  actions: {
    addRecord() {
      this.set("isEdit", false);
      this.set("editData", this.get("newTeam"));
      this.set("isShowingModal", true);
    },
    editRecord(record) {
      this.set("isEdit", true);
      this.set("editData", record);
      this.set("isShowingModal", true);
    },
    // deleteRecord(team) {
    //   console.log("deleteRecord", team);
    //   this.set("selectedDeleteRecord", team);
    //   this.set("isShowingDeleteConfirm", true);
    // },
    closeModal(reload = false) {
      console.log("closeModel in admin control", reload);
      this.set("isShowingModal", false);
      if (reload) {
        this.set("reloadTable", !this.get("reloadTable"));
      }
    },
  },
});
