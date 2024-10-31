import {
  validatePresence,
  validateNumber,
} from "ember-changeset-validations/validators";

export default {
  //team_id: [validatePresence(true), validateNumber({ integer: true })],
  user_name: [validatePresence(true)],
  is_editable: [validatePresence(true)],
  is_admin: [validatePresence(true)],
};
