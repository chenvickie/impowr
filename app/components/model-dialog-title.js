import Component from "@ember/component";

export default Component.extend({
  title: null,
  actions: {
    closeModal(reload = false) {
      this.get("onClose")(reload);
    },
  },
});
