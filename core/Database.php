<?php
/**
 * 数据库操作类 (PDO 单例)
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $prefix;

    private function __construct()
    {
        $cfg = config('database');
        $this->prefix = $cfg['prefix'];
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset={$cfg['charset']}";
        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('数据库连接失败: ' . $e->getMessage());
        }
    }

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function pdo()
    {
        return $this->pdo;
    }

    public function prefix()
    {
        return $this->prefix;
    }

    /** 表名(带前缀) */
    public function table($name)
    {
        return $this->prefix . $name;
    }

    /** 查询多行 */
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** 查询单行 */
    public function queryOne($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /** 查询单值 */
    public function queryScalar($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /** 写操作 (insert/update/delete) 返回影响行数 */
    public function execute($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** 插入并返回 lastInsertId */
    public function insert($table, array $data)
    {
        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO {$this->prefix}{$table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
        $this->pdo->prepare($sql)->execute($data);
        return $this->pdo->lastInsertId();
    }

    /** 更新 */
    public function update($table, array $data, $where, array $whereParams = [])
    {
        $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $sql = "UPDATE {$this->prefix}{$table} SET $set WHERE $where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($data, $whereParams));
        return $stmt->rowCount();
    }

    /** 删除 */
    public function delete($table, $where, array $whereParams = [])
    {
        $sql = "DELETE FROM {$this->prefix}{$table} WHERE $where";
        return $this->execute($sql, $whereParams);
    }

    /** 统计 */
    public function count($table, $where = '1=1', array $whereParams = [])
    {
        return (int)$this->queryScalar("SELECT COUNT(*) FROM {$this->prefix}{$table} WHERE $where", $whereParams);
    }
}
