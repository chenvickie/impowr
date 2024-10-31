import Service from "@ember/service";
import { storageFor } from "ember-local-storage";
import { inject as service } from "@ember/service";

export default Service.extend({
  apiService: service("api"),
  stats: storageFor("stats"),
  authenticated: false,
  isAuthenticated(skipLogin = false) {
    if (skipLogin) {
      //only for localhost to skip orcid auth
      this.set("stats.userID", "123456789");
      this.set("stats.userToken", "Test token");
      this.set("stats.userName", "Vickie Test");
      this.set("stats.admin", true);
      this.set("stats.expireOn", null);
      return true;
    } else {
      return this.apiService.isAuth();
    }
  },
  setAuthenticated(isAuth) {
    this.set("authenticated", isAuth);
  },
  loginOrcid() {
    return this.apiService.loginOrcid();
  },
  logoutOrcid() {
    return this.apiService.logoutOrcid();
  },
  orcidCallback(data) {
    if (data.access_token) {
      this.token = data.access_token;
    }
    return this.apiService.orcidCallback(data);
  },
});
