import Component from "@ember/component";

export default Component.extend({
  jobOptions: null,
  selectedJob: null,
  selectedFilter: null,
  customButton: null,
  searchInput: "",
  showCustomButton: false,
  actions: {
    onSearchChange() {
      this.onSearchChange(this.get("selectedFilter"), this.get("searchInput"));
    },
    onSearchInputChange() {
      this.onSearchInputChange(
        this.get("selectedFilter"),
        this.get("searchInput")
      );
    },
    onSearchClick() {
      this.onSearchClick(
        this.get("selectedJob"),
        this.get("selectedFilter"),
        this.get("searchInput")
      );
    },
    onCustomButtonClick() {
      this.get("onCustomButtonClick")();
    },
  },
});
