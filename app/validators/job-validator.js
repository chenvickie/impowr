import {
  validatePresence,
  validateLength,
  //validateFormat,
  validateNumber,
} from "ember-changeset-validations/validators";

export default {
  job_name: [validatePresence(true), validateLength({ min: 2 })],
  project_name: [validatePresence(true)],
  project_id: [validatePresence(true), validateNumber({ integer: true })],
  project_url: [validatePresence(true)],
  project_token: [validatePresence(true)],
  project_contact_name: [validatePresence(true)],
  project_contact_email: [
    validatePresence(true),
    /*validateFormat({ type: "email", inverse: true }),*/
  ],
  source_institution: [validatePresence(true)],
  source_project_name: [validatePresence(true)],
  source_project_id: [
    validatePresence(true),
    validateNumber({ integer: true }),
  ],
  source_project_url: [validatePresence(true)],
  source_project_token: [validatePresence(true)],
  source_contact_name: [validatePresence(true)],
  source_contact_email: [
    validatePresence(true),
    /*validateFormat({ type: "email", inverse: true }),*/
  ],
  date_activated: [validatePresence(true)],
  transfer_frequency: [validatePresence(true)],
  scheduled_on: [validatePresence(true)],
  //note: [],
};
