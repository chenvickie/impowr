<?php

class Audits
{
    private $conn;
    private $today;
    public $loginUser = null;

    public function __construct ($db, $loginUser = null)
    {
        $this->conn      = $db;
        $this->today     = date ("Y-m-d");
        $this->loginUser = $loginUser;
    }

    public function read ($key = '', $value = '', $partial = 'No', $sort = 'process_start', $dir = 'desc', $offset = 0, $limit = 10, $jobId = "all")
    {
        $ikeys   = [ "project_name", "source_institution", "source_project_name" ];
        $noQuote = [ "a.job_id" ];

        try {
            $auditsQuery = "SELECT * ";
            $totalQuery  = "SELECT COUNT(*) as count ";

            $query = "FROM impowr_audit_logs AS a
                      LEFT JOIN impowr_jobs AS i on a.job_id = i.id";

            if ( $this->loginUser["super_admin"] != "YES" ) {
                $query .= " WHERE a.job_id IN (
                SELECT job_id 
                FROM [dbo].[impowr_team_jobs]
                WHERE team_id IN (
                    SELECT [team_id]
                    FROM [dbo].[impowr_team_users] AS itu 
                    LEFT JOIN [dbo].[impowr_teams] AS it ON itu.team_id = it.id
                    WHERE user_name ='" . $this->loginUser['user_name'] . "'
                )
            )";
            }
            $key  = in_array ($key, $ikeys) ? "i." . $key : "a." . $key;
            $sort = in_array ($sort, $ikeys) ? "i." . $sort : "a." . $sort;

            if ( $key !== '' && $value !== '' ) {
                if ( in_array ($key, $noQuote) ) {
                    $query .= " AND " . $key . " = " . $value;
                } else {
                    if ( $partial === 'YES' ) {
                        $query .= " AND (" . $key . " = '" . $value . "' OR " . $key . " like '%" . $value . "' OR " . $key . " like '" . $value . "%' OR " . $key . " like '%" . $value . "%')";
                    } else {
                        $query .= " AND (" . $key . " = ''" . $value . ")";
                    }
                }
                if ( $jobId != "all" && $jobId != "" ) {
                    $query .= " AND a.job_id = " . $jobId;
                }

            } else if ( $jobId != "all" && $jobId != "" ) {
                $query .= " AND a.job_id = " . $jobId;
            }

            $totalQuery .= $query;
            $auditsQuery .= $query . " ORDER BY " . $sort . " " . $dir .
                " OFFSET " . $offset . " ROWS
                            FETCH FIRST " . $limit . " ROWS ONLY";

            $dbh = $this->conn;
            $sth = $dbh->query ($auditsQuery);

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                $row['records'] = ""; //dont pass reocrds for now since its huge
                array_push ($data, $row);
            }

            $count = 0;
            $res   = $dbh->query ($totalQuery);
            while ( $row = $res->fetch (PDO::FETCH_ASSOC) ) {
                $count = $row["count"];
            }

            $dbh = null;
            return array( "data" => $data, "total" => $count );
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }

    public function save ($params)
    {
        try {
            $query = "INSERT[dbo].[impowr_audit_logs] (forms, fields, records, forms_count, fields_count, records_count, process_start, process_end, status, note, triggered_by, job_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
            $dbh   = $this->conn;
            $sth   = $dbh->prepare ($query);
            $sth->execute ($params);

            $dbh = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }
}