import Component from "@ember/component";
import { computed } from "@ember/object";

export default Component.extend({
  selectedJob: null,
  availableJobs: computed(function () {
    return [];
  }),
  actions: {
    updateSelectedJob(selectedJob) {
      this.get("onUpdateSelectedJob")(selectedJob);
    },
  },
});
