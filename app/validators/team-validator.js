import { validatePresence } from "ember-changeset-validations/validators";

export default {
  team_name: [validatePresence(true)],
  description: [validatePresence(true)],
};
