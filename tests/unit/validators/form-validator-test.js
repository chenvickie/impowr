import { module, test } from "qunit";
import validateFormValidator from "impowr/validators/form-validator";

module("Unit | Validator | form-validator");

test("it exists", function (assert) {
  assert.ok(validateFormValidator());
});
