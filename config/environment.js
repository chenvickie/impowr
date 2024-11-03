"use strict";

module.exports = function (environment) {
  let ENV = {
    modulePrefix: "impowr",
    environment,
    rootURL: "/",
    locationType: "auto",
    EmberENV: {
      FEATURES: {
        // Here you can enable experimental features on an ember canary build
        // e.g. EMBER_NATIVE_DECORATOR_SUPPORT: true
      },
      EXTEND_PROTOTYPES: {
        // Prevent Ember Data from overriding Date.parse.
        Date: false,
      },
    },
    "ember-toastr": {
      toastrOptions: {
        timeOut: "6000",
        positionClass: "toast-top-full-width",
        closeButton: true,
        progressBar: true,
      },
    },

    APP: {
      // Here you can pass flags/options to your application instance
      // when it is created
      API: "http://cancerdskt06dv:443/impowr-api/",
      authorizer: "whatever",
    },
  };

  if (environment === "development") {
    // ENV.APP.LOG_RESOLVER = true;
    // ENV.APP.LOG_ACTIVE_GENERATION = true;
    // ENV.APP.LOG_TRANSITIONS = true;
    // ENV.APP.LOG_TRANSITIONS_INTERNAL = true;
    // ENV.APP.LOG_VIEW_LOOKUPS = true;
  }

  if (environment === "test") {
    // Testem prefers this...
    ENV.locationType = "none";

    // keep test console output quieter
    ENV.APP.LOG_ACTIVE_GENERATION = false;
    ENV.APP.LOG_VIEW_LOOKUPS = false;

    ENV.APP.rootElement = "#ember-testing";
    ENV.APP.autoboot = false;
  }

  if (environment === "production") {
    // here you can enable a production-specific feature
    ENV.rootURL = "/impowr-ui/";
    ENV.APP.API = "/impowr-ui/impowr-api/";
    ENV.APP.redirectUri =
      "https://cde2omop.wakehealth.edu/impowr-ui/impowr-api/auth-orcid/callback.php";
    ENV.APP.disableRightClick = "return false";
    console.log = function () {}; //dont show log in production
  }

  return ENV;
};
