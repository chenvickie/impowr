import Component from "@ember/component";

export default Component.extend({
  actions: {
    submit() {
      this.get("onSubmit")();
    },
    cancel() {
      this.get("onCancel")();
    },
  },
});
