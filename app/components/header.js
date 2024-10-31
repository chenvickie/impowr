import Component from "@ember/component";
import ENV from "impowr/config/environment";
import { computed, observer } from "@ember/object";
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
  loginAdmin: null,
  greeting: "",
  canSimulate: computed("stats.admin", "stats.simulated", function () {
    return this.get("stats.admin") || this.get("simulated") != null;
  }),
  isSimulated: computed("stats.simulated", function () {
    return this.get("stats.simulated") != null;
  }),
  updateCanSimulate: observer("stats.admin", "stats.simulated", function () {
    this.set(
      "canSimulate",
      this.get("stats.admin") || this.get("stats.simulated") != null
    );
  }),
  updateStats: observer("stats.userID", "stats.userName", function () {
    if (this.get("stats.userID") !== null) {
      this.set("showLogout", true);
      this.updateGreetingMsg();
    } else {
      this.set("showLogout", false);
      this.set("greeting", "");
    }
  }),
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
    this.updateStats();
    this.get("loginAdmin", this.get("stats.admin"));
  },
  actions: {
    logout() {
      this.send("updateStats", false);
      this.get("logout")();
    },
    showSimulateModal() {
      this.set("isShowingSimulateModal", true);
    },
    updateStats(isSimulated) {
      if (isSimulated === true) {
        this.set("stats.admin", false);
        this.set("stats.activatedJobs", null);
        this.set("stats.simulated", this.get("simulateID"));
      } else {
        this.set("stats.admin", this.get("loginAdmin"));
        this.set("stats.simulated", null);
      }
      this.updateGreetingMsg();
    },
    onStartSimulate() {
      if (this.get("simulateID") != null && this.get("simulateID") != "") {
        this.send("updateStats", true);
        let self = this;
        this.get("api")
          .startSimulate()
          .then((res) => {
            console.log("res", res);
            if (res == true) {
              self.set("isSimulated", true);
              self.send("closeModal");
              self.send("reloadPage");
            } else {
              self.send("exitSimulate");
            }
          });
      } else {
        this.toast.error("Invalid Simulated ID");
      }
    },
    onExitSimulate() {
      this.send("updateStats", false);
      let self = this;
      this.get("api")
        .stopSimulate()
        .then((res) => {
          if (res == true) {
            self.set("isSimulated", false);
            self.send("reloadPage");
          }
        });
    },
    exitSimulate() {
      this.send("updateStats", false);
      this.set("isSimulated", false);
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
