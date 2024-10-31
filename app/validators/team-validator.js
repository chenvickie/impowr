import {
  validatePresence,
  validateNumber,
} from "ember-changeset-validations/validators";

export default {
  //id: [validatePresence(true), validateNumber({ integer: true })],
  team_name: [validatePresence(true)],
  description: [validatePresence(true)],
};
