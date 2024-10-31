import Route from "@ember/routing/route";
import RSVP from "rsvp";
import { inject as service } from "@ember/service";
import { storageFor } from "ember-local-storage";

export default Route.extend({
  api: service(),
  stats: storageFor("stats"),
  pageLength: 10,
  model(params) {
    let ajax = this.get("api");
    let jobs = this.get("stats.activatedJobs");
    let res = {
      jobs:
        jobs != null
          ? jobs
          : ajax.getJobs({
              offset: 0,
              limit: 500,
            }),
      forms: ajax.getFormControls({
        offset: 0,
        limit: this.get("pageLength"),
      }),
      searchValue: params.searchValue,
    };
    return RSVP.hash(res);
  },
  setupController(controller, model) {
    this._super(controller, model);
    controller.set("allowPull", model.forms["pull"]);
    controller.set("forms", model.forms["data"]);
    controller.set("pageLength", this.get("pageLength"));
    controller.set(
      "totalPages",
      Math.ceil(model.forms["total"] / this.get("pageLength"))
    );
  },
});
