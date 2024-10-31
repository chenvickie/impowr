import Component from "@ember/component";
import FormCommon from "../mixins/form-common";
import teamValidator from "../validators/team-validator";

export default Component.extend(FormCommon, {
  changeSetValidator: teamValidator,
  redirectRoute: "admin-control",
  didInsertElement() {},
  actions: {
    saveTeam() {
      let self = this;
      let hasError = false;
      this.updateChangeSet();
      let changeset = this.get("changeset");
      changeset.validate().then(() => {
        if (!changeset.get("isValid")) {
          self.toastr.error("Invalid fields!");
          self.$("#team-form").focus();
          hasError = true;
        } else {
          if (!hasError) {
            this.get("api")
              .updateTeam(this.get("data"))
              .then(function (res) {
                if (res == true) {
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
