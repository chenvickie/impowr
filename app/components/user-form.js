import Component from "@ember/component";
import FormCommon from "../mixins/form-common";
import teamUserValidator from "../validators/team-user-validator";

export default Component.extend(FormCommon, {
  changeSetValidator: teamUserValidator,
  didInsertElement() {},
  actions: {
    updateTeamUser() {
      let self = this;
      let hasError = false;
      this.updateChangeSet();
      let changeset = this.get("changeset");
      changeset.validate().then(() => {
        if (!changeset.get("isValid")) {
          self.toastr.error("Invalid fields!");
          self.$("#team-user-form").focus();
          hasError = true;
        } else {
          if (!hasError) {
            self.get("onSave")();
          }
        }
      });
    },
  },
});
