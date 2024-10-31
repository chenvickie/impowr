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
  selectedJob: null,
  allowPull: false,
  editData: computed(function () {
    return {};
  }),
  actions: {
    updateSelectedJob(selectedJob) {
      this.set("selectedJob", selectedJob);
    },
    showPullModel() {
      this.set("isShowingPullConfirm", true);
    },
    editRecord(record) {
      this.set("editData", record);
      this.set("isShowingModal", true);
    },
    pullForms() {
      let self = this;
      if (this.get("selectedJob") != null) {
        let params = {
          id: this.get("selectedJob.id"),
        };
        this.get("api")
          .pullForms(params)
          .then(function (res) {
            if (res) {
              self.set("reloadTable", !self.get("reloadTable"));
            }
            self.send("closePullConfirmDialog");
          });
      } else {
        self.toast.error("No Job selected!");
      }
    },
    closePullConfirmDialog() {
      this.set("selectedJob", null);
      this.set("isShowingPullConfirm", false);
    },
    closeModal(reload = false) {
      this.set("isShowingModal", false);
      if (reload) {
        this.set("reloadTable", !this.get("reloadTable"));
      }
    },
  },
});
