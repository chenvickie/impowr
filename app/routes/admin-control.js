import Route from "@ember/routing/route";

import RSVP from "rsvp";
import { inject as service } from "@ember/service";

export default Route.extend({
  api: service(),
  pageLength: 500,
  model(params) {
    let ajax = this.get("api");
    let res = {
      teams: ajax.getTeams({
        offset: 0,
        limit: this.get("pageLength"),
      }),
      searchValue: params.searchValue,
    };
    return RSVP.hash(res);
  },
  setupController(controller, model) {
    this._super(controller, model);
    controller.set("allowAdd", model.teams["add"]);
    controller.set("teams", model.teams["data"]);
    controller.set("pageLength", this.get("pageLength"));
    controller.set(
      "totalPages",
      Math.ceil(model.teams["total"] / this.get("pageLength"))
    );
  },
});
