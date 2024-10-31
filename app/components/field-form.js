import Component from "@ember/component";
import { computed } from "@ember/object";

import FormCommon from "../mixins/form-common";
import fieldValidator from "../validators/field-validator";

export default Component.extend(FormCommon, {
  changeSetValidator: fieldValidator,
  redirectRoute: "admin-fields",
  checkboxReadonly: computed("data.field_name", function () {
    return this.get("data.field_name") == "record_id";
  }),
  didInsertElement() {
    this.convertDateFormat();
  },
  actions: {
    saveField() {
      let self = this;
      let hasError = false;
      this.updateChangeSet();
      let changeset = this.get("changeset");
      changeset.validate().then(() => {
        if (!changeset.get("isValid")) {
          self.toastr.error("Invalid fields!");
          self.$("#field-form").focus();
          hasError = true;
        } else {
          if (!hasError) {
            this.convertDateFormat();
            this.get("api")
              .updateField(this.get("data"))
              .then(function (res) {
                if (res) {
                  self.resetForm();
                  self.send("closeModal", true);
                }
              });
          }
        }
      });
    },
  },
});
