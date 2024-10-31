import Component from "@ember/component";
import TableCommon from "../mixins/table-common";
import formCommon from "../mixins/form-common";
import { computed } from "@ember/object";
import { storageFor } from "ember-local-storage";
import moment from "moment";

export default Component.extend(TableCommon, formCommon, {
  stats: storageFor("stats"),
  sort: "user_name",
  showActionCell: true,
  editData: null,
  teamData: null,
  columns: computed(function () {
    let columns = [
      {
        label: "Orcid ID",
        valuePath: "user_name",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Editable",
        valuePath: "is_editable",
        cellComponent: "cell-bit",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Is Admin",
        valuePath: "is_admin",
        cellComponent: "cell-bit",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Updated On",
        valuePath: "updated_on",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
      {
        label: "Updated By",
        valuePath: "updated_by",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: true,
        sortable: true,
      },
    ];
    if (this.get("showActionCell")) {
      columns.push({
        label: "",
        valuePath: "show",
        cellComponent: "cell-edit",
        actionName: "editRecord",
        width: "60px",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: false,
        sortable: false,
      });
      columns.push({
        label: "",
        valuePath: "show",
        cellComponent: "cell-delete",
        actionName: "deleteRecord",
        width: "60px",
        breakpoints: ["mobile", "tablet", "desktop", "jumbo"],
        searchable: false,
        sortable: false,
      });
    }
    return columns;
  }),
  createNewTeamUser() {
    return {
      team_id: this.get("teamData.id"),
      user_name: "",
      is_editable: false,
      is_admin: false,
      updated_on: moment().format("YYYY-MM-DD"),
      updated_by: this.get("stats.userID"),
    };
  },
  actions: {
    addTeamUser() {
      let newUser = this.createNewTeamUser();
      this.set("editData", newUser);
      this.set("isEdit", false);
      this.set("isShowingTeamUserModal", true);
    },
    updateTeamUser() {
      // only add new user into data if not exist
      // if edit, it will be updated itself
      if (this.get("isEdit") == false) {
        let teamUsers = this.get("data");
        let userExist = teamUsers.find((user) => {
          return user["user_name"] == this.get("editData.user_name");
        });
        if (!userExist) {
          teamUsers.push(this.get("editData"));
          this.set("data", []);
          this.set("data", teamUsers);
        } else {
          this.toastr.error(
            "User " +
              this.get("editData.user_name") +
              " already exist. Update the record instead of adding it!"
          );
          this.set("editData", null);
        }
      }
      this.send("closeModal");
    },
    editRecord(user) {
      user["updated_on"] = moment().format("YYYY-MM-DD");
      user["updated_by"] = this.get("stats.userID");
      this.set("editData", user);
      this.set("isEdit", true);
      this.set("isShowingTeamUserModal", true);
    },
    deleteRecord(user) {
      let teamUsers = this.get("data").filter((ts) => {
        return ts.user_name != user.get("user_name");
      });
      this.set("data", []);
      this.set("data", teamUsers);
    },
    closeModal() {
      this.set("editData", {});
      this.set("isShowingTeamUserModal", false);
    },
  },
});
