import Mixin from "@ember/object/mixin";
import { computed, observer, set } from "@ember/object";
import Table from "ember-light-table";
import { task } from "ember-concurrency";
import { inject as service } from "@ember/service";
import { storageFor } from "ember-local-storage";

export default Mixin.create({
  api: service("api"),
  toast: service("toast"),
  stats: storageFor("stats"),

  tableId: null,

  service: "",
  serviceMethod: "",

  page: 1,
  limit: 50,
  dir: "desc",
  sort: "id",

  isLoading: false, //computed.oneWay("fetchRecords.isRunning"),
  canLoadMore: false,
  enableSync: true,
  responsive: true,

  panelHeaderTitle: "",

  headFixed: true,
  footFixed: true,

  canSelect: false,
  canSelectAll: false,

  hasSelection: computed.notEmpty("table.selectedRows"),
  multiSelect: true,
  expandOnClick: false,

  data: computed(function () {
    return [];
  }),

  meta: null,
  columns: null,
  table: computed("data", function () {
    return Table.create({
      columns: this.get("columns"),
      rows: this.get("data") ? this.get("data") : [],
    });
  }),
  reloadTable: false,

  filter: false,
  filterComponent: null,
  filterClassNames: null,
  serviceSearchData: null,
  searchInput: "",
  searchByKeyup: false,
  searchPartial: "NO",

  possibleFilters: computed("table.columns", function () {
    return this.get("table.columns").filterBy("searchable", true);
  }),

  selectedFilter: computed("possibleFilters", function () {
    let pf = this.get("possibleFilters") ? this.get("possibleFilters") : [];
    for (let i = 0; i <= pf.length; i++) {
      if (pf[i] !== undefined && pf[i]["valuePath"] !== "") {
        return pf[i];
      }
    }
    return pf[0];
  }),

  jobOptions: computed("stats.activatedJobs", function () {
    let all = { id: "all", job_name: "All activated jobs" };
    let jobs = [...this.get("stats.activatedJobs")];
    jobs.unshift(all);
    return jobs;
  }),

  selectedJob: computed("jobOptions", function () {
    if (this.get("jobOptions")) {
      return this.get("jobOptions")[0];
    } else {
      return { id: "all", job_name: "All activated jobs" };
    }
  }),

  totalPages: 1,
  paging: true,
  pageLength: 5,
  paginationOnFooter: true,
  paginationClass: "pagingClass pull-right",
  paginationColClass: "col-sm-4",

  offset: 0,
  onUpdatePage: observer("page", "pageLength", function () {
    this.set(
      "offset",
      this.get("page") == 1
        ? 0
        : (this.get("page") - 1) * this.get("pageLength")
    );
  }),

  resetFilterOnDataChange: false,
  resetPagingOnReload: null,

  // titleComponent: null,
  searchFieldClass: computed("paging", function () {
    return this.get("paging")
      ? "col-sm-8 search-filter-fields"
      : "col-sm-12 search-filter-fields";
  }),
  panelClasses: "",

  onReloadTable: observer("reloadTable", function () {
    this.get("fetchRecords").perform();
  }),

  init() {
    this._super(...arguments);

    let table = this.get("table");
    let sortColumn = table
      .get("allColumns")
      .findBy("valuePath", this.get("sort"));

    // Setup initial sort column
    if (sortColumn) {
      sortColumn.set("sorted", true);
    }
  },

  didInsertElement() {
    this._super(...arguments);
    this.send("setPage", 1);

    if (this.get("searchInput")) {
      this.get("fetchRecords").perform();
    }
  },

  getAPIParams() {
    return {
      key: this.get("selectedFilter.valuePath"),
      value: this.get("searchInput"),
      partial: this.get("searchPartial"),
      sort: this.get("sort"),
      dir: this.get("dir"),
      offset: this.get("offset"),
      limit: this.get("pageLength"),
      jobId: this.get("selectedJob") ? this.get("selectedJob.id") : null,
    };
  },

  updateTotalPages(totalCount) {
    this.set("totalPages", Math.ceil(totalCount / this.get("pageLength")));
  },

  updateSortColumn() {
    let sortColumn = this.get("columns").findBy("valuePath", this.get("sort"));
    if (sortColumn) {
      set(sortColumn, "ascending", !sortColumn.ascending);
    }
  },

  resetFilter: function () {
    this.set("searchInput", "");
  },

  resetPaging: function () {
    this.set("page", 1);
  },

  fetchRecords: task(function* () {
    this.get("data").clear();
    yield this.send("getData");
  }).restartable(),

  compareValues(key, order = "asc") {
    return function innerSort(a, b) {
      if (!a.hasOwnProperty(key) || !b.hasOwnProperty(key)) {
        // property doesn't exist on either object
        return 0;
      }

      const varA = typeof a[key] === "string" ? a[key].toUpperCase() : a[key];
      const varB = typeof b[key] === "string" ? b[key].toUpperCase() : b[key];

      let comparison = 0;
      if (varA > varB) {
        comparison = 1;
      } else if (varA < varB) {
        comparison = -1;
      }
      return order === "desc" ? comparison * -1 : comparison;
    };
  },

  actions: {
    getData() {
      // need to be implemented on the component that extend this mixin
      console.log("You will need to define this method on your own component");
    },
    setPage(page) {
      if (page === this.get("page")) {
        return;
      }
      this.set("page", page);
      this.get("fetchRecords").perform();
    },
    onSearchChange(selectedFilter, searchInput) {
      // do nothing for now
      console.log("onSearchChange", selectedFilter, searchInput);
    },
    onSearchInputChange(selectedFilter, searchInput) {
      // do nothing for now
      console.log("onSearchInputChange", selectedFilter, searchInput);
    },
    onSearchClick(selectedJob, selectedFilter, searchInput) {
      this.set("selectedJob", selectedJob);
      this.set("selectedFilter", selectedFilter);
      this.set("searchInput", searchInput);
      this.set("searchPartial", "YES");
      this.resetPaging();
      this.get("fetchRecords").perform();
    },
    onColumnClick(column) {
      if (column.sorted) {
        this.setProperties({
          dir: column.ascending ? "desc" : "asc",
          sort: column.get("valuePath"),
          canLoadMore: false,
          page: 1,
        });
        this.resetPaging();
        this.get("fetchRecords").perform();
      }
    },
    onScrolledToBottom() {
      if (this.get("canLoadMore")) {
        this.incrementProperty("page");
        this.get("fetchRecords").perform();
      }
    },
    onAfterResponsiveChange(matches) {
      if (matches.indexOf("jumbo") > -1) {
        this.get("table.expandedRows").setEach("expanded", false);
      }
    },
    addRecord() {
      this.onAdd();
    },
    editRecord(row) {
      this.onEdit(row.content);
    },
    deleteRecord(row) {
      this.onDelete(row.content);
    },
    pullRecord() {
      this.onPull();
    },
    refreshTable() {
      this.resetPaging();
      this.get("fetchRecords").perform();
    },
    onCustomAction(name, row) {
      if (name !== undefined && name !== "") {
        this.send(name, row);
      }
    },
  },
});
