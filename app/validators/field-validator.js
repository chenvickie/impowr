import {
  validatePresence,
  validateNumber,
  //  validateDate,
} from "ember-changeset-validations/validators";

//const today = moment(new Date()).format("YYYY-MM-DD");

export default {
  job_id: [validatePresence(true), validateNumber({ integer: true })],
  field_name: [validatePresence(true)],
  show_blank: [validatePresence(true)],
  date_activated: [validatePresence(true)],
  // date_activated: [
  //   validatePresence(true),
  //   validateDate({ after: new Date("2024-02-11") }),
  // ],
};
