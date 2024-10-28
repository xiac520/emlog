<?php
class DbPgsql {
    private $conn;

    public function __construct() {
        $this->conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASSWD . " sslmode=require");
        if (!$this->conn) {
            die('Could not connect: ' . pg_last_error());
        }
    }

    public function query($sql) {
        $result = pg_query($this->conn, $sql);
        if (!$result) {
            die('Query failed: ' . pg_last_error());
        }
        return $result;
    }

    public function fetchArray($result) {
        return pg_fetch_array($result, null, PGSQL_ASSOC);
    }

    public function numRows($result) {
        return pg_num_rows($result);
    }

    public function close() {
        pg_close($this->conn);
    }

    // 其他数据库操作方法
}