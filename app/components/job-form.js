import Component from "@ember/component";
import { computed, observer, set } from "@ember/object";
import FormCommon from "../mixins/form-common";
import jobValidator from "../validators/job-validator";
import moment from "moment";

export default Component.extend(FormCommon, {
  changeSetValidator: jobValidator,
  redirectRoute: "admin-jobs",
  isEdit: false,
  isConnected: false,
  showProjectInfo: false,
  originalData: null,
  selectedWeekDay: computed("data.transfer_frequency", function () {
    return this.get("data.transfer_frequency") == "weekly"
      ? this.get("data.scheduled_on")
      : null;
  }),
  selectedMonthDate: computed("data.transfer_frequency", function () {
    return this.get("data.transfer_frequency") == "monthly"
      ? this.get("data.scheduled_on")
      : null;
  }),
  availableWeekDays: computed("", function () {
    return [
      "Sunday",
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday",
    ];
  }),
  availableMonthDates: computed("", function () {
    let dates = [];
    for (let i = 1; i <= 27; i++) {
      dates.push(i);
    }
    dates.push("Last day");
    return dates;
  }),
  availableTeams: computed("teams", "data.job_teams", function () {
    return this.get("teams") && this.get("teams").length > 0
      ? this.getAvailableTeams()
      : [];
  }),
  getAvailableTeams() {
    let teams = [];
    let selectedTeams = this.get("data.job_teams")
      ? this.get("data.job_teams")
      : [];
    this.get("teams").forEach((ele) => {
      let team = ele;
      let isChecked =
        selectedTeams.find((t) => t.id == team.id) == undefined ? false : true;
      // Use Ember's set method to update the property
      set(team, "checked", isChecked);
      teams.push(team);
    });
    return teams;
  },
  onTransferFrequencyChanged: observer("data.transfer_frequency", function () {
    let scheduledOn =
      this.get("originalData.transfer_frequency") ==
      this.get("data.transfer_frequency")
        ? this.get("data.scheduled_on")
        : "00:00";

    switch (this.get("data.transfer_frequency")) {
      case "weekly":
        this.set("selectedWeekDay", scheduledOn);
        break;
      case "monthly":
        this.set("selectedMonthDate", scheduledOn);
        break;
      default:
    }
  }),
  onShowProjectInfo: observer("isEdit", "isConnected", function () {
    let flag = this.get("isEdit") || this.get("isConnected");
    this.set("showProjectInfo", flag);
  }),
  didInsertElement() {
    this.set("originalData", { ...this.get("data") });
    this.onShowProjectInfo();
    this.convertDateFormat();
  },

  actions: {
    onScheduledOnChange(scheduledOn) {
      console.log("scheduledOn", scheduledOn);
    },

    testConnections() {
      let self = this;
      this.get("api")
        .testConnections(this.get("data"))
        .then(function (res) {
          if (res && res.success) {
            self.set("isConnected", true);
            if (res["sourceInfo"]) {
              self.set(
                "data.source_project_name",
                res["sourceInfo"]["project_title"]
              );
              self.set(
                "data.source_project_id",
                res["sourceInfo"]["project_id"]
              );
            }
            if (res["destInfo"]) {
              self.set("data.project_name", res["destInfo"]["project_title"]);
              self.set("data.project_id", res["destInfo"]["project_id"]);
            }
          } else {
            self.set("isConnected", false);
          }
        });
    },
    saveJob() {
      let self = this;
      let hasError = false;
      this.updateScheduledOn();
      this.updateChangeSet();
      let selectedJobTeams = this.getSelectedJobTeams();
      if (selectedJobTeams.length <= 0) {
        self.toastr.error("Team(s) can not be empty");
        self.$("#job").focus();
        return;
      } else {
        this.set("data.job_teams", selectedJobTeams);
      }

      let changeset = this.get("changeset");
      changeset.validate().then(() => {
        if (!changeset.get("isValid")) {
          self.toastr.error("Invalid fields!");
          self.$("#job").focus();
          hasError = true;
        } else {
          if (!hasError) {
            this.convertDateFormat();
            if (this.get("isEdit")) {
              this.get("api")
                .updateJob(this.get("data"))
                .then(function (res) {
                  if (res) {
                    self.resetForm();
                    self.send("closeModal", true);
                  }
                });
            } else {
              this.get("api")
                .addJob(this.get("data"))
                .then(function (res) {
                  if (res) {
                    self.resetForm();
                    self.send("closeModal", true);
                  }
                });
            }
          }
        }
      });
    },
  },
  updateScheduledOn() {
    if (!this.get("data.transfer_frequency")) {
      return;
    }
    switch (this.get("data.transfer_frequency")) {
      case "none":
        this.set("data.scheduled_on", "none");
        break;
      case "daily":
        this.set("data.scheduled_on", "00:00");
        break;
      case "weekly":
        this.set("data.scheduled_on", this.get("selectedWeekDay"));
        break;
      case "monthly":
        this.set("data.scheduled_on", this.get("selectedMonthDate"));
        break;
      case "custom":
        this.set(
          "data.scheduled_on",
          moment(this.get("data.scheduled_on"), "YYYY-MM-DD").format(
            "YYYY-MM-DD"
          ) + " 00:00:00"
        );
        break;
      default:
    }
  },
  getSelectedJobTeams() {
    return this.get("availableTeams")
      .filter((team) => team["checked"] == true)
      .map((team) => team.id);
  },
});
