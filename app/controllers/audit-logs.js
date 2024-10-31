import Controller from "@ember/controller";
import { inject as service } from "@ember/service";

export default Controller.extend({
  queryParams: ["searchValue"],
  toast: service("toast"),
  api: service("api"),
  searchValue: null,
  reloadTable: false,
  isShowingModal: false,
  selectedJob: null,
  allowPull: false,
  actions: {
    updateSelectedJob(selectedJob) {
      this.set("selectedJob", selectedJob);
    },
    showPullModel() {
      this.set("isShowingPullConfirm", true);
    },
    dataTransfer() {
      let self = this;
      if (this.get("selectedJob") != null) {
        let params = {
          id: this.get("selectedJob.id"),
        };
        this.get("api")
          .dataTransfer(params)
          .then(function () {
            self.set("reloadTable", !self.get("reloadTable"));
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
