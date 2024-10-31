<?php
include_once '../utils/utils.php';
include_once '../utils/email.php';
include_once '../objects/job.php';
include_once '../objects/audits.php';
include_once '../objects/impowr.php';

class ImpowrTransfer
{
    private $conn;
    private $debug = true;
    private $today;
    private $triggered_by;
    public $jobInfo;
    public $args;  //json
    public $errorMsg = "";

    public function __construct ($db, $jobInfo, $triggered_by = 'system', $args = null)
    {
        $this->conn         = $db;
        $this->today        = date ("Y-m-d");
        $this->jobInfo      = $jobInfo;
        $this->triggered_by = $triggered_by;
        $this->args         = $args;
    }

    public function tranfer ()
    {
        $jobInfo = $this->jobInfo;
        $job     = new Job($this->conn, $jobInfo['id']);

        // create a new payload to store data for audit logs
        $payload                  = array();
        $payload["process_start"] = date ("Y-m-d H:i:s");

        // create a new redcap impowr library based on the site name
        logs ("*************************************************************", true);
        logs ("********* BEGIN IMPOWR TRANSFER " . date ('Y-m-d H:i:s') . " *********", true);

        logs ("*************************************************************\n", true);
        logs ("[PROJECT FROM] : " . $jobInfo["source_project_name"] . "\n", true);
        logs ("[PROJECT TO] : " . $jobInfo["project_name"] . "\n", true);

        // init impowr library with site INFO
        $library = new IMPOWRLibrary($jobInfo);

        // get all forms on the destination site 
        $destForms = $library->getDestForms ();

        // get all forms on source form control table 
        $sourceForms = $job->getRequiredForms ();

        // only process forms exist on both source and dest sites
        $matchedForms = array_intersect ($sourceForms, $destForms);

        $payload["forms"]       = $matchedForms;
        $payload["forms_count"] = count ($matchedForms);

        logs ("[INFO] Total Count of source | dest | matched forms: " . count ($sourceForms) . " | " . count ($destForms) . " | " . count ($matchedForms));
        if ( $this->debug ) logs ("[DEBUG] Source | dest | matched forms\n: " . json_encode ($sourceForms) . " | " . json_encode ($destForms) . " | " . json_encode ($matchedForms));

        // get all fields on the destination site
        $destFields = $library->getDestExportFields ();

        // get all fiels on source field control table if we didnt pass any particular fields on the params
        $sourceFields = $job->getAllFields ();

        // only process fields exist on both source and dest sites
        $matchedFields = array_intersect ($sourceFields, $destFields);

        $payload["fields"]       = $matchedFields;
        $payload["fields_count"] = count ($matchedFields);

        logs ("[INFO] Total Count of source | dest | matched fields: " . count ($sourceFields) . " | " . count ($destFields) . " | " . count ($matchedFields));
        if ( $this->debug ) logs ("[DEBUG] Source | dest | matched fields\n: " . json_encode ($sourceFields) . " | " . json_encode ($destFields) . " | " . json_encode ($matchedFields));

        // run the import process when importRecords = Y and the matched fields are not empty
        if ( count ($matchedFields) > 0 ) {

            logs ("[INFO] Export data from " . $jobInfo['source_project_url'], true);
            $data = $library->getRecords (null, $matchedFields);

            $skipForms = $job->getSkipForms ();
            if ( $this->debug ) logs ("[DEBUG] SkipForms\n" . json_encode ($skipForms), true);

            if ( count ($skipForms) > 0 ) {
                $skipFields = $job->getFieldNameByForms ($skipForms);
                if ( $this->debug ) logs ("[DEBUG]  SkipFields\n" . json_encode ($skipFields), true);

                if ( count ($skipFields) > 0 ) {
                    $data = emptyDataFields ($data, $skipFields);
                }
            }

            $blankFields = $job->getBlankFields ();
            if ( $this->debug ) logs ("[DEBUG] BlankFields\n" . json_encode ($blankFields), true);

            if ( count ($blankFields) > 0 ) {
                $data = emptyDataFields ($data, $blankFields);
            }

            $payload["forms"]       = array_diff ($matchedForms, $skipForms);
            $payload["forms_count"] = count ($payload["forms"]);

            if ( $this->debug ) logs ("[DEBUG] Forms\n" . json_encode (array_diff ($matchedForms, $skipForms)), true);

            if ( $payload["forms_count"] > 0 ) {

                $payload["fields"]       = array_diff ($matchedFields, $blankFields);
                $payload["fields_count"] = count ($matchedFields) - count ($blankFields) > 0 ? count ($matchedFields) - count ($blankFields) : 0;

                $payload["records"]       = json_encode ($data);
                $payload["records_count"] = count ($data);

                logs ("[INFO] Total Count of records: " . $payload["records_count"]);

                // import the records we get from the source site to the dest site when data is not empty
                if ( count ($data) > 0 ) {
                    logs ("[INFO] Import data to " . $jobInfo['project_url'], true);
                    $res = $library->importRecords ($data);

                    if ( isset ($res["error"]) ) {
                        logs ("[ERROR]: " . $res["error"], true);
                        $this->errorMsg         = "ERROR: " . $res["error"];
                        $payload["status"]      = "Failed: " . $res["error"];
                        $payload["process_end"] = date ("Y-m-d H:i:s");
                        sendTransferErrorNotification ($jobInfo, $payload);
                    } else {
                        logs ("[INFO] Import " . $res["count"] . " records successfully!");
                        $this->errorMsg         = "";
                        $payload["status"]      = "Success: " . $res["count"] . " records";
                        $payload["process_end"] = date ("Y-m-d H:i:s");
                    }
                } else {
                    logs ("[WARN] No data Found", true);
                    $payload["status"]      = "No data found";
                    $payload["process_end"] = date ("Y-m-d H:i:s");
                }
            } else {
                logs ("[WARN] No Forms Found", true);
                $payload["fields"]        = null;
                $payload["fields_count"]  = 0;
                $payload["records"]       = null;
                $payload["records_count"] = 0;
                $payload["status"]        = "No forms found";
                $payload["process_end"]   = date ("Y-m-d H:i:s");
            }

        } else {
            logs ("[WARN] No Matched fields\n", true);
            logs ("[WARN] Destination fields\n" . json_encode ($destFields), true);
            logs ("[WARN] Source fields\n" . json_encode ($sourceFields), true);
            $payload["fields"]        = null;
            $payload["fields_count"]  = 0;
            $payload["records"]       = null;
            $payload["records_count"] = 0;
            $payload["status"]        = "No fields found";
            $payload["process_end"]   = date ("Y-m-d H:i:s");
        }

        logs ("\n", true);
        if ( $this->debug ) logs ("[DEBUG] Payload\n" . json_encode ($payload), true);
        return $this->auditLog ($payload);
    }


    public function auditLog ($payload)
    {

        // initialize Audit object
        $audits = new Audits($this->conn);

        $params = array(
            json_encode ($payload['forms']),
            json_encode ($payload['fields']),
            $payload['records'],
            $payload['forms_count'],
            $payload['fields_count'],
            $payload['records_count'],
            $payload['process_start'],
            $payload['process_end'],
            $payload['status'],
            json_encode ($this->args),
            $this->triggered_by,
            $this->jobInfo["id"],
        );

        // save log payload into db
        logs ('[INFO] insert audit logs in impowr_audit_logs table: ' . $this->jobInfo["job_name"] . "(" . $this->jobInfo["id"] . ")\n");
        logs ("**************************************************************", true);
        logs ("********** END IMPOWR TRANSFER " . date ('Y-m-d H:i:s') . " ***********", true);
        logs ("**************************************************************\n", true);
        return $audits->save ($params);
    }
}

?>