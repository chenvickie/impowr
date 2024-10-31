import { module, test } from "qunit";
import { setupTest } from "ember-qunit";

module("Unit | Route | admin-jobs", function (hooks) {
  setupTest(hooks);

  test("it exists", function (assert) {
    let route = this.owner.lookup("route:admin-jobs");
    assert.ok(route);
  });
});
