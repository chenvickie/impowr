<?php

class Database
{
  // specify your own database credentials
  private $dbServer;
  private $connectionOptions;
  public $conn;

  // get the database connection
  public function getConnection ($connectionOptions)
  {
    //connect to ccc info
    $this->connectionOptions = $connectionOptions;

    try {
      $conn = new PDO("sqlsrv:server= " . $connectionOptions['dbServer'] . " ; Database = " . $connectionOptions['dbName'], $connectionOptions['dbUser'], $connectionOptions['dbPw']);
      $conn->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $conn->setAttribute (PDO::SQLSRV_ATTR_QUERY_TIMEOUT, 1);
      if ( $conn == false ) {
        logs ("[Failed] open data base connection:" . json_encode (sqlsrv_errors ()));
        die ();
      } else {
        return $conn;
      }
    }
    catch ( Exception $e ) {
      logs ("Database Catch Connection Error!" . json_encode ($e));
    }
  }
}
?>