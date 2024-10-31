<?php

class Jobs
{
    private $conn;
    private $today;
    private $tomorrow;
    public $loginUser = null;

    public function __construct ($db, $loginUser = null)
    {
        $this->conn      = $db;
        $this->today     = date ("Y-m-d");
        $this->tomorrow  = date ('Y-m-d', strtotime ('+1 day'));
        $this->loginUser = $loginUser;
    }

    public function read ($key = '', $value = '', $partial = 'No', $sort = 'id', $dir = 'asc', $offset = 0, $limit = 100)
    {
        $noQuote = [ "id" ];

        try {
            $instQuery  = "SELECT * ";
            $totalQuery = "SELECT COUNT(*) as count ";

            $query = "FROM impowr_jobs WHERE date_deleted is null";

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
            }

            if ( $this->loginUser["super_admin"] != "YES" ) {
                $query .= " AND id IN (
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
            $instQuery .= $query . " ORDER BY " . $sort . " " . $dir .
                " OFFSET " . $offset . " ROWS
                            FETCH FIRST " . $limit . " ROWS ONLY";


            $dbh = $this->conn;
            $sth = $dbh->query ($instQuery);

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {

                // get job's team
                $row["job_teams"] = $this->getJobTeams ($row["id"]);

                // only job admin and super admin has right to edit jobs
                if ( $this->loginUser["user_name"] == $row["job_admin"] || $this->loginUser["super_admin"] == "YES" ) {
                    $row["is_editable"] = true;
                } else {
                    $row["is_editable"] = false;
                    // do not show token info on project table
                    $row["source_project_token"] = "**************";
                    $row["project_token"]        = "**************";
                }

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

    // function isEditable ($jobId)
    // {
    //     if ( count ($this->loginUser["team_jobs"]) > 0 ) {
    //         foreach ($this->loginUser["team_jobs"] as $job) {
    //             if ( $job["job_id"] == $jobId && $job["is_admin"] == 1 ) {
    //                 return true;
    //             }
    //         }
    //     }
    //     return false;
    // }

    public function getJobInfo ($id)
    {
        try {
            $query = "SELECT * FROM [dbo].[impowr_jobs] WHERE id = '" . $id . "'";
            $dbh   = $this->conn;
            $sth   = $dbh->query ($query);

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row);
            }

            $dbh = null;
            return $data[0];
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }

    public function getActivatedJobInfo ($id)
    {
        try {
            $query = "SELECT * FROM [dbo].[impowr_jobs] WHERE id = '" . $id . "' 
            AND ( date_deactivated IS NULL OR date_deactivated = '' OR date_deactivated > :date ) 
            AND ( date_deleted is NULL OR date_deleted = '') 
            AND date_activated <= :date1";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->bindParam (":date", $this->today, PDO::PARAM_STR);
            $sth->bindParam (":date1", $this->today, PDO::PARAM_STR);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row);
            }

            $dbh = null;
            return $data[0];
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }


    public function getActivatedJobInfoByJobName ($job_name)
    {
        try {
            $query = "SELECT * FROM [dbo].[impowr_jobs] WHERE date_deleted is null AND job_name = '" . $job_name . "' 
            AND ( date_deactivated IS NULL OR date_deactivated = '' OR date_deactivated > :date ) 
            AND (date_deleted is NULL OR date_deleted = '')
            AND date_activated <= :date1";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->bindParam (":date", $this->today, PDO::PARAM_STR);
            $sth->bindParam (":date1", $this->today, PDO::PARAM_STR);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row);
            }

            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }

    public function getAllJobs ()
    {
        try {
            $query = "Select * From [dbo].[impowr_jobs] WHERE date_deleted is Null";
            $dbh   = $this->conn;
            $sth   = $dbh->query ($query);

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row);
            }

            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }

    public function getAllActivatedJobs ()
    {
        try {
            $query = "SELECT * FROM [dbo].[impowr_jobs] 
            WHERE ( date_deactivated IS NULL OR date_deactivated = '' OR date_deactivated > :date ) 
            AND (date_deleted is NULL OR date_deleted = '')
            AND date_activated <= :date1";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->bindParam (":date", $this->today, PDO::PARAM_STR);
            $sth->bindParam (":date1", $this->today, PDO::PARAM_STR);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row);
            }

            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return [];
        }
    }

    public function getScheduledJobs ()
    {
        $scheduledJobs = [];
        $jobs          = $this->getAllActivatedJobs ();
        $today         = getdate ();

        foreach ($jobs as $job) {
            $transfer_frequency = $job["transfer_frequency"];
            $scheduled_on       = $job["scheduled_on"];

            switch ($transfer_frequency) {
                case "daily":
                    $scheduledJobs[] = $job;
                    break;

                case "weekly":
                    if ( $scheduled_on == $today["weekday"] ) {
                        $scheduledJobs[] = $job;
                    }
                    break;

                case "monthly":
                    $month_end = strtotime ('last day of this month', time ());
                    $last_date = getdate ($month_end);
                    if ( $scheduled_on == $today["mday"] || ($scheduled_on == "Last day" && $today["mday"] == $last_date["mday"]) ) {
                        $scheduledJobs[] = $job;
                    }
                    break;

                case "custom":
                    list( $d, $t ) = explode (" ", $scheduled_on);
                    if ( $d == $this->today ) {
                        $scheduledJobs[] = $job;
                    }
                    break;

                default;
            }
        }

        return $scheduledJobs;
    }

    public function getJobTeams ($job_id)
    {
        try {
            $query = "SELECT *
            FROM [dbo].[impowr_team_jobs] AS j
            LEFT JOIN [dbo].[impowr_teams] AS t on j.team_id = t.id";

            if ( $job_id ) $query .= " WHERE j.job_id = '" . $job_id . "'";

            $dbh  = $this->conn;
            $sth  = $dbh->query ($query);
            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                // Create a new stdClass object
                $team       = new stdClass();
                $team->id   = $row["team_id"];
                $team->name = $row["team_name"];

                // Push the object to the array
                array_push ($data, $team);
            }
            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }
}