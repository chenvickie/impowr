<?php

class User
{
    private $conn;
    private $today;
    public $info = null;

    public function __construct ($db)
    {
        $this->conn  = $db;
        $this->today = date ("Y-m-d");

    }

    // read user permissions by user name
    function read ($userName)
    {
        try {
            $query = "SELECT * FROM dbo.impowr_user_permissions
            WHERE user_name = '" . $userName . "'";
            $dbh   = $this->conn;
            $sth   = $dbh->query ($query);
            $data  = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                $row["team_jobs"] = $this->getTeamJobPermissions ($row["user_name"]);
                array_push ($data, $row);
            }
            if ( count ($data) > 0 ) {
                $this->info = $data[0];
            }
            $dbh = null;
            return $this->info;
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            die ();
        }
    }

    function updateLastLogin ()
    {
        if ( isset ($this->info) && $this->info["user_name"] != "" ) {
            try {
                $query = "UPDATE dbo.impowr_user_permission
                     SET last_login ='" . $this->today . "'
                     WHERE user_name = '" . $this->info["user_name"] . "'";
                $dbh   = $this->conn;
                $sth   = $dbh->query ($query);
                $dbh   = null;
                return true;
            }
            catch ( PDOException $e ) {
                logs ("[ERROR] Error in updateLastLogin: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
                return false;
            }
        }
    }

    function getTeamJobPermissions ($username)
    {
        try {
            $query = "SELECT
                    [user_name]
                    ,[is_editable]
                    ,[is_admin]
                    ,[team_name]
                    ,[description]
                    ,[id] AS team_id
                    ,[job_id]
                FROM [dbo].[impowr_team_users] AS itu 
                LEFT JOIN [dbo].[impowr_teams] AS it ON itu.team_id = it.id
                LEFT JOIN [dbo].[impowr_team_jobs] AS itj ON itj.team_id = it.id
                WHERE user_name ='" . $username . "' AND it.date_deleted is NULL";

            $dbh = $this->conn;
            $sth = $dbh->query ($query);

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                array_push ($data, $row);
            }
            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("Error!: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return [];
        }
    }
}

?>