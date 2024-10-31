import EmberRouter from "@ember/routing/router";
import config from "./config/environment";

export default class Router extends EmberRouter {
  location = config.locationType;
  rootURL = config.rootURL;
}

Router.map(function () {
  this.route("auth");
  this.route("admin-forms");
  this.route("admin-fields");
  this.route("admin-dictionaries");
  this.route("admin-jobs");
  this.route("audit-logs");
  this.route("admin-control");
  this.route("about");
  this.route("job-scheduling");
  this.route("admin-users");
  this.route("callback");
  this.route('plan');
});
