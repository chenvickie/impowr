import AjaxService from "ember-ajax/services/ajax";
import ENV from "../config/environment";
import { computed, observer } from "@ember/object";
import { inject as service } from "@ember/service";
import { storageFor } from "ember-local-storage";
import $ from "jquery";
import moment from "moment";

export default AjaxService.extend({
  stats: storageFor("stats"),
  toastr: service("toast"),
  router: service("router"),
  host: ENV.APP.API,
  token: computed(function () {
    return encodeURIComponent(window.btoa(ENV.APP.authorizer));
  }),
  headers: computed("token", function () {
    return this.getHeader();
  }),
  getHeader() {
    const username = this.get("stats.userID");
    const token = this.get("stats.userToken");
    const base64Credentials = btoa(`${username}:${token}`);
    let header = {
      Authorization: `Basic ${base64Credentials}`,
      "Content-Type": "application/x-www-form-urlencoded",
    };
    if (
      this.get("stats.simulated") != null &&
      this.get("stats.simulated") != ""
    ) {
      header = {
        ...header,
        Simulated: this.get("stats.simulated"),
      };
    }
    return header;
  },
  params: computed("token", function () {
    return {
      userId: this.get("stats.userID"),
    };
  }),
  onSessionChanged: observer("stats.userID", function () {
    this.set("params.userId", this.get("stats.userID"));
  }),
  showLoader() {
    $("body").addClass("loading");
  },
  hideLoader() {
    $("body").removeClass("loading");
  },
  handleError(error) {
    console.log("Error Occurred:", error);
    this.toastr.error(error);
    this.hideLoader();
    return false;
  },
  isEmptyDate(date) {
    return date == "" || date == undefined;
  },

  /************************/
  /*** API Calls - JOBS ***/
  /************************/

  getJobs(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("jobs/read.php", { data: options }).then(function (res) {
        self.hideLoader();
        if (res && res.success) {
          // only store info we need for activated job dropdown list
          self.updatedActivatedJobs(res.data);
          return res;
        } else {
          self.toastr.error("getJobs error: " + res.message);
          return false;
        }
      });
    } catch (error) {
      self.handleError(error);
    }
  },

  updatedActivatedJobs(jobs) {
    let activatedJobs = [];
    jobs.map((job) => {
      if (this.isActivatedJob(job)) {
        activatedJobs.push(this.getStatsJob(job));
      }
    });
    this.set("stats.activatedJobs", activatedJobs);
  },

  updateActivatedJob(job) {
    let activatedJobs = this.get("stats.activatedJobs");
    activatedJobs = activatedJobs.filter((ajob) => {
      return ajob["id"] != job["id"];
    });

    if (this.isActivatedJob(job)) {
      activatedJobs.push(this.getStatsJob(job));
    }
    this.set("stats.activatedJobs", activatedJobs);
  },

  removeActivatedJob(job) {
    let activatedJobs = this.get("stats.activatedJobs");
    activatedJobs = activatedJobs.filter((ajob) => {
      return ajob["id"] != job["id"];
    });
    this.set("stats.activatedJobs", activatedJobs);
  },

  isActivatedJob(job) {
    let today = new Date();
    return (
      !this.isEmptyDate(job["date_activated"]) &&
      moment(job["date_activated"]).isBefore(today) &&
      (this.isEmptyDate(job["date_deactivated"]) ||
        moment(job["date_deactivated"]).isAfter(today))
    );
  },

  getStatsJob(job) {
    return {
      id: job["id"],
      job_name: job["job_name"],
      project_id: job["project_id"],
      project_name: job["project_name"],
      date_activated: job["date_activated"],
      date_deactivated: job["date_deactivated"],
    };
  },

  addJob(options = {}) {
    let self = this;
    options = $.extend(options, this.get("params"));
    options = $.extend(options, { update: false });
    this.showLoader();
    try {
      return this.post("jobs/save.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success(res.message);
            self.updateActivatedJob(res.data);
            return true;
          } else {
            self.toastr.error(res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  updateJob(options = {}) {
    let self = this;
    options = $.extend(options, this.get("params"));
    options = $.extend(options, { update: true });
    this.showLoader();
    try {
      return this.post("jobs/save.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success(res.message);
            self.updateActivatedJob(res.data);
            return true;
          } else {
            self.toastr.error(res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  deleteJob(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("jobs/delete.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success("Delete Success.");
            self.removeActivatedJob(options);
            return true;
          } else {
            self.toastr.error("Delete Error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  testConnections(options = {}) {
    let self = this;
    options = $.extend(options, this.get("params"));
    options = $.extend(options, { update: false });
    this.showLoader();
    try {
      return this.post("jobs/connect.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success(
              "Test Connection Success. " +
                res.message +
                ". Please process to save the job information by clicking on the Submit button!"
            );
            return res;
          } else {
            self.toastr.error("Test Connection Failed: " + res.message);
            return res;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  /*************************/
  /*** API Calls - FORMS ***/
  /*************************/

  getFormControls(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("forms/read.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            return res;
          } else {
            self.toastr.error("getFormControls error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  pullForms(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("forms/pull.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success(res.message);
            return true;
          } else {
            self.toastr.error("pullForms error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  updateForm(options = {}) {
    let self = this;
    options = $.extend(options, this.get("params"));
    this.showLoader();
    try {
      return this.post("forms/save.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success("Updated Form Success. " + res.message);
            return true;
          } else {
            self.toastr.error("Updated Form Error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  /**************************/
  /*** API Calls - FIELDS ***/
  /**************************/

  getFieldControls(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("fields/read.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            return res;
          } else {
            self.toastr.error("getFieldControls error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  pullFields(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("fields/pull.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            return true;
          } else {
            self.toastr.error("pullFields error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  updateField(options = {}) {
    let self = this;
    options = $.extend(options, this.get("params"));
    this.showLoader();
    try {
      return this.post("fields/save.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success("Updated Field Success. " + res.message);
            return true;
          } else {
            self.toastr.error("Updated Field Error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  /******************************/
  /*** API Calls - DICTIONARY ***/
  /******************************/

  getDictionaryControls(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("dictionaries/read.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            return res.data;
          } else {
            self.toastr.error("getDictionaryControls error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  /******************************/
  /*** API Calls - AUDIT LOGS ***/
  /******************************/

  getAuditLogs(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("audits/read.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            return res;
          } else {
            self.toastr.error("getAuditLogs error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  dataTransfer(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.post("transfer/import.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success("Data Transfer Success.");
            return true;
          } else {
            self.toastr.error("dataTransfer error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  /******************************/
  /*** API Calls - USERS/TEAMS **/
  /******************************/

  getUsers(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.request("users/read.php", {
        method: "POST",
        data: options,
      }).then(function (res) {
        self.hideLoader();
        if (res && res.success) {
          return res;
        } else {
          self.toastr.error("getUsers error: " + res.message);
          return false;
        }
      });
    } catch (error) {
      self.handleError(error);
    }
  },

  getTeams(options = {}) {
    let self = this;
    this.showLoader();
    options = $.extend(options, this.get("params"));
    try {
      return this.request("teams/read.php", {
        method: "POST",
        data: options,
      }).then(function (res) {
        self.hideLoader();
        if (res && res.success) {
          return res;
        } else {
          self.toastr.error("getTeams error: " + res.message);
          return false;
        }
      });
    } catch (error) {
      self.handleError(error);
    }
  },

  addTeam(options = {}) {
    let self = this;
    options = $.extend(options, this.get("params"));
    options = $.extend(options, { update: false });
    this.showLoader();
    try {
      return this.post("teams/save.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success(res.message);
            return true;
          } else {
            self.toastr.error(res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  updateTeam(options = {}) {
    let self = this;
    options = $.extend(options, this.get("params"));
    options = $.extend(options, { update: true });
    this.showLoader();
    try {
      return this.request("teams/save.php", { method: "POST", data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success(res.message);
            return true;
          } else {
            self.toastr.error(res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  /****************************/
  /*** API Calls - SIMULATE **/
  /***************************/

  startSimulate() {
    let self = this;
    this.showLoader();
    let options = {
      simulate: this.get("stats.simulated"),
    };
    options = $.extend(options, this.get("params"));
    this.set("headers", this.getHeader());
    try {
      return this.post("auth-orcid/simulate.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            self.toastr.success(res.message);
            return res.info;
          } else {
            self.resetHeader();
            self.toastr.error("Error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.resetHeader();
          self.handleError(err);
          return false;
        });
    } catch (error) {
      self.resetHeader();
      self.handleError(error);
      return false;
    }
  },

  stopSimulate() {
    let self = this;
    this.showLoader();
    let options = {
      simulate: null,
    };
    options = $.extend(options, this.get("params"));
    this.resetHeader();
    try {
      return this.post("auth-orcid/simulate.php", { data: options })
        .then(function (res) {
          self.hideLoader();
          if (res && res.success) {
            return res.info;
          } else {
            self.toastr.error("Error: " + res.message);
            return false;
          }
        })
        .catch(function (err) {
          self.handleError(err);
        });
    } catch (error) {
      self.handleError(error);
    }
  },

  resetHeader() {
    this.set("stats.simulated", null);
    this.set("headers", this.getHeader());
  },

  /*********************************/
  /*** API Calls - AUTHENTICATION **/
  /*********************************/

  isAuth() {
    let self = this;
    if (this.get("stats.userID") === null) {
      return false;
    }

    let options = {
      username: this.get("stats.userID"),
      expire_on: this.get("stats.expireOn"),
    };
    options = $.extend(options, this.get("params"));
    try {
      return this.post("auth-orcid/isAuth.php", { data: options })
        .then(function (res) {
          if (res && res.success) {
            self.set("stats.expireOn", res.expire_on);
            return true;
          } else {
            self.logoutOrcid();
            return self.handleError(res.message);
          }
        })
        .catch(function (err) {
          return self.handleError(err);
        });
    } catch (error) {
      return self.handleError(error);
    }
  },

  loginOrcid() {
    const api = this.get("host");
    window.location.href = `${api}/auth-orcid/login.php`;
  },

  orcidCallback(data) {
    if (data.access_token) {
      this.set("stats.userID", data.orcid);
      this.set("stats.userToken", data.access_token);
      this.set("stats.userName", data.name);
      this.set("stats.expireOn", data.expire_on);
      this.set("stats.admin", data.is_admin);
      this.router.transitionTo("index");
    } else {
      return this.handleError("Failed to authenticate");
    }
  },

  logoutOrcid() {
    let params = this.get("params");
    let self = this;
    try {
      return this.post("auth-orcid/logout.php", { data: params })
        .then(function () {
          self.set("stats.userID", null);
          self.set("stats.userToken", null);
          self.set("stats.userName", null);
          self.set("stats.expireOn", null);
          self.set("stats.admin", false);
          return true;
        })
        .catch(function (err) {
          return self.handleError(err);
        });
    } catch (error) {
      return self.handleError(error);
    }
  },
});
