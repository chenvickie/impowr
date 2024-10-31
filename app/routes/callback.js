import Route from "@ember/routing/route";
import { inject as service } from "@ember/service";

export default Route.extend({
  authService: service("auth"),
  queryParams: {
    access_token: {
      refreshModel: true,
    },
    orcid: {
      refreshModel: true,
    },
    name: {
      refreshModel: true,
    },
    expire_on: {
      refreshModel: true,
    },
    is_admin: {
      refreshModel: true,
    },
  },

  model(params) {
    return this.get("authService").orcidCallback(params);
  },
});
