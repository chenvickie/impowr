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
  isShowingDeleteConfirm: false,
  isEdit: false,
  allowAdd: false,
  selectedDeleteRecord: null,
  editData: computed(function () {
    return {};
  }),
  actions: {
    addRecord() {
      this.set("editData", {});
      this.set("isEdit", false);
      this.set("isShowingModal", true);
    },
    editRecord(record) {
      this.set("editData", record);
      this.set("isEdit", true);
      this.set("isShowingModal", true);
    },
    deleteRecord(record) {
      this.set("selectedDeleteRecord", record);
      this.set("isShowingDeleteConfirm", true);
    },
    deleteEditData() {
      let self = this;
      let record = this.get("selectedDeleteRecord");
      if (record != null) {
        let params = {
          id: record.id,
        };
        this.get("api")
          .deleteJob(params)
          .then(function (res) {
            if (res) {
              self.send("closeDeleteConfirmDialog");
              self.set("reloadTable", !self.get("reloadTable"));
            }
          });
      }
    },
    closeDeleteConfirmDialog() {
      this.set("selectedDeleteRecord", null);
      this.set("isShowingDeleteConfirm", false);
    },
    closeModal(reload = false) {
      this.set("isShowingModal", false);
      if (reload) {
        this.set("reloadTable", !this.get("reloadTable"));
      }
    },
  },
});
