
DROP TABLE IF EXISTS dbo.impowr_user_permissions
CREATE TABLE dbo.impowr_user_permissions(
  user_name varchar(128) PRIMARY KEY, 
  super_admin varchar(10),
  last_login varchar(128)
)

DROP TABLE IF EXISTS dbo.impowr_teams;
CREATE TABLE dbo.impowr_teams(
  id int IDENTITY(1,1) PRIMARY KEY,
  team_name varchar(128), 
  description varchar(max),
  updated_on varchar(30),
  updated_by varchar(128),
  date_deleted varchar(30)
  FOREIGN KEY (updated_by) REFERENCES dbo.impowr_user_permissions (user_name) ON UPDATE CASCADE 
)

DROP TABLE IF EXISTS dbo.impowr_team_users;
CREATE TABLE dbo.impowr_team_users(
  team_id int, 
  user_name varchar(128),
  is_editable bit,
  is_admin bit, 
  updated_on varchar(30),
  updated_by varchar(128) 
  CONSTRAINT team_id UNIQUE(user_name)   
)

DROP TABLE IF EXISTS dbo.impowr_jobs;
CREATE TABLE dbo.impowr_jobs(
    id int IDENTITY(1,1) PRIMARY KEY,
    job_name varchar(max),
    project_name varchar(max),
    project_id varchar(max),
    project_url varchar(max),
    project_token varchar(max),
    project_contact_name varchar(max),
    project_contact_email varchar(max),
    source_institution varchar(max),
    source_project_name varchar(max),
    source_project_id varchar(max),
    source_project_url varchar(max),
    source_project_token varchar(max),
    source_contact_name varchar(max),
    source_contact_email varchar(max),
    transfer_frequency varchar(30), 
    scheduled_on varchar(30),
    date_activated varchar(10),
    date_deactivated varchar(10),
    date_deleted varchar(10),
    updated_on varchar(30),
    updated_by varchar(128),
    note varchar(max),
    job_admin varchar(128),
    FOREIGN KEY (updated_by) 
    REFERENCES dbo.impowr_user_permissions (user_name) ON UPDATE CASCADE 
);

DROP TABLE IF EXISTS dbo.impowr_team_jobs
CREATE TABLE dbo.impowr_team_jobs(
  team_id int, 
  job_id int
  CONSTRAINT team_job_id UNIQUE(team_id, job_id)   
)

DROP TABLE IF EXISTS dbo.impowr_audit_logs;
CREATE TABLE dbo.impowr_audit_logs(
    id int IDENTITY(1,1) PRIMARY KEY,
    forms varchar(max),
    fields varchar(max),
    records varchar(max),
    forms_count int,
    fields_count int,
    records_count int,
    process_start varchar(30),
    process_end varchar(30),
    status varchar(max),
    note varchar(max),
    triggered_by varchar(128),
    job_id int,
    FOREIGN KEY (job_id) 
    REFERENCES dbo.impowr_jobs (id) ON UPDATE CASCADE 
);

DROP TABLE dbo.impowr_form_controls;
CREATE TABLE dbo.impowr_form_controls(
    id int IDENTITY(1,1) PRIMARY KEY,
    form_name varchar(max),
    import_need bit,
    date_activated varchar(10),
    date_deactivated varchar(10),
    job_id int,
    FOREIGN KEY (job_id) 
    REFERENCES dbo.impowr_jobs (id) ON UPDATE CASCADE 
);
 
DROP TABLE IF EXISTS dbo.impowr_field_controls;
CREATE TABLE dbo.impowr_field_controls(
    id int IDENTITY(1,1) PRIMARY KEY,
    field_name varchar(max),
    show_blank bit,
    date_activated varchar(10),
    date_deactivated varchar(10),
    job_id int,
    FOREIGN KEY (job_id) 
    REFERENCES dbo.impowr_jobs (id) ON UPDATE CASCADE 
);

DROP TABLE IF EXISTS dbo.impowr_dictionary_controls;
CREATE TABLE dbo.impowr_dictionary_controls(
    id int IDENTITY(1,1) PRIMARY KEY,
    field_name varchar(max),
    form_name varchar(max),
    section_header varchar(max),
    field_type varchar(max),
    field_label varchar(max),
    select_choices_or_calculations varchar(max),
    field_note varchar(max),
    text_validation_type_or_show_slider_number varchar(max),
    text_validation_min varchar(max),
    text_validation_max varchar(max),
    identifier bit,
    branching_logic varchar(max),
    required_field bit,
    custom_alignment varchar(max),
    question_number varchar(max),
    matrix_group_name varchar(max),
    matrix_ranking bit,
    field_annotation varchar(max),
    is_allow bit,
    is_destination bit,
    date_created varchar(20),
    job_id int,
    FOREIGN KEY (job_id) 
    REFERENCES dbo.impowr_jobs (id) ON UPDATE CASCADE 
);

