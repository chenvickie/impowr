import { module, test } from "qunit";
import validatejobValidator from "impowr/validators/job-validator";

module("Unit | Validator | job-validator");

test("it exists", function (assert) {
  assert.ok(validatejobValidator());
});
