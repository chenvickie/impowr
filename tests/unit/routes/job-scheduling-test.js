import { module, test } from 'qunit';
import { setupTest } from 'ember-qunit';

module('Unit | Route | job-scheduling', function(hooks) {
  setupTest(hooks);

  test('it exists', function(assert) {
    let route = this.owner.lookup('route:job-scheduling');
    assert.ok(route);
  });
});
