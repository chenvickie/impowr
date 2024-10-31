import { module, test } from "qunit";
import validateFieldValidator from "impowr/validators/field-validator";

module("Unit | Validator | field-validator");

test("it exists", function (assert) {
  assert.ok(validateFieldValidator());
});
