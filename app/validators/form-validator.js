import {
  validatePresence,
  validateNumber,
} from "ember-changeset-validations/validators";

export default {
  job_id: [validatePresence(true), validateNumber({ integer: true })],
  id: [validatePresence(true), validateNumber({ integer: true })],
  form_name: [validatePresence(true)],
  import_need: [validatePresence(true)],
  date_activated: [validatePresence(true)],
};
