<?php

class Users
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

    public function read ($key = '', $value = '', $partial = 'No', $sort = 'user_name', $dir = 'asc', $offset = 0, $limit = 10)
    {
        $noQuote = [ "is_editable", "is_admin" ];

        if ( $key == "is_editable" || $key == "is_admin" ) {
            $value = strtoupper ($value) == 'TRUE' ? 1 : 0;
        }

        try {
            $usersQuery = "SELECT * ";
            $totalQuery = "SELECT COUNT(*) as count ";

            $query = "FROM impowr_user_permissions";

            if ( $key !== '' && $value !== '' ) {
                $query .= " WHERE ";
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
            $usersQuery .= $query . " ORDER BY " . $sort . " " . $dir .
                " OFFSET " . $offset . " ROWS
                                    FETCH FIRST " . $limit . " ROWS ONLY";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($usersQuery);
            $sth->execute ();

            $data = [];
            while ( $row = $sth->fetch (PDO::FETCH_ASSOC) ) {
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
            logs ("[ERROR] Error in read Users: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return array( "data" => [], "total" => 0 );
        }
    }


    public function updateUser ($params)
    {
        try {
            $query = "UPDATE [dbo].[impowr_user_permissions] SET  
                    user_name = ?
                    WHERE id = ?";

            $dbh = $this->conn;
            $sth = $dbh->prepare ($query);
            $sth->execute ($params);
            $dbh = null;
            return true;
        }
        catch ( PDOException $e ) {
            logs ("[ERROR] Error in updateUser: " . json_encode ($e->getMessage ()) . "<br/>", true, true);
            return false;
        }
    }

}