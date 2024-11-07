import { validatePresence } from "ember-changeset-validations/validators";

export default {
  user_name: [validatePresence(true)],
  is_editable: [validatePresence(true)],
  is_admin: [validatePresence(true)],
};
