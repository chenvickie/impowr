<?php

class Job
{
    private $conn;
    private $debug = true;
    private $today;
    private $tomorrow;
    public $loginUser = null;
    public $jobInfo;

    public function __construct ($db, $jobId, $loginUser = null)
    {
        $this->conn      = $db;
        $this->today     = date ("Y-m-d");
        $this->tomorrow  = date ('Y-m-d', strtotime ('+1 day'));
        $this->loginUser = $loginUser;

        try {
            $this->setJobInfo ($jobId);
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in get job: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }

    public function setJobInfo ($jobId)
    {
        if ( $jobId ) {
            $query = "SELECT * FROM [dbo].[impowr_jobs] 
                        WHERE date_deleted is null AND id = " . $jobId;

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row);
            }

            $dbh           = null;
            $this->jobInfo = $data[0];
        } else {
            $this->jobInfo = null;
        }
    }

    public function addJob ($params)
    {
        $params[] = $this->loginUser["user_name"];
        try {
            $query = "
                INSERT[dbo].[impowr_jobs] (  
                    updated_on,
                    updated_by,
                    job_name,
                    project_name,
                    project_id,
                    project_url,
                    project_token,
                    project_contact_name,
                    project_contact_email,
                    source_institution,
                    source_project_name,
                    source_project_id,
                    source_project_url,
                    source_project_token,
                    source_contact_name,
                    source_contact_email,
                    transfer_frequency, 
                    date_activated,
                    date_deactivated,
                    scheduled_on,
                    note,
                    job_admin
                ) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute (convertParams2QueryValues ($params));
            $lastInsertId = $dbh->lastInsertId ();
            $this->setJobInfo ($lastInsertId);
            $dbh = null;
            return $lastInsertId;
        }
        catch ( PDOException $e ) {
            logs ("Error in addJob: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return null;
        }
    }

    public function updateJob ($params, $jobId)
    {
        try {
            $query = "UPDATE [dbo].[impowr_jobs] SET  
                updated_on = ?,
                    updated_by = ?,
                    job_name = ?,
                    project_name = ?,
                    project_id = ?,
                    project_url = ?,
                    project_token = ?,
                    project_contact_name = ?,
                    project_contact_email = ?,
                    source_institution = ?,
                    source_project_name = ?,
                    source_project_id = ?,
                    source_project_url = ?,
                    source_project_token = ?,
                    source_contact_name = ?,
                    source_contact_email = ?,
                    transfer_frequency = ?,
                    date_activated = ?,
                    date_deactivated = ?,
                    scheduled_on = ?,
                    note = ? 
                    WHERE id = ?";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute (convertParams2QueryValues ($params));
            $this->setJobInfo ($jobId);

            $dbh = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in updateJob: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    public function deleteJob ($id)
    {
        $dbh = $this->conn;

        try {
            $field_query = "UPDATE [dbo].[impowr_field_controls]
                              SET date_deactivated = '" . $this->today . "' 
                              WHERE job_id = " . $id;
            $form_query  = "UPDATE [dbo].[impowr_form_controls]
                              SET date_deactivated = '" . $this->today . "' 
                              WHERE job_id = " . $id;
            $job_query   = "UPDATE [dbo].[impowr_jobs] 
                            SET date_deactivated = '" . $this->today . "',
                                date_deleted = '" . $this->today . "' 
                            WHERE id = " . $id;

            /* Begin a transaction, turning off autocommit */
            $dbh->beginTransaction ();

            /* Run queries */
            $dbh->exec ($field_query);
            $dbh->exec ($form_query);
            $dbh->exec ($job_query);

            /* Database connection is now back in autocommit mode */
            $dbh->commit ();
            $dbh           = null;
            $this->jobInfo = null;
            return true;
        }
        catch ( PDOException $e ) {
            $dbh->rollBack ();
            logs ("[ERROR] Error in deleteJob: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    public function getDictionary ($returnLast = true)
    {
        try {
            $query = "SELECT * FROM [dbo].[impowr_dictionary_controls]
                        WHERE job_id = " . $this->jobInfo["id"] . " 
                        ORDER BY date_created DESC";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute ();

            $data = null;
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                $key = $row["field_name"];
                if ( $returnLast ) {
                    if ( ! $data[$key] ) {
                        $data[$key] = $row;
                    }
                } else {
                    if ( ! $data[$key] ) $data[$key] = [];
                    array_push ($data[$key], $row);
                }
            }

            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in getDictionary: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return [];
        }
    }

    public function updateDictionary ($fields)
    {
        try {
            $dbh = $this->conn;
            foreach ($fields as $field) {
                $params = array(
                    $field['field_name'],
                    $field['form_name'],
                    $field['section_header'],
                    $field['field_type'],
                    $field['field_label'],
                    $field['select_choices_or_calculations'],
                    $field['field_note'],
                    $field['text_validation_type_or_show_slider_number'],
                    $field['text_validation_min'],
                    $field['text_validation_max'],
                    $field['identifier'] ? 1 : 0,
                    $field['branching_logic'],
                    $field['required_field'],
                    $field['custom_alignment'],
                    $field['question_number'],
                    $field['matrix_group_name'],
                    $field['matrix_ranking'],
                    $field['field_annotation'],
                    1,
                    1,
                    date ("Y-m-d H:i:s"),
                    $this->jobInfo["id"]
                );
                $query  = "
                    INSERT[dbo].[impowr_dictionary_controls] (  
                        field_name,
                        form_name,
                        section_header,
                        field_type,
                        field_label,
                        select_choices_or_calculations,
                        field_note,
                        text_validation_type_or_show_slider_number,
                        text_validation_min,
                        text_validation_max,
                        identifier,
                        branching_logic,
                        required_field,
                        custom_alignment,
                        question_number,
                        matrix_group_name,
                        matrix_ranking,
                        field_annotation,
                        is_allow,
                        is_destination,
                        date_created,
                        job_id
                    ) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $sth    = $dbh->prepare ($query);
                $sth->execute (convertParams2QueryValues ($params));
            }
            $dbh = null;
            logs ("[INFO] Total updated fields in dictionary: " . count ($fields) . "<br/>", true, true);
            return $fields;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in updateDictionary: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return [];
        }
    }

    public function getImportForms ($all, $import_need = 1)
    {
        try {
            $query = "SELECT * FROM [dbo].[impowr_form_controls]
                        WHERE job_id = " . $this->jobInfo["id"] . " 
                        AND ( date_deactivated IS NULL OR date_deactivated > :date ) 
                        AND date_activated <= :date1";

            if ( ! $all ) $query .= " AND import_need = " . $import_need;

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->bindParam (":date", $this->today, PDO::PARAM_STR);
            $sth->bindParam (":date1", $this->today, PDO::PARAM_STR);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row["form_name"]);
            }
            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in getImportForms: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return [];
        }
    }

    public function getAllForms ()
    {
        return $this->getImportForms (true);
    }

    public function getRequiredForms ()
    {
        return $this->getImportForms (false, 1);
    }

    public function getSkipForms ()
    {
        return $this->getImportForms (false, 0);
    }

    public function updateImportForms ($surveyForms, $skipForms = [])
    {
        try {
            if ( $this->debug ) logs ("[DEBUG] surveyForms | skipForms: " . json_encode ($surveyForms) . " | " . json_encode ($skipForms));

            $dbh = $this->conn;

            // deactivated forms are not longer exist in survey forms
            $currentForms = $this->getAllForms ();
            if ( $this->debug ) logs ("[DEBUG] currentForms: " . json_encode ($currentForms));

            $delForms = array_diff ($currentForms, $surveyForms);
            if ( $this->debug ) logs ("[DEBUG] delForms: " . json_encode ($delForms));

            foreach ($delForms as $cForm) {
                $query = "UPDATE [dbo].[impowr_form_controls] 
                          SET date_deactivated = '" . $this->today . "' 
                          WHERE form_name = '" . $cForm . "'
                          AND date_deactivated IS NULL
                          AND job_id = " . $this->jobInfo["id"];
                $sth   = $dbh->query ($query);
            }

            // update current form list
            $currentForms = array_diff ($currentForms, $delForms);
            if ( $this->debug ) logs ("[DEBUG] currentForms after removing delete froms: " . json_encode ($currentForms));

            // add new forms from survey into form controls table
            $newForms = array_diff ($surveyForms, $currentForms);
            if ( $this->debug ) logs ("[DEBUG] newForms: " . json_encode ($newForms));

            $chunks = array_chunk ($newForms, 100);
            foreach ($chunks as $forms) {
                $query = "INSERT INTO [dbo].[impowr_form_controls] VALUES ";
                foreach ($forms as $form) {
                    $importNeed = in_array ($form, $skipForms) ? 0 : 1;
                    $query .= "('" . $form . "', " . $importNeed . ", '" . $this->today . "', NULL, " . $this->jobInfo["id"] . "),";
                }
                $query = substr_replace ($query, ';', strrpos ($query, ','), 1);
                $sth   = $dbh->query ($query);
            }

            // update need_import value on all exist activated forms 
            // not include those new once since it already have correct import need value
            foreach ($currentForms as $cForm) {
                $skip = in_array ($cForm, $skipForms) ? 1 : 0;
                if ( $skip ) {
                    $query = "UPDATE [dbo].[impowr_form_controls] 
                            SET import_need = 0  
                            WHERE form_name = '" . $cForm . "'
                            AND date_deactivated IS NULL
                            AND job_id = " . $this->jobInfo["id"];
                    $sth   = $dbh->query ($query);
                }
            }

            $dbh = null;
            $msg = "[INFO] Total updated forms:  new " . count ($newForms) . " and delete " . count ($delForms);
            logs ($msg . " <br/>");
            return array(
                "success" => true,
                "message" => $msg,
                "forms"   => $this->getAllForms ()  //$this->getRequiredForms ();
            );
        }
        catch ( PDOException $e ) {
            $msg = "[ERROR] Error in updateImportFields: " . json_encode ($e->getMessage ());
            logs ($msg . "<br/>");
            return array(
                "success" => false,
                "message" => $msg,
                "forms"   => []
            );
        }

    }

    public function getAllFields ()
    {
        return $this->getImportFields (true);
    }

    public function getRequiredFields ()
    {
        return $this->getImportFields (false, 0);
    }

    public function getBlankFields ()
    {
        return $this->getImportFields (false, 1);
    }

    public function getImportFields ($all, $showBlank = 0)
    {
        try {
            $query = "SELECT * FROM [dbo].[impowr_field_controls]
                        WHERE job_id = " . $this->jobInfo["id"] . " 
                        AND ( date_deactivated IS NULL OR date_deactivated > :date ) 
                        AND date_activated <= :date1";

            if ( ! $all ) $query .= " AND show_blank = " . $showBlank;

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->bindParam (":date", $this->today, PDO::PARAM_STR);
            $sth->bindParam (":date1", $this->today, PDO::PARAM_STR);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row["field_name"]);
            }

            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in getImportFields: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return [];
        }
    }

    //INSERT INTO dbo.impowr_field_controls if fields doesnt exist or not activated
    public function updateImportFields ($surveyFields, $blankFields = [])
    {
        try {
            if ( $this->debug ) logs ("[DEBUG] surveyFields | blankFields: " . json_encode ($surveyFields) . " | " . json_encode ($blankFields));

            $dbh = $this->conn;

            // deactivated fields are not longer exist in survey fields
            $currentFields = $this->getAllFields (); //$this->getRequiredFields ();
            if ( $this->debug ) logs ("[DEBUG] currentFields: " . json_encode ($currentFields));

            $delFields = array_diff ($currentFields, $surveyFields);
            if ( $this->debug ) logs ("[DEBUG] delFields: " . json_encode ($delFields));

            foreach ($delFields as $cField) {
                $query = "UPDATE [dbo].[impowr_field_controls] 
                            SET date_deactivated = '" . $this->today . "' 
                            WHERE field_name = '" . $cField . "'
                            AND date_deactivated IS NULL
                            AND job_id = " . $this->jobInfo["id"];
                $sth   = $dbh->query ($query);
            }

            // update current fields list
            $currentFields = array_diff ($currentFields, $delFields);
            if ( $this->debug ) logs ("[DEBUG] currentFields after removing delete fields: " . json_encode ($currentFields));

            // add new fields into form controls
            $newFields = array_diff ($surveyFields, $currentFields);
            if ( $this->debug ) logs ("[DEBUG] newFields: " . json_encode ($newFields));

            $chunks = array_chunk ($newFields, 100);
            foreach ($chunks as $fields) {
                $query = "INSERT INTO [dbo].[impowr_field_controls] VALUES ";
                foreach ($fields as $field) {
                    $showBlank = in_array ($field, $blankFields) ? 1 : 0;
                    $query .= "('" . $field . "', " . $showBlank . ", '" . $this->today . "', NULL, " . $this->jobInfo["id"] . "),";
                }
                $query = substr_replace ($query, ';', strrpos ($query, ','), 1);
                $sth   = $dbh->query ($query);
            }

            // update show blank value on all exist activated fields (not include those new once since it already have correct show blank value)
            foreach ($currentFields as $cField) {
                $showBlank = in_array ($cField, $blankFields) ? 1 : 0;
                if ( $showBlank ) {
                    $query = "UPDATE [dbo].[impowr_field_controls] 
                            SET show_blank = " . $showBlank . "
                            WHERE field_name = '" . $cField . "' 
                            AND date_deactivated IS NULL
                            AND job_id = " . $this->jobInfo["id"];
                    $sth   = $dbh->query ($query);
                }
            }

            $dbh = null;
            logs ("[INFO] Total updated fields:  new " . count ($newFields) . ", delete " . count ($delFields) . ", and delete " . count ($delFields) . " <br/>");
            return $this->getRequiredFields (); //$this->getImportFields ();
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in updateImportFields: " . json_encode ($e->getMessage ()) . "<br/>");
            return [];
        }
    }

    public function getFieldNameByForms ($forms = [])
    {
        if ( count ($forms) == 0 ) return [];
        $formString = "'" . implode ("', '", $forms) . "'";
        try {
            $query = "SELECT distinct(field_name) FROM [dbo].[impowr_dictionary_controls]
                        WHERE job_id = " . $this->jobInfo["id"] . " AND form_name in (" . $formString . ")";
            $dbh   = $this->conn;
            $sth   = $dbh->prepare ($query);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row["field_name"]);
            }

            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in getDictionary: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return [];
        }
    }

    public function deactivatedForms ($deactivatedDate)
    {
        try {
            $dbh = $this->conn;

            $query = "UPDATE [dbo].[impowr_form_controls] SET date_deactivated = '" . $deactivatedDate . "' WHERE job_id = " . $this->jobInfo["id"];
            $sth   = $dbh->query ($query);

            $dbh = null;
            $msg = "[INFO] Deactivated all forms from Job " . $this->jobInfo["job_name"];
            logs ($msg . " <br/>");
            return array(
                "success" => true,
                "message" => $msg,
            );
        }
        catch ( PDOException $e ) {
            $msg = "[ERROR] Error in deactivatedForms: " . json_encode ($e->getMessage ());
            logs ($msg . "<br>" . $query);
            return array(
                "success" => false,
                "message" => $msg . "<br>" . $query
            );
        }
    }

    public function deactivatedFields ($deactivatedDate)
    {
        try {
            $dbh = $this->conn;

            $query = "UPDATE [dbo].[impowr_field_controls] SET date_deactivated = '" . $deactivatedDate . "' WHERE job_id = " . $this->jobInfo["id"];
            $sth   = $dbh->query ($query);

            $dbh = null;
            $msg = "[INFO] Deactivated all fields from Job " . $this->jobInfo["job_name"];
            logs ($msg . "<br>");
            return array(
                "success" => true,
                "message" => $msg,
            );
        }
        catch ( PDOException $e ) {
            $msg = "[ERROR] Error in deactivatedFields: " . json_encode ($e->getMessage ());
            logs ($msg . "<br>" . $query);
            return array(
                "success" => false,
                "message" => $msg . "<br>" . $query
            );
        }
    }

    public function addJobTeams ($jobID, $teams)
    {
        if ( ! $jobID || count ($teams) == 0 ) {
            logs ("Invalid Job ID or team list is empty" . "<br/>", true, true);
            return false;
        }
        try {
            $teamQuery = $this->getTeamQueryStrings ($jobID, $teams);
            $query     = "INSERT INTO [dbo].[impowr_team_jobs] VALUES " . $teamQuery;
            $dbh       = $this->conn;
            $sth       = $dbh->query ($query);
            $dbh       = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("Error in addJobTeams: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    public function updateJobTeams ($jobID, $teams)
    {
        if ( ! $jobID || count ($teams) == 0 ) {
            logs ("Invalid job ID or team list is empty" . "<br/>", true, true);
            return false;
        }
        $dbh = $this->conn;
        try {
            // Start a transaction
            $dbh->beginTransaction ();

            // Prepare and execute the first query - delete all teams for the job
            $sql1  = "DELETE FROM [dbo].[impowr_team_jobs] WHERE job_id = :jobID";
            $stmt1 = $dbh->prepare ($sql1);
            $stmt1->execute ([
                ':jobID' => $jobID
            ]);

            // Add all teams into the job
            $teamQuery = $this->getTeamQueryStrings ($jobID, $teams);
            $sql2      = "INSERT INTO [dbo].[impowr_team_jobs] VALUES " . $teamQuery;
            $stmt2     = $dbh->prepare ($sql2);
            $stmt2->execute ();

            // Commit the transaction
            $dbh->commit ();
            $dbh = null;
            return true;
        }
        catch ( PDOException $e ) {
            // Rollback the transaction if something goes wrong
            $dbh->rollBack ();
            logs ("Error in updateJobTeams: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    private function getTeamQueryStrings ($jobID, $teams)
    {
        $query = "";
        foreach ($teams as $team) {
            $query .= "(" . $team . "," . $jobID . "),";
        }
        $query = rtrim ($query, ",");  // remove last comma
        return $query;
    }

    public function deleteJobTeams ($jobID)
    {
        if ( ! $jobID || $jobID == "" ) {
            logs ("Invalid job ID" . "<br/>", true, true);
            return false;
        }
        try {
            $query = "DELETE FROM [dbo].[impowr_team_jobs] WHERE job_id = " . $jobID;
            $dbh   = $this->conn;
            $sth   = $dbh->query ($query);
            $dbh   = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("Error in deleteJobTeams: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }
}