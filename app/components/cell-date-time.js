import Component from '@ember/component';
import { computed } from '@ember/object';
import moment from 'moment';

export default Component.extend({
  displayDate: computed('value',function() {
    return moment(this.get('value')).format('MM-DD-YYYY h:mm a');
  })
});
