<?php

class FieldControls
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


    public function read ($key = '', $value = '', $partial = 'No', $sort = 'id', $dir = 'asc', $offset = 0, $limit = 10, $jobId = "all")
    {
        $ikeys   = [ "project_name", "source_institution", "source_project_name" ];
        $noQuote = [ "f.job_id", "f.show_blank" ];

        if ( $key == "show_blank" ) {
            $value = strtoupper ($value) == 'TRUE' ? 1 : 0;
        }

        try {
            $fieldsQuery = "SELECT f.*, i.project_name, i.source_institution, i.source_project_name ";
            $totalQuery  = "SELECT COUNT(*) as count ";

            $query = "FROM impowr_field_controls AS f
                      LEFT JOIN impowr_jobs AS i on f.job_id = i.id
                      WHERE (f.date_deactivated IS NULL OR f.date_deactivated > :date)";

            if ( $jobId != "all" && $jobId != "" ) {
                $query .= " AND f.job_id = " . $jobId;
            }

            if ( $key !== '' && $value !== '' ) {

                $key  = in_array ($key, $ikeys) ? "i." . $key : "f." . $key;
                $sort = in_array ($sort, $ikeys) ? "i." . $sort : "f." . $sort;

                if ( in_array ($key, $noQuote) ) {
                    $query .= " AND " . $key . " = " . $value;
                } else {
                    if ( $partial === 'YES' ) {
                        $query .= " AND (" . $key . " = '" . $value . "' OR " . $key . " like '%" . $value . "' OR " . $key . " like '" . $value . "%' OR " . $key . " like '%" . $value . "%')";
                    } else {
                        $query .= " AND (" . $key . " = ''" . $value . ")";
                    }
                }
            }

            if ( $this->loginUser["super_admin"] != "YES" ) {
                $query .= " AND job_id IN (
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

            $totalQuery .= $query;
            $fieldsQuery .= $query . " ORDER BY " . $sort . " " . $dir .
                " OFFSET " . $offset . " ROWS
                            FETCH FIRST " . $limit . " ROWS ONLY";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($fieldsQuery);
            $sth->bindParam (":date", $this->today, PDO::PARAM_STR);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                $row['records']     = ""; //dont pass reocrds for now since its huge
                $row['show_blank']  = $row['show_blank'] == 1;
                $row["is_editable"] = $this->isEditable ($row["job_id"]);
                array_push ($data, $row);
            }

            $count = 0;
            $sth1  = $dbh->prepare ($totalQuery);
            $sth1->bindParam (":date", $this->today, PDO::PARAM_STR);
            $sth1->execute ();

            while ( $row = $sth1->fetch (PDO::FETCH_ASSOC) ) {
                $count = $row["count"];
            }

            $dbh = null;
            return array( "data" => $data, "total" => $count );
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in read fields: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return array( "data" => [], "total" => 0 );
        }
    }

    function isEditable ($jobId)
    {
        if ( count ($this->loginUser["team_jobs"]) > 0 ) {
            foreach ($this->loginUser["team_jobs"] as $job) {
                if ( $job["job_id"] == $jobId && ($job["is_editable"] == 1 || $job["is_admin"] == 1) ) {
                    return true;
                }
            }
        }
        return false;
    }


    public function updateFieldControl ($params)
    {
        try {
            $query = "UPDATE [dbo].[impowr_field_controls] SET  
                    show_blank = ?,
                    date_activated = ? 
                    WHERE id = ?";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute ($params);
            $dbh = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in updateFieldControl: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }
}