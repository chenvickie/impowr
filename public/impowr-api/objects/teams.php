<?php

class Teams
{
    private $conn;
    private $today;
    public $loginUser = null;
    private $teamID;

    public function __construct ($db, $loginUser = null, $team_id = null)
    {
        $this->conn      = $db;
        $this->today     = date ("Y-m-d");
        $this->teamID    = $team_id;
        $this->loginUser = $loginUser;
    }

    public function getTeamInfo ($id)
    {
        try {
            $query = "SELECT * From [dbo].[impowr_teams] WHERE id = '" . $id . "'";
            $dbh   = $this->conn;
            $sth   = $dbh->query ($query);

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                $row["team_users"] = $this->getTeamUsers ($row["id"]);
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


    public function getTeams ($key = '', $value = '', $partial = 'No', $sort = 'id', $dir = 'asc', $offset = 0, $limit = 10)
    {
        $noQuote      = [ "updated_on" ];
        $isSuperAdmin = $this->loginUser["super_admin"] == "YES";

        try {
            $teamsQuery = "SELECT * ";
            $totalQuery = "SELECT COUNT(*) as count ";

            $query = " FROM impowr_teams";

            $hasWhere = false;
            if ( ! $isSuperAdmin ) {
                $query .= " WHERE id IN (
                SELECT team_id 
                FROM impowr_team_users
                WHERE user_name ='" . $this->loginUser["user_name"] . "')";
                $hasWhere = true;
            }

            if ( $key !== '' && $value !== '' ) {
                $query .= $hasWhere ? " AND " : " WHERE ";
                if ( in_array ($key, $noQuote) ) {
                    $query .= $key . " = " . $value;
                } else {
                    if ( $partial === 'YES' ) {
                        $query .= $key . " = '" . $value . "' OR " . $key . " like '%" . $value . "' OR " . $key . " like '" . $value . "%' OR " . $key . " like '%" . $value . "%'";
                    } else {
                        $query .= $key . " = ''" . $value;
                    }
                }
            }

            $totalQuery .= $query;
            $teamsQuery .= $query . " ORDER BY " . $sort . " " . $dir .
                " OFFSET " . $offset . " ROWS
                FETCH FIRST " . $limit . " ROWS ONLY";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($teamsQuery);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                // get team users
                $row["team_users"] = $this->getTeamUsers ($row["id"]);

                //make is_editable to true if the login user is a super admin
                if ( $isSuperAdmin ) {
                    $row["is_editable"] = true;
                } else {
                    $row["is_editable"] = false;
                    foreach ($row["team_users"] as $tu) {
                        if ( $tu["user_name"] == $this->loginUser["user_name"] && $tu["is_admin"] == true ) {
                            $row["is_editable"] = true;
                        }
                    }
                }
                array_push ($data, $row);
            }

            $count = 0;
            $sth1  = $dbh->prepare ($totalQuery);
            $sth1->execute ();

            while ( $row = $sth1->fetch (PDO::FETCH_ASSOC) ) {
                $count = $row["count"];
            }

            $dbh = null;
            return array( "data" => $data, "total" => $count );
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in read Teams: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return array( "data" => [], "total" => 0 );
        }
    }

    public function addTeam ($params)
    {
        try {
            $query = "
                INSERT[dbo].[impowr_teams] (  
                  team_name,
                  description,
                  updated_on
                ) 
                VALUES (?,?,'" . $this->today . "')";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute (convertParams2QueryValues ($params));
            $lastInsertId = $dbh->lastInsertId ();

            $dbh = null;
            return $lastInsertId;
        }
        catch ( PDOException $e ) {
            logs ("Error in addTeam: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return null;
        }
    }

    public function updateTeam ($params)
    {
        try {
            $query = "UPDATE [dbo].[impowr_teams] SET  
                    team_name = ?,
                    description = ?,
                    updated_on = '" . $this->today . "'
                    WHERE id = ?";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute ($params);
            $dbh = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in updateTeam: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    public function addTeamUsers ($teamID, $users)
    {
        if ( ! $teamID || count ($users) == 0 ) {
            logs ("Invalid teamID or team user list is empty" . "<br/>", true, true);
            return false;

        }
        try {

            $userQuery = $this->getUserQueryStrings ($teamID, $users);
            $query     = "INSERT INTO [dbo].[impowr_team_users] VALUES " . $userQuery;
            $dbh       = $this->conn;
            $sth       = $dbh->query ($query);
            $dbh       = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("Error in addTeamUser: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    public function updateTeamUsers ($teamID, $users)
    {
        if ( ! $teamID || count ($users) == 0 ) {
            logs ("Invalid teamID or team user list is empty" . "<br/>", true, true);
            return false;
        }

        $dbh = $this->conn;

        try {
            // Start a transaction
            $dbh->beginTransaction ();

            // Prepare and execute the first query - delete all users for the team
            $sql1  = "DELETE FROM [dbo].[impowr_team_users] WHERE team_id = :teamID";
            $stmt1 = $dbh->prepare ($sql1);
            $stmt1->execute ([
                ':teamID' => $teamID
            ]);

            // Add all users into the team
            $userQuery = $this->getUserQueryStrings ($teamID, $users);
            $sql2      = "INSERT INTO [dbo].[impowr_team_users] VALUES " . $userQuery;
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
            logs ("Error in updateTeamUsers: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    private function getUserQueryStrings ($teamID, $users)
    {
        $query = "";
        foreach ($users as $user) {
            if ( $this->upsertUser ($user) ) {
                $is_editable = $this->convertStringToNumber ($user["is_editable"]);
                $is_admin    = $this->convertStringToNumber ($user["is_admin"]);
                $query .= "(" . $teamID . ",";
                $query .= "'" . trim ($user["user_name"]) . "',";
                $query .= (int) $is_editable . ",";
                $query .= (int) $is_admin . ",";
                $query .= "'" . $user["updated_on"] . "',";
                $query .= "'" . $user["updated_by"] . "'),";
            }
        }
        $query = rtrim ($query, ",");  // remove last comma
        return $query;
    }

    public function deleteTeamUsers ($teamID)
    {
        if ( ! $teamID || $teamID == "" ) {
            logs ("Invalid teamID" . "<br/>", true, true);
            return false;
        }
        try {
            $query = "DELETE FROM [dbo].[impowr_team_users] WHERE team_id = " . $teamID;
            $dbh   = $this->conn;
            $sth   = $dbh->query ($query);
            $dbh   = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("Error in deleteTeamUsers: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    public function getTeamUsers ($team_id)
    {
        try {
            $query = "SELECT * FROM impowr_team_users WHERE team_id=" . $team_id;

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
                $row["is_editable"] = (bool) $row["is_editable"];
                $row["is_admin"]    = (bool) $row["is_admin"];
                array_push ($data, $row);
            }
            $dbh = null;
            return $data;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in read Teams: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return array( "data" => [], "total" => 0 );
        }

    }

    public function upsertUser ($user)
    {
        if ( ! $user ) {
            logs ("Invalid user" . "<br/>", true, true);
            return false;
        }

        try {
            // Prepare the MERGE statement
            $sql = "
                MERGE INTO [dbo].[impowr_user_permissions] AS target
                USING (SELECT :user_name AS user_name) AS source
                ON target.user_name = source.user_name
                WHEN NOT MATCHED THEN
                    INSERT (user_name, super_admin)
                    VALUES (source.user_name, 'No');
            ";

            // Prepare and execute the query
            $dbh  = $this->conn;
            $stmt = $dbh->prepare ($sql);
            $stmt->execute ([
                ':user_name' => $user["user_name"],
            ]);
            $dbh = null;
            return true;

        }
        catch ( PDOException $e ) {
            logs ("Error in upsertUser: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

    public function convertStringToNumber ($value)
    {
        $value = (string) $value;

        // Define the mapping
        $mapping = [
            'true'  => 1,
            'false' => 0
        ];

        // Normalize the input to lowercase for case-insensitive comparison
        $normalizedValue = strtolower (trim ($value));

        // Return the mapped value if it exists, otherwise return null
        return $mapping[$normalizedValue] ?? null; // or handle it as needed
    }

}