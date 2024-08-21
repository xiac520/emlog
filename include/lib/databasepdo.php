<?php
/**
 * PDO Database Connection
 *
 * @package EMLOG
 * @link https://www.emlog.net
 */

class DatabasePDO {
    /**
     * 内部实例对象
     * @var object DatabasePDO
     */
    private static $instance = null;

    /**
     * 查询次数
     * @var int
     */
    private $queryCount = 0;

    /**
     * 内部数据连接对象
     * @var PDO
     */
    private $conn;

    /**
     * 内部数据结果
     */
    private $result;

    /**
     * 数据库类型
     * @var string
     */
    private $dbType;

    private function __construct($dbType = 'mysql') {
        if (!class_exists('PDO')) {
            emMsg('服务器空间PHP不支持PDO函数');
        }

        $this->dbType = $dbType;

        try {
            if ($this->dbType === 'mysql') {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                $options = [];
                $dbh = new PDO($dsn, DB_USER, DB_PASSWD, $options);
            } elseif ($this->dbType === 'sqlite') {
                $dsn = 'sqlite:' . DB_NAME; // the path to the SQLite file
                $dbh = new PDO($dsn);
            } else {
                throw new PDOException("不支持的数据库类型: $this->dbType");
            }

            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // 设置如果SQL语句执行错误则抛出异常，事务会自动回滚
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);           // 禁用prepared statements的仿真效果(防SQL注入)

            $this->conn = $dbh;
        } catch (PDOException $e) {
            emMsg("连接数据库失败，请检查数据库信息。错误原因：" . $e->getMessage());
        }
    }

    /**
     * 静态方法，返回数据库连接实例
     */
    public static function getInstance($dbType = 'mysql') {
        if (self::$instance === null) {
            self::$instance = new DatabasePDO($dbType);
        }

        return self::$instance;
    }

    /**
     * 关闭数据库连接
     */
    function close() {
        if (!is_null($this->conn)) {
            $this->conn = null;
        }
    }

    /**
     * 发送查询语句
     */
    function query($sql, $ignore_err = FALSE) {
        try {
            $this->result = $this->conn->query($sql);
            $this->queryCount++;
            if (!$ignore_err && 1046 == $this->geterrno()) {
                emMsg("连接数据库失败，请填写数据库名");
            }
            if (!$ignore_err && !$this->result) {
                emMsg("SQL语句执行错误: {$sql}<br />" . $this->geterror());
            } else {
                return $this->result;
            }
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * 从结果集中取得一行作为关联数组/数字索引数组
     */
    function fetch_array($query, $type = PDO::FETCH_ASSOC) {
        return $query->fetch($type);
    }

    /**
     * 从结果集中取得一行作为关联数组/数字索引数组
     */
    public function fetch_all($sql, $fetchMode = MYSQLI_ASSOC) {
        $this->result = $this->query($sql);
        $data = [];
        while ($row = $this->fetch_array($this->result, $fetchMode)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * 从结果级中取一行
     * @param $sql
     * @return mixed
     */
    function once_fetch_array($sql) {
        try {
            $result = $this->conn->query($sql);
            $resultDb = $result->fetchAll(PDO::FETCH_ASSOC);
            return isset($resultDb[0]) ? $resultDb[0] : [];
        } catch (\PDOException $e) {
            emMsg($e->getMessage());
        }
    }

    /**
     * 从结果集中取得一行作为数字索引数组
     */
    function fetch_row($query) {
        return $query->rowCount();
    }

    /**
     * 取得行的数目
     */
    function num_rows($query) {
        $rows = $query->fetch(PDO::FETCH_NUM);
        return isset($rows[0]) ? $rows[0] : 0;
    }

    /**
     * 取得结果集中字段的数目
     */
    function num_fields($query) {
        return $query->fetchColumn();
    }

    /**
     * 取得上一步 INSERT 操作产生的 ID
     */
    function insert_id() {
        return $this->conn->lastInsertId();
    }

    /**
     * 获取错误信息
     */
    function geterror() {
        return $this->conn->errorInfo();
    }

    /**
     * 获取错误编码
     */
    function geterrno() {
        return $this->conn->errorCode();
    }

    /**
     * Get number of affected rows in previous MySQL operation
     */
    function affected_rows() {
        if ($this->result) {
            return $this->result->rowCount();
        }
        return 0;
    }

    /**
     * 取得数据库版本信息
     */
    function getVersion() {
        return $this->conn->query('select version()')->fetchColumn();
    }

    /**
     * 取得数据库查询次数
     */
    function getQueryCount() {
        return $this->queryCount;
    }

    /**
     * Escapes special characters
     */
    function escape_string($sql) {
        return trim($sql);
    }
}