import Component from "@ember/component";
import ENV from "impowr/config/environment";
import { observer } from "@ember/object";
import { storageFor } from "ember-local-storage";
import { inject as service } from "@ember/service";

export default Component.extend({
  toast: service("toast"),
  api: service("api"),
  router: service("router"),
  stats: storageFor("stats"),
  logoImg: ENV.rootURL + "images/logo.png",
  showLogout: false,
  isShowingSimulateModal: false,
  simulateID: null,
  greeting: "",
  canSimulate: false,
  isSimulated: false,
  updateSimulateStats: observer("stats.simulated", function () {
    const isSimulated =
      this.get("stats.simulated") != null && this.get("stats.simulated") != "";
    this.set("isSimulated", isSimulated);
    this.set("canSimulate", this.get("stats.admin") || isSimulated);
  }),
  updateLoginInfo: observer(
    "stats.userID",
    "stats.userName",
    "stats.simulated",
    function () {
      if (this.get("stats.userID") !== null) {
        this.set("showLogout", true);
        this.updateGreetingMsg();
      } else {
        this.set("showLogout", false);
        this.set("greeting", "");
      }
    }
  ),
  updateGreetingMsg() {
    if (this.get("stats.simulated")) {
      this.set("greeting", "Hello, Simulator " + this.get("stats.simulated"));
    } else {
      this.set(
        "greeting",
        "Hello,  " +
          this.get("stats.userName") +
          " - " +
          this.get("stats.userID")
      );
    }
  },
  didInsertElement() {
    this.updateSimulateStats();
    this.updateLoginInfo();
  },
  actions: {
    logout() {
      this.set("stats.simulated", null);
      this.get("logout")();
    },
    showSimulateModal() {
      this.set("isShowingSimulateModal", true);
    },
    onStartSimulate() {
      if (this.get("simulateID") != null && this.get("simulateID") != "") {
        this.set("stats.simulated", this.get("simulateID"));
        let self = this;
        this.get("api")
          .startSimulate()
          .then((info) => {
            if (info) {
              self.set("stats.admin", false);
              self.set("stats.activatedJobs", null);
              self.send("closeModal");
              self.send("reloadPage");
            } else {
              self.set("stats.simulated", null);
            }
          });
      } else {
        this.toast.error("Invalid Simulated ID");
      }
    },
    onExitSimulate() {
      this.set("stats.simulated", null);
      let self = this;
      this.get("api")
        .stopSimulate()
        .then((info) => {
          if (info) {
            if (info["super_admin"] == "YES") {
              self.set("stats.admin", true);
            }
            self.send("reloadPage");
          }
        });
    },
    closeModal() {
      this.set("simulateID", null);
      this.set("isShowingSimulateModal", false);
    },
    reloadPage() {
      window.location.reload();
    },
  },
});
