import Component from "@ember/component";
import FormCommon from "../mixins/form-common";
import formValidator from "../validators/form-validator";

export default Component.extend(FormCommon, {
  changeSetValidator: formValidator,
  redirectRoute: "admin-forms",
  didInsertElement() {
    this.convertDateFormat();
  },
  actions: {
    saveForm() {
      let self = this;
      let hasError = false;
      this.updateChangeSet();
      let changeset = this.get("changeset");
      changeset.validate().then(() => {
        if (!changeset.get("isValid")) {
          self.toastr.error("Invalid fields!");
          self.$("#form-form").focus();
          hasError = true;
        } else {
          if (!hasError) {
            this.convertDateFormat();
            this.get("api")
              .updateForm(this.get("data"))
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
