import Controller from "@ember/controller";
import { inject } from "@ember/service";
import { storageFor } from "ember-local-storage";
import { computed } from "@ember/object";

export default Controller.extend({
  authService: inject("auth"),
  stats: storageFor("stats"),
  isAuthenticated: false,
  skipAuth: computed(function () {
    const hostname = window.location.hostname;
    return hostname === "localhost" || hostname === "127.0.0.1";
  }),
  currentlyLoading: false,
  actions: {
    logout() {
      if (this.skipAuth) return;

      let self = this;
      this.get("authService")
        .logoutOrcid()
        .then(function () {
          self.authService.setAuthenticated(false);
          self.set("isAuthenticated", false);
          self.transitionToRoute("index");
        });
    },
  },
});
