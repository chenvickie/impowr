<?php

include_once '../utils/curl.php';

class IMPOWRLibrary
{
    private $config;
    private $sslEnable;

    /**
     * @param $jobInfo job information
     * @param $sslEnable enable SSL
     */
    public function __construct ($jobInfo, $sslEnable = false)
    {
        $this->config    = $jobInfo;
        $this->sslEnable = $sslEnable;
    }

    /**
     * Get Meta Data Dictionary from source site
     */
    public function getDictionary ($forms = null, $fields = null)
    {
        $options = array(
            'token'        => $this->config["source_project_token"],
            'content'      => 'metadata',
            'format'       => 'json',
            'returnFormat' => 'json',
            'fields'       => $fields,
            'forms'        => $forms
        );
        $res     = $this->callAPI ("POST", $this->config["source_project_url"], $options);
        return $res;
    }


    /**
     * Get Meta Data from dest site
     */
    public function getDestDictionary ($forms = null, $fields = null)
    {
        $options = array(
            'token'        => $this->config["project_token"],
            'content'      => 'metadata',
            'format'       => 'json',
            'returnFormat' => 'json',
            'fields'       => $fields,
            'forms'        => $forms
        );
        $res     = $this->callAPI ("POST", $this->config["project_url"], $options);
        return $res;
    }

    /**
     * Get list of forms source site
     */
    public function getForms ()
    {
        $options = array(
            'token'        => $this->config["source_project_token"],
            'content'      => 'instrument',
            'format'       => 'json',
            'returnFormat' => 'json',
        );
        $res     = $this->callAPI ("POST", $this->config["source_project_url"], $options);
        return $res ? array_column ($res, 'instrument_name') : null;
    }

    /**
     * Get list of forms from the dest site 
     */
    public function getDestForms ()
    {
        $options = array(
            'token'        => $this->config["project_token"],
            'content'      => 'instrument',
            'format'       => 'json',
            'returnFormat' => 'json',
        );
        $res     = $this->callAPI ("POST", $this->config["project_url"], $options);
        return $res ? array_column ($res, 'instrument_name') : null;
    }

    /**
     * Get list of export fields from the source site
     */
    public function getExportFields ()
    {
        $options = array(
            'token'        => $this->config["source_project_token"],
            'content'      => 'exportFieldNames',
            'format'       => 'json',
            'returnFormat' => 'json',
        );
        $res     = $this->callAPI ("POST", $this->config["source_project_url"], $options);
        return $res ? array_column ($res, 'export_field_name') : null;
    }

    /**
     * Get list of export fields from the dest site
     */
    public function getDestExportFields ()
    {
        $options = array(
            'token'        => $this->config["project_token"],
            'content'      => 'exportFieldNames',
            'format'       => 'json',
            'returnFormat' => 'json',
        );
        $res     = $this->callAPI ("POST", $this->config["project_url"], $options);
        return $res ? array_column ($res, 'export_field_name') : null;
    }

    /***
     * Get list of records from source site based on the forms and fields
     * If both are null, it will return all fields on the project
     * If list of forms, it will return all fields on the forms
     * If list of fields without form(s), it will return all fields across the survey
     *
     * example forms =  array(
     *   "impowr_study_adminme", "impowr_study_adminyou",
     * )
     *
     * example fields =  array(
     *   "demguid", "imp_followup_date"
     * )
     */
    public function getRecords ($forms = null, $fields = null)
    {
        $options = array(
            'token'                  => $this->config["source_project_token"],
            'content'                => 'record',
            'action'                 => 'export',
            'format'                 => 'json',
            'type'                   => 'flat',
            'rawOrLabel'             => 'raw',
            'rawOrLabelHeaders'      => 'raw',
            'exportCheckboxLabel'    => 'false',
            'exportSurveyFields'     => 'false',
            'exportDataAccessGroups' => 'false',
            'returnFormat'           => 'json',
        );


        if ( $forms && count ($forms) > 0 ) {
            $options["forms"] = $forms;
        }

        if ( $fields && count ($fields) > 0 ) {
            $options["fields"] = $fields;
        }

        $res = $this->callAPI ("POST", $this->config["source_project_url"], $options);

        #ISSUE, when we have same record id on different remote site, it will override the previous records since record id is the indentifier for the data
        #So we need to prefix record id based on the remote site id, TODO: maybe with other prefix?
        foreach ($res as $k => $r) {
            if ( isset ($res[$k]) && isset ($res[$k]["record_id"]) ) {
                $res[$k]["record_id"] = $this->config["source_project_id"] . "." . $r["record_id"];
            }
        }
        return $res;
    }

    /**
     * Import data to the destination site
     */
    public function importRecords ($records = [])
    {
        if ( count ($records) == 0 ) {
            return array( "error" => "Empty records, Do nothing!" );
        }
        $options = array(
            'token'             => $this->config["project_token"],
            'content'           => 'record',
            'action'            => 'import',
            'format'            => 'json',
            'type'              => 'flat',
            'overwriteBehavior' => 'overwrite',
            'forceAutoNumber'   => 'false',
            'data'              => json_encode ($records),
            'returnContent'     => 'count',
            'returnFormat'      => 'json'
        );
        return $this->callAPI ("POST", $this->config["project_url"], $options);
    }

    /**
     * Get project info from source site
     * @return mixed
     */
    public function exportProjectInfo ()
    {
        $options = array(
            'token'        => $this->config["source_project_token"],
            'content'      => 'project',
            'format'       => 'json',
            'returnFormat' => 'json'

        );
        return $this->callAPI ("POST", $this->config["source_project_url"], $options);
    }

    /**
     * Get project info from destination site
     * @return mixed
     */
    public function exportDestProjectInfo ()
    {
        $options = array(
            'token'        => $this->config["project_token"],
            'content'      => 'project',
            'format'       => 'json',
            'returnFormat' => 'json'

        );
        return $this->callAPI ("POST", $this->config["project_url"], $options);
    }


    /**
     * Curl Connection with api call
     * @param mixed $method
     * @param mixed $url
     * @param mixed $data
     * @throws \Exception
     * @return mixed
     */
    public function callAPI ($method, $url, $data)
    {
        try {
            //curl initialization
            $ch = curl_init ();

            //check if initialization had gone wrong
            if ( $ch === false ) {
                throw new Exception('failed to initialize');
            }

            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, $this->sslEnable);
            curl_setopt ($ch, CURLOPT_VERBOSE, 0);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt ($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt ($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt ($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query ($data, '', '&'));

            // EXECUTE:
            $result = curl_exec ($ch);

            $httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
            if ( $httpCode != 200 ) {
                logs ("Return code is {$httpCode} \n"
                    . curl_error ($ch), true);
            }

            if ( ! $result ) {
                logs ('Curl exec failed without result', true);
                throw new Exception(curl_error ($ch), curl_errno ($ch));
            }

            curl_close ($ch);
            $res = json_decode ($result, true);
            return $res;

        }
        catch ( Exception $e ) {
            //trigger_error (sprintf ('Curl failed with error #%d: %s', $e->getCode (), $e->getMessage ()), E_USER_ERROR);
            logs (sprintf ('Curl failed with error #%d: %s', $e->getCode (), $e->getMessage ()), true);
            return false;
        }
    }
}
