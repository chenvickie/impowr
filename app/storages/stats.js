import StorageObject from "ember-local-storage/session/object";

const Storage = StorageObject.extend();
Storage.reopenClass({
  initialState() {
    return {
      userID: null,
      userName: null,
      userToken: null,
      expireOn: null,
      activatedJobs: null,
      admin: false,
      simulated: null,
    };
  },
});

export default Storage;
