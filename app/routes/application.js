import Route from "@ember/routing/route";
import { inject as service } from "@ember/service";
import { computed } from "@ember/object";

export default Route.extend({
  authService: service("auth"),
  skipAuth: computed(function () {
    const hostname = window.location.hostname;
    return hostname === "localhost" || hostname === "127.0.0.1";
  }),
  beforeModel() {
    if (!this.authService.isAuthenticated(this.skipAuth)) {
      return;
    }
  },

  model: function () {},
  setupController: function (controller, model) {
    this._super(controller, model);
    controller.set(
      "isAuthenticated",
      this.authService.isAuthenticated(this.skipAuth)
    );
  },
  actions: {
    login() {
      this.authService.loginOrcid();
    },
    // loading(transition, originRoute) {
    //   let controller = this.controllerFor("application");
    //   controller.set("currentlyLoading", true);
    //   transition.promise.finally(function () {
    //     controller.set("currentlyLoading", false);
    //   });
    // },
  },
});
