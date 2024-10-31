import Component from "@ember/component";

export default Component.extend({
  items: [], // a list of available items
  key: null,
  actions: {
    toggleOption(option) {
      // Update the 'checked' property of the option
      let updatedOptions = this.get("items").map((o) => {
        if (o.id === option.id) {
          return { ...o, checked: !o.checked }; // Toggle the checked state
        }
        return o;
      });

      this.set("items", updatedOptions);
    },
  },
});
