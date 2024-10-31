import Mixin from "@ember/object/mixin";
import { computed, set } from "@ember/object";
import { inject as service } from "@ember/service";
import lookupValidator from "ember-changeset-validations";
import Changeset from "ember-changeset";
import moment from "moment";

export default Mixin.create({
  api: service(),
  router: service(),
  toastr: service("toast"),
  redirectRoute: "",
  saveApi: "",
  changeset: null,
  changeSetValidator: null,
  readonly: false,
  data: computed(function () {
    return {};
  }),
  dateFields: computed(function () {
    return ["data.date_activated", "data.date_deactivated"];
  }),
  init() {
    this._super(...arguments);
    if (this.get("changeSetValidator") != null) {
      this.updateChangeSet();
    }
  },
  updateChangeSet() {
    let validator = this.get("changeSetValidator");
    this.set(
      "changeset",
      new Changeset(this.get("data"), lookupValidator(validator), validator)
    );
  },
  convertDateFormat: function () {
    let self = this;
    this.get("dateFields").map(function (d) {
      let val = self.get(d);
      if (val) {
        let time = d == "data.date_deactivated" ? " 11:59:59" : " 00:00:00";
        let dd = moment(val, "YYYY-MM-DD").format("YYYY-MM-DD") + time;
        self.set(d, dd);
      }
    });
  },
  actions: {
    saveField() {
      // default method, please override it on the compoent that extends this mixin
    },
    closeModal(reload = false) {
      this.get("onClose")(reload);
    },
  },
  resetForm() {
    set(this.data, "searchValue", "");
    this.set("data", {});
  },
  redirectToRoute() {
    if (this.redirectRoute != "") {
      this.get("router").transitionTo(this.redirectRoute, {
        queryParams: {
          searchValue: this.data.id,
        },
      });
    }
  },
});
