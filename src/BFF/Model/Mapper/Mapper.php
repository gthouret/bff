<?php

namespace BFF\Model\Mapper;

use BFF\Cache\TaggedMemcache;
use BFF\Service;
use BFF\Cache\Memcache;
use BFF\Time;
use Aura\SqlQuery\Common\SelectInterface;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;

abstract class Mapper
{
    /**
     * @var \PDO
     */
    protected $db;
    /**
     * @var Memcache
     */
    protected $cache;

    protected $model;
    protected $table;
    protected $primaryKey;
    protected $columnMap;
    protected $cacheTtl = Time::ONE_HOUR;

    protected $query;
    protected $lastError;
    public $lastRequestFromCache;

    public $limit = 0;
    public $page = 0;
    public $order = [];

    public $debugQuery = false;
    public $fetchFromCache = true;

    const TRANSFORM_MYSQL_TIMESTAMP = 1;
    const TRANSFORM_IP_ADDRESS = 2;
    const TRANSFORM_IP6_ADDRESS = 3;
    const TRANSFORM_HEX = 4;

    const MAP_COLUMN = 0;
    const MAP_MODEL = 1;
    const MAP_UPDATE = 2;
    const MAP_TRANSFORM = 3;
    const MAP_TYPE = 4;

    const TYPE_STRING = 0;
    const TYPE_INT = 1;
    const TYPE_FLOAT = 2;
    const TYPE_BOOL = 3;

    public function __construct(\PDO $db=null, Memcache $cache=null)
    {
        $this->db = $db ?? Service::pdo();
        $this->cache = $cache ?? Service::cache();

        $this->lastRequestFromCache = false;
        $this->resetQueryParams();
        $this->query = new QueryFactory('mysql');
    }

    public function resetQueryParams()
    {
        $this->limit = 0;
        $this->page = 0;
        $this->order = [];
    }

    protected function getByPrimaryKeyId(int $id) : array
    {
        $rows = $this->getByColumnValue([$this->primaryKey => $id]);
        return $this->singleEntryFromResult($rows);
    }

    protected function getByColumnValue(array $where) : array
    {
        $select = $this->query->newSelect();

        $select->cols($this->columnsForSelect())->from($this->table);

        foreach ($where as $column => $value) {
            $found = false;
            foreach ($this->columnMap as $columnMapRow) {
                if ($columnMapRow[self::MAP_COLUMN] == $column) {
                    $found = true;
                    if ($columnMapRow[self::MAP_TRANSFORM] !== null) {
                        $value = $this->transformValue($columnMapRow, $value);
                    }
                }
            }

            if (!$found)
                throw new Exception("Column '$column' does not exist in the column map");

            if (is_null($value))
                $select->where("$column IS NULL");
            else
                $select->where("$column = ?", $value);
        }

        $this->limit($select);
        $this->order($select);

        $this->debugQuery($select);

        $stmt = $this->db->prepare($select->getStatement());
        if ($stmt === false) {
            throw new Exception('Error in statement: ' . implode($this->db->errorInfo(), '|'));
        }
        $stmt->execute($select->getBindValues());
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($stmt->rowCount() == 0)
            return [];

        $rows = [];
        foreach ($result as $row)
            $rows[] = $row;

        return $rows;
    }

    public function columnsForSelect(string $prefix=null) : array
    {
        $columns = [];
        foreach ($this->columnMap as $columnMapRow) {
            $columns[] = $this->transformColumn($columnMapRow, $prefix);
        }

        return $columns;
    }

    public function columnsForInsert() : array
    {
        $columns = [];
        foreach ($this->columnMap as $columnMapRow) {
            if ($columnMapRow[self::MAP_COLUMN] !== $this->primaryKey)
                $columns[] = $columnMapRow[self::MAP_COLUMN];
        }

        return $columns;
    }

    public function columnsForUpdate() : array
    {
        $columns = [];
        foreach ($this->columnMap as $columnMapRow) {
            if ($columnMapRow[self::MAP_COLUMN] !== $this->primaryKey) {
                if ($columnMapRow[self::MAP_UPDATE])
                    $columns[] = $columnMapRow[self::MAP_COLUMN];
            }
        }

        return $columns;
    }

    protected function transformColumn(array $columnMapRow, string $prefix=null) : string
    {
        $column = $columnMapRow[self::MAP_COLUMN];
        $transform = $columnMapRow[self::MAP_TRANSFORM];

        $prefixedColumn = (is_null($prefix)) ? $column : $prefix . '.' . $column;

        if (!is_null($transform)) {
            switch ($transform) {
                case self::TRANSFORM_MYSQL_TIMESTAMP:
                    $transformedColumn = 'UNIX_TIMESTAMP(' . $prefixedColumn . ') AS ' . $column;
                    break;
                case self::TRANSFORM_IP_ADDRESS:
                    $transformedColumn = 'INET_NTOA(' . $prefixedColumn . ') AS ' . $column;
                    break;
                case self::TRANSFORM_IP6_ADDRESS:
                    $transformedColumn = 'INET6_NTOA(' . $prefixedColumn . ') AS ' . $column;
                    break;
                case self::TRANSFORM_HEX:
                    $transformedColumn = 'LOWER(HEX(' . $prefixedColumn . ')) AS ' . $column;
                    break;
                default:
                    throw new Exception('Unknown column transform: ' . $transform);
            }
        } else {
            $transformedColumn = $prefixedColumn;
        }

        return $transformedColumn;
    }

    protected function transformValue(array $columnMapRow, $value) : ?string
    {
        if ($value === null)
            return null;

        $transform = $columnMapRow[self::MAP_TRANSFORM];
        $type = $columnMapRow[self::MAP_TYPE] ?? self::TYPE_STRING;

        /* Because of the way these values are bound to a query it's
         * not possible to use the SQL transform functions
         */
        if (!is_null($transform)) {
            switch ($transform) {
                case self::TRANSFORM_MYSQL_TIMESTAMP:
                    $transformedValue = empty($value) ? null : date('Y-m-d H:i:s', $value);
                    break;
                case self::TRANSFORM_IP_ADDRESS:
                case self::TRANSFORM_IP6_ADDRESS:
                    $transformedValue = inet_pton($value);
                    break;
                case self::TRANSFORM_HEX:
                    $transformedValue = hex2bin($value);
                    break;
                default:
                    throw new Exception('Unknown column transform: ' . $transform);
            }
        } else {
            $transformedValue = $value;
        }

        switch ($type) {
            case self::TYPE_BOOL:
                $transformedValue = intval($transformedValue);
                break;
        }

        return $transformedValue;
    }

    public function lastInsertId() : int
    {
        return $this->db->lastInsertId() ?? -1;
    }

    public static function transformTypeToString(int $type) : string
    {
        switch ($type) {
            case self::TRANSFORM_MYSQL_TIMESTAMP:
                return 'TRANSFORM_MYSQL_TIMESTAMP';
            case self::TRANSFORM_IP_ADDRESS:
                return 'TRANSFORM_IP_ADDRESS';
            case self::TRANSFORM_IP6_ADDRESS:
                return 'TRANSFORM_IP6_ADDRESS';
            case self::TRANSFORM_HEX:
                return 'TRANSFORM_HEX';
            default:
                throw new \Exception("Unknown transform column type $type");
        }
    }

    public function setCache(array $params, $value, array $tags = []) : bool
    {
        $key = $this->cacheKey($params);
        $cache = empty($tags) ? $this->cache : new TaggedMemcache($this->cache, $tags);
        return $cache->set($key, $value, $this->cacheTtl);
    }

    public function delCache(array $params, array $tags = []) : bool
    {
        $key = $this->cacheKey($params);
        $cache = empty($tags) ? $this->cache : new TaggedMemcache($this->cache, $tags);
        return $cache->del($key);
    }

    public function getFromCache(array $params, array $tags = [])
    {
        if (!$this->fetchFromCache) {
            $this->lastRequestFromCache = false;
            return false;
        }

        $key = $this->cacheKey($params);
        $this->lastRequestFromCache = false;

        $cache = empty($tags) ? $this->cache : new TaggedMemcache($this->cache, $tags);
        $data = $cache->get($key);

        if ($data !== false) {
            $this->lastRequestFromCache = true;
        }

        return $data;
    }

    private function cacheKey(array $params)
    {
        $hash = md5(implode("\000", $params));
        return $this->model . "_" . $hash;
    }

    protected function limit(SelectInterface &$select)
    {
        if ($this->limit > 0) {
            if ($this->page <= 0)
                $this->page = 1;

            $select->setPaging($this->limit)->page($this->page);
        }
    }

    protected function order(SelectInterface &$select)
    {
        if (!empty($this->order))
            $select->orderBy($this->order);
    }

    protected function modelCacheIdentifier($primaryKey) : array
    {
        return [$primaryKey];
    }

    protected function viewCacheIdentifier() : array
    {
        $backtrace = debug_backtrace(false, 2);
        $class = $backtrace[1]['class'];
        $function = $backtrace[1]['function'];
        $args = $backtrace[1]['args'];
        return array_merge([$class, $function], $args, [$this->page, $this->limit], $this->order);
    }

    protected function assignDataToObject(&$object, array $data)
    {
        foreach ($this->columnMap as $columnMapRow) {
            $column = $columnMapRow[self::MAP_COLUMN];
            $name = $columnMapRow[self::MAP_MODEL];
            $type = $columnMapRow[self::MAP_TYPE] ?? self::TYPE_STRING;

            switch ($type) {
                case self::TYPE_BOOL:
                    $object->$name = boolval($data[$column]);
                    break;
                case self::TYPE_INT:
                    $object->$name = intval($data[$column]);
                    break;
                case self::TYPE_FLOAT:
                    $object->$name = floatval($data[$column]);
                    break;
                default:
                    $object->$name = $data[$column];
            }

        }
    }

    protected function saveModel(&$model) : bool
    {
        $primaryKey = $this->primaryKey;
        $primaryKeyName = $this->getPrimaryKeyName();
        $primaryKeyValue = $model->$primaryKeyName;
        $update = isset($model->$primaryKeyName);

        if ($update) {
            $query = $this->query->newUpdate();
            $query->table($this->table);
            $query->cols($this->columnsForUpdate());
        } else {
            $query = $this->query->newInsert();
            $query->into($this->table);
            $query->cols($this->columnsForInsert());
        }

        foreach ($this->columnMap as $columnMapRow) {
            $column = $columnMapRow[self::MAP_COLUMN];
            $name = $columnMapRow[self::MAP_MODEL];
            $updateColumn = $columnMapRow[self::MAP_UPDATE];

            if ($update) {
                if ($column == $primaryKey) {
                    $query->where($primaryKey . ' = ?', $this->transformValue($columnMapRow, $model->$primaryKeyName));
                } else {
                    if ($updateColumn)
                        $query->bindValue($column, $this->transformValue($columnMapRow, $model->$name));
                }
            } else {
                if ($column !== $this->primaryKey)
                    $query->bindValue($column, $this->transformValue($columnMapRow, $model->$name));
            }
        }

        $this->debugQuery($query);

        $stmt = $this->db->prepare($query->getStatement());

        if ($stmt === false) {
            $this->lastError = $this->db->errorInfo();
            return false;
        }

        if (!$stmt->execute($query->getBindValues())) {
            $this->lastError = $stmt->errorInfo();
            return false;
        }

        if ($stmt->rowCount() !== 0) {
            if (!$update)
                $model->$primaryKeyName = $this->lastInsertId();
            $cacheIdentifier = $this->modelCacheIdentifier($primaryKeyValue);
            $this->setCache($cacheIdentifier, $model);
            return true;
        } else {
            return false;
        }
    }

    protected function getPrimaryKeyName() : string
    {
        foreach ($this->columnMap as $columnMapRow) {
            if ($columnMapRow[self::MAP_COLUMN] === $this->primaryKey)
                return $columnMapRow[self::MAP_MODEL];
        }

        return '';
    }

    public function delete(int $id) : bool {
        $deleted = $this->deleteByColumnValue([$this->primaryKey => $id]);
        if ($deleted) {
            $cacheIdentifier = $this->modelCacheIdentifier($id);
            $this->delCache($cacheIdentifier);
            return true;
        } else {
            return false;
        }
    }

    protected function deleteByColumnValue(array $where) : bool
    {
        $delete = $this->query->newDelete();
        $delete->from($this->table);

        foreach ($where as $column => $value) {
            $delete->where("$column = ?", $value);
        }

        $this->debugQuery($delete);

        $stmt = $this->db->prepare($delete->getStatement());
        $stmt->execute($delete->getBindValues());

        return $stmt->rowCount() !== 0;
    }

    /**
     * @param $query
     */
    protected function debugQuery(QueryInterface $query): void
    {
        if ($this->debugQuery) {
            echo $query->getStatement();
            print_r($query->getBindValues());
        }
    }

    public function singleEntryFromResult(array $result) : array
    {
        return ($result === []) ? [] : reset($result);
    }

    public function lastError() : string
    {
        return is_array($this->lastError) ? implode('|', $this->lastError) : 'Unknown';
    }

    public function lastErrorCode() : string
    {
        return $this->lastError[0] ?? 00000;
    }

    public function doNotFetchFromCache() : void
    {
        $this->fetchFromCache = false;
    }

    public function invalidateTags(array $tags) : void
    {
        $cache = new TaggedMemcache($this->cache, $tags);
        $cache->flush();
    }

    abstract protected function build(array $data);
    abstract protected function get(int $id);
}