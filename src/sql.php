<?php declare (strict_types = 1);

namespace Src;

use Generator;
use RuntimeException;
use Src\Core\CacheManager;
use Src\QueryBuilder\CaseBuilder;
use Src\QueryBuilder\ClauseBuilder;
use Src\QueryBuilder\CRUDdelete;
use Src\QueryBuilder\CRUDInsert;
use Src\QueryBuilder\CRUDUpdate;
use Src\QueryBuilder\JoinBuilder;
use Src\QueryBuilder\SubBuilder;
use Src\QueryBuilder\UnionBuilder;
use Throwable;

/**
 * Summary of Sql
 */
class Sql
{
    use \Src\Traits\Aggregate;
    use \Src\Traits\DbTrait;
    /**
     * Summary of table
     * @var string
     */
    protected string $table;

    /**
     * Summary of cols
     * @var array
     */
    protected array $cols = [];

    /**
     * Summary of from
     * @var string
     */
    protected string $from = "";

    /**
     * Summary of params
     * @var array
     */
    protected array $params = [];

    /**
     * @var array
     */
    protected array $conditions = [];

    /**
     * Summary of having
     * @var array
     */
    protected array $having = [];

    /**
     * Summary of groups
     * @var array
     */
    protected array $groups = [];

    /**
     * Summary of order
     * @var string
     */
    protected string $order = "";

    protected ?int $limit = null;

    /**
     * Summary of union
     * @var string
     */
    protected string $union = "";

    /**
     * Summary of join
     * @var string
     */
    protected string $join = "";

    protected ?int $offset = null;

    /**
     * Summary of cte
     * @var array
     */
    protected array $cte = [];

    /**
     * Summary of explain
     * @var string
     */
    protected string $explain = "";

    /**
     * Summary of distinct
     * @var bool
     */
    protected bool $distinct = false;

    protected ?string $sql                = null;
    protected array $request              = [];
    protected ?ClauseBuilder $buildClause = null;
    protected ?JoinBuilder $buildJoin     = null;

    protected ?UnionBuilder $buildUnion = null;

    protected ?CRUDInsert $crudinsert = null;

    protected ?CRUDUpdate $crudupdate = null;

    protected ?CRUDdelete $cruddelete = null;

    public function __construct()
    {
        $this->table = "";

    }

    /**
     * @param string|callable $table
     * @return Sql
     */
    public function table(string | Sql | callable $table, ?string $alias = ''): Sql
    {
        $clone = clone $this;

        if (\is_string($table)) {
            if (! empty($alias)) {
                $clone->table = "$table AS $alias";
            } else {
                $clone->table = $table;
            }
        } else if (is_callable($table) || $table instanceof Sql) {
            $sub          = $table;
            $sql          = $sub instanceof Sql ? $sub->getSql() : $sub($clone);
            $clone->table = " ($sql) AS " . ($alias ?: 't');
            if ($sub instanceof Sql) {
                $clone->params = $clone->mergeParams($sub->params);
            }
        }

        return $clone;
    }

    /**
     * Summary of with
     * @param string $cte
     * @param mixed $recursive
     * @param Sql|callable $callback
     * @return Sql
     */
    public function with(string $cte, Sql | callable $callback, ?bool $recursive = false): Sql
    {
        $clone = clone $this;
        $ctes  = [];
        $ctes  = $clone->initClause()
            ->toWith($cte, $callback, $recursive);

        foreach ($ctes as $c) {
            $clone->params = $clone->mergeParams($c['params'] ?? []);
            $clone->cte[]  = [
                'name'      => $c['name'],
                'sql'       => $c['sql'],
                'recursive' => $c['recursive'],
            ];

        }

        return $clone;
    }

    /**
     * Summary of select
     * @param array $col
     * @return Sql
     */
    public function select(...$col): Sql
    {
        $clone = clone $this;

        [$cols, $params] = $clone->initClause()
            ->toselect($col);

        $clone->cols = $clone->mergeColumn($cols);

        $clone->params = $clone->mergeParams($params);

        return $clone;
    }

    public function distinct(bool $distinct = false): Sql
    {
        $clone           = clone $this;
        $clone->distinct = $distinct;
        return $clone;
    }

    /**
     * Summary of selectCase
     * @param callable $callback
     * @param mixed $colonne
     * @return Sql
     */
    public function selectCase(callable|CaseBuilder $callback, ?string $colonne = ''): Sql
    {
        $clone           = clone $this;
        [$cols, $params] = $clone->initClause()
            ->toCase($callback, $colonne);

        $clone->cols   = $clone->mergeColumn($cols);
        $clone->params = $clone->mergeParams($params);
        return $clone;
    }

    /**
     * Summary of from
     * @param string|Sql|callable $table
     * @param mixed $alias
     * @return Sql
     */
    public function from(string | Sql | callable $table, ?string $alias = ''): Sql
    {
        $clone       = clone $this;
        $clone->from = $clone->initClause()->toform($table, $alias);
        return $clone;
    }

    /**
     * Summary of isNull
     * @param string $value
     * @return Sql
     */
    public function isNull(string $value): Sql
    {
        $clone             = clone $this;
        $conditions        = $clone->initClause()->toNull($value);
        $clone->conditions = $clone->mergeCond([$conditions]);
        return $clone;
    }

    /**
     * Summary of isNotnull
     * @param string $value
     * @return Sql
     */
    public function isNotnull(string $value): Sql
    {
        $clone      = clone $this;
        $conditions = $clone->initClause()
            ->toNotnull($value);
        $clone->conditions = $clone->mergeCond([$conditions]);
        return $clone;
    }

    /**
     * Summary of where
     * @param array $cols
     * @return Sql
     */
    public function where(array $cols): Sql
    {
        $clone                = clone $this;
        [$condition, $params] = $clone->initClause()
            ->towhere($cols);
        $clone->conditions = $clone->mergeCond($condition);
        $clone->params     = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of whereOr
     * @param array $cols
     * @return Sql
     */
    public function whereOr(array $cols): Sql
    {
        $clone                = clone $this;
        [$condition, $params] = $clone->initClause()
            ->towhereOr($cols);
        $clone->conditions = $clone->mergeCond($condition);
        $clone->params     = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of whereLike
     * @param array $cols
     * @return Sql
     */
    public function whereLike(array $cols): Sql
    {
        $clone                = clone $this;
        [$condition, $params] = $clone->initClause()
            ->towhereLike($cols);
        $clone->conditions = $clone->mergeCond($condition);
        $clone->params     = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of whereIn
     * @param string $cols
     * @param callable|Sql|array $values
     * @param bool $not
     * @return Sql
     */
    public function whereIn(string $cols, callable | Sql | array $values, bool $not = false): Sql
    {
        $clone = clone $this;

        [$conditions, $params] = $clone->initClause()
            ->towhereIn($cols, $values, $not);
        $clone->params     = $clone->mergeParams($params);
        $clone->conditions = $clone->mergeCond($conditions);
        return $clone;
    }

    /**
     * Summary of whereBetween
     * @param array $cols
     * @return Sql
     */
    public function whereBetween(array $cols): Sql
    {
        $clone                 = clone $this;
        [$conditions, $params] = $clone->initClause()
            ->towhereBetween($cols, false);
        $clone->conditions = $clone->mergeCond($conditions);
        $clone->params     = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of whereNotBetween
     * @param array $cols
     * @return Sql
     */
    public function whereNotBetween(array $cols): Sql
    {
        $clone                 = clone $this;
        [$conditions, $params] = $clone->initClause()
            ->towhereBetween($cols, true);
        $clone->conditions = $clone->mergeCond($conditions);
        $clone->params     = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of whereColumn
     * @param string $cols
     * @param string|int $value
     * @param string $signComparaison
     * @return Sql
     */
    public function whereColumn(string $cols, string | int $value, string $signComparaison = '='): Sql
    {
        $clone = clone $this;
        $cond  = [];
        $cond  = $clone->initClause()
            ->towhereColumn($cols, $signComparaison, $value);
        $clone->conditions = $clone->mergeCond($cond);

        return $clone;
    }

    /**
     * Summary of whereExists
     * @param callable $callback
     * @return Sql
     */
    public function whereExists(callable $callback): Sql
    {
        $clone                = clone $this;
        [$condition, $params] = $clone->initClause()
            ->toExists($callback, false);
        $clone->conditions = $clone->mergeCond($condition);
        $clone->params     = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of whereNotexists
     * @param callable $callback
     * @return Sql
     */
    public function whereNotexists(callable $callback): Sql
    {
        $clone                = clone $this;
        [$condition, $params] = $clone->initClause()
            ->toExists($callback, true);
        $clone->conditions = $clone->mergeCond($condition);
        $clone->params     = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of having
     * @param array $cols
     * @return Sql
     */
    public function having(array $cols): Sql
    {
        $clone                           = clone $this;
        [$clone->having, $clone->params] = $clone->initClause()
            ->toHaving($cols);
        return $clone;
    }

    /**
     * Summary of groupBy
     * @param array $groups
     * @return Sql
     */
    public function groupBy(array $groups): Sql
    {
        $clone         = clone $this;
        $clone->groups = $clone->initClause()
            ->toGroupsBy($groups);
        return $clone;
    }

    /**
     * Summary of orderBy
     * @param string $key
     * @param mixed $order
     * @return Sql
     */
    public function orderBy(string $key, ?string $order = 'ASC'): Sql
    {
        $clone        = clone $this;
        $clone->order = $clone->initClause()
            ->toOrderBy($key, $order);
        return $clone;
    }

    /**
     * Summary of limit
     * @param mixed $limit
     * @return Sql
     */
    public function limit(?int $limit = null): Sql
    {
        $clone        = clone $this;
        $clone->limit = $clone->initClause()
            ->toLimit($limit);
        return $clone;
    }

    /**
     * Summary of offset
     * @param mixed $offset
     * @return Sql
     */
    public function offset(?int $offset): Sql
    {
        $clone         = clone $this;
        $clone->offset = $clone->initClause()
            ->toOffset($offset);
        return $clone;
    }

    /**
     * Summary of join
     * @param callable|JoinBuilder $join
     * @return Sql
     */
    private function join(string $type, callable | JoinBuilder $join): Sql
    {
        $clone          = clone $this;
        [$sql, $params] = $clone->initClause()
            ->toJoin($type, $join);
        foreach ($sql as $j) {
            $clone->join .= $j;
        }

        $clone->params = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of innerJoin
     * @param callable|JoinBuilder $join
     * @return Sql
     */
    public function innerJoin(callable | JoinBuilder $join): Sql
    {
        return $this->join('INNER', $join);
    }

    /**
     * Summary of leftJoin
     * @param callable|JoinBuilder $join
     * @return Sql
     */
    public function leftJoin(callable | JoinBuilder $join): Sql
    {
        return $this->join('LEFT', $join);
    }

    /**
     * Summary of rightJoin
     * @param callable|JoinBuilder $join
     * @return Sql
     */
    public function rightJoin(callable | JoinBuilder $join): Sql
    {
        return $this->join('RIGHT', $join);
    }

    /**
     * Summary of crossJoin
     * @param callable|JoinBuilder $join
     * @return Sql
     */
    public function crossJoin(callable | JoinBuilder $join): Sql
    {
        return $this->join('CROSS', $join);
    }

    /**
     * Summary of joins
     * @param string $table
     * @param string $alias
     * @return JoinBuilder
     */
    public static function joins(string $table, string $alias): JoinBuilder
    {
        return (new JoinBuilder())->table($table)
            ->as($alias);
    }

    /**
     * Summary of transaction
     * @param callable $callback
     * @return static
     */
    public function transaction(callable | Sql $callback): static
    {
        $this->db->totransaction($callback);
        return $this;
    }

    /**
     * Summary of unions
     * @param bool $all
     * @param UnionBuilder|callable $query
     * @return Sql
     */
    private function Unions(UnionBuilder | callable $query, ?bool $all = false): Sql
    {
        $clone          = clone $this;
        [$sql, $params] = $clone->initClause()
            ->toUnion($all, $query, $clone->cols, $clone->distinct);

        foreach ($sql as $u) {
            $clone->union .= $u;
        }

        $clone->params = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of union
     * @param UnionBuilder|callable $query
     * @return Sql
     */
    public function union(UnionBuilder | callable $query):Sql{
         $clone = clone $this;
        return $clone->Unions($query, false);
    }

    /**
     * Summary of unionAll
     * @param UnionBuilder|callable $query
     * @return Sql
     */
    public function unionAll(UnionBuilder | callable $query): Sql
    {
        $clone = clone $this;
        return $clone->Unions($query, true);

    }

    public static function u():UnionBuilder{
        return (new UnionBuilder());
    }

    /**
     * Summary of explain
     * @param callable|Sql|string $explain
     * @return Sql
     */
    public function explain(callable | Sql | string $explain, ?string $mode = ''): Sql
    {
        $instance       = clone $this;
        [$sql, $params] = $instance->initClause()
            ->toexplain($explain, $mode);

        $instance->params  = $params;
        $instance->explain = $sql;

        return $instance;
    }

      /**
     * Summary of subrow
     * @param callable|Sql|string $subquery
     * @param string $alias
     * @return Sql
     */

    public function sub(callable | Sql | string $subquery, string $alias): Sql
    {
        $clone          = clone $this;
        [$sql, $params] = (new SubBuilder())
            ->tosub($subquery, $alias)->compileSub();
        $clone->params = $clone->mergeParams($params);

        return $clone;
    }

    /**
     * Summary of compiler
     * @return string
     */
    private function compiler(): string
    {
        $clone = clone $this;

        $table    = ! empty($clone->from) ? $clone->from : $clone->table;
        $distinct = $clone->distinct ? ' DISTINCT ' : '';

        $clone->sql = "SELECT {$distinct}" . (implode(',', $clone->cols) ?: '*') . " FROM {$table} ";
        if (! empty($clone->join)) {
            $clone->sql .= $clone->join;
        }

        if (! empty($clone->conditions)) {
            $clone->sql .= ' WHERE ' . implode(' AND ', $clone->conditions) . ' ';
        }

        if (! empty($clone->groups)) {
            $clone->sql .= ' GROUP BY ' . implode(',', $clone->groups);
        }

        if (! empty($clone->groups) && ! empty($clone->having)) {
            $clone->sql .= ' HAVING ' . implode(' AND ', $clone->having);
        }

        if (! empty($clone->union)) {
            $clone->sql .= $clone->union;
        }

        if ($clone->order !== '') {
            $clone->sql .= " ORDER BY {$clone->order} ";
        }

        if ($clone->limit !== null) {
            $clone->sql .= " LIMIT {$clone->limit} ";
        }

        if ($clone->offset !== null) {
            $clone->sql .= " OFFSET {$clone->offset} ";
        }

        //Recurcive cte
        if (! empty($clone->cte)) {
            $requestWith = [];
            $recursive   = '';
            foreach ($clone->cte as $info) {
                $requestWith[] = "{$info['name']} AS ({$info['sql']})";
                if ($info['recursive']) {
                    $recursive = 'RECURSIVE';
                }

            }

            $clone->sql = "WITH {$recursive} " .
            implode(', ', $requestWith) .
            ' ' .
            $clone->sql;
        }

        //explain
        if (! empty($clone->explain)) {
            $clone->sql = $clone->explain;
        }

        return $clone->sql;

    }

    /**
     * Summary of mergeParams
     * @param array $params
     * @return array
     */
    private function mergeParams(array $params): array
    {
        $this->params = array_merge($this->params, $params);
        return $this->params;
    }

    /**
     * Summary of addParams
     * @param array $params
     * @return static
     */
    public function addParams(array $params): static
    {
        $this->params = $this->mergeParams($params);
        return $this;
    }

    /**
     * Summary of mergeCond
     * @param array $condition
     * @return array
     */
    private function mergeCond(array $condition): array
    {
        $this->conditions = \array_merge(
            $this->conditions,
            $condition ?? []
        );
        return $this->conditions;
    }

    /**
     * Summary of mergeColumn
     * @param array $cols
     * @return array
     */
    private function mergeColumn(array $cols): array
    {
        $this->cols = \array_merge(
            $this->cols,
            $cols ?? []
        );
        return $this->cols;
    }

    public function getSql(): string
    {
        return (! empty($this->request) &&
            isset($this->request['sql'])) ?
        $this->request['sql'] :
        $this->compiler();
    }

    /**
     * Summary of getParams
     * @return array
     */
    public function getParams(): array
    {
        return (! empty($this->request) &&
            ! empty($this->request['params'])) ?
        $this->request['params'] :
        $this->params;
    }

    public function getDriver(): string
    {
        return (! empty($this->request) &&
            ! empty($this->request['driver'])) ?
        $this->request['driver'] :
        $this->driver;
    }

    /**
     * Summary of getTable
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Summary of getColumn
     * @return array
     */
    public function getColumn(): array
    {
        return $this->cols;
    }

    public function cteDebug(): array
    {
        return $this->cte;
    }

    /**
     * Summary of get
     * @param int $fetchMode
     * @return array
     */
    public function get(int $fetchMode = \PDO::FETCH_OBJ): array
    {
        return $this->db()->All(
            $this->getSql(),
            $this->getParams(),
            $fetchMode
        );
    }

    /**
     * Summary of getStream
     * @param int $fetchMode
     * @return Generator
     */
    public function getStream(int $fetchMode = \PDO::FETCH_OBJ): Generator
    {
        return $this->db()->stream(
            $this->getSql(),
            $this->getParams(),
            $fetchMode
        );
    }

    /**
     * Summary of getOne
     * @param int $fetchMode
     */
    public function getOne(int $fetchMode = \PDO::FETCH_OBJ)
    {
        return $this->db()->One(
            $this->getSql(),
            $this->getParams(), $fetchMode
        );
    }

    /**
     * Summary of debugRequete
     * @return array{Sql: string, params: array}
     */
    public function sqlDebug():array
    {
        return [
            'driver' => $this->getDriver(),
            'Sql'    => $this->getSql(),
            'params' => $this->getParams(),
        ];
    }

    /**
     * Summary of Columns
     * @param array $cols
     * @return Sql
     */
    public function Columns(array $cols = [])
    {
        $clone = clone $this;
        $clone->initInsert()->colonnes($cols);

        return $clone;
    }

    /**
     * Summary of insert
     * @return bool|\PDOStatement
     */
    public function insert()
    {
        [$sql, $params] = $this->initInsert()
            ->insertData($this->table);
        return $this->db()->fais($sql, $params);
    }

    /**
     * Summary of BulkInsert
     * @param string $fileDirectory
     * @return array<bool|\PDOStatement>
     */
    public function bulkInsert(string $fileDirectory = '')
    {
       $conn = $this->db()->connect();
        try{
        [$sql, $params] = $this->initInsert()
            ->bulkInsert($fileDirectory, $this->table);
      
        $conn->beginTransaction();
        $response = [];

            foreach ($params as $param) {
                $response[] = $this->db()->fais($sql, $param);
            }

            $conn->commit();

            return $response;

        } catch (Throwable $e) {
            $conn->rollBack();
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Summary of Set
     * @param array $cols
     * @return Sql
     */
    public function set_cols(array $cols = []):Sql
    {
        $clone = clone $this;
        $clone->initUpdate()->set($cols);
        return $clone;
    }

    /**
     * Summary of update
     */
    public function update()
    {
        [$set, $sql, $params] = $this->initUpdate()
            ->updateData($this->table, $this->conditions);
        $this->params = array_merge($this->params, $params);
        $this->db->fais($sql, $this->params);

        return $set;
    }

    /**
     * Summary of Delete
     * @return Sql
     */
    public function delete()
    {
        $clone = clone $this;
        $sql   = $clone->initDelete()->DeleteData($clone->table, $clone->conditions);
        $clone->db->fais($sql, $clone->params);
        return $clone;
    }

    /**
     * Summary of deepCopy
     * @param mixed $data
     * @return mixed
     */
    private function deepCopy(mixed $data)
    {
        return unserialize(serialize($data));
    }

    /**
     * Summary of add_query
     * @param string $key
     * @return static
     */
    public function add_query(string $key): static
    {
        $clone = clone $this;

        $data = $this->deepCopy(
            ["driver" => $this->getDriver(),
                "sql"     => $clone->compiler(),
                "params"  => $this->params
                ]);

       $this->cache($key)
            ->data($data)
            ->signature($data)
            ->put();
        return $this;
    }

    /**
     * Summary of use_query
     * @param string $key
     * @return Sql
     */
    public function use_query(string $key):Sql
    {
        $clone = clone $this;

        $clone->request = $this->cache($key)
            ->get()["data"];

        return $clone;
    }

    /**
     * Summary of removeCache
     * @param string $key
     * @return string|null
     */
    public static function removeCache(string $key):?string{
            $instance = new static();
           $delete = $instance->cache($key)
                    ->delete()
                    ->debug();

        return $delete;
    }

   /**
    * Summary of removeAllCache
    * @return string|null
    */
   public static function removeAllCache():string{
        $instat = new static();
        $clearAll = CacheManager::clearAll($instat->cacheDir)
                    ->debug();
    
        return $clearAll;
   }

    /**
     * Summary of initClause
     * @return ClauseBuilder|null
     */
    private function initClause(): ClauseBuilder
    {
        if ($this->buildClause === null) {
            $this->buildClause = new ClauseBuilder();
        }
        return clone $this->buildClause;
    }

    /**
     * Summary of initInsert
     * @return CRUDInsert|null
     */
    private function initInsert():CRUDInsert|null
    {
        if ($this->crudinsert === null) {
            $this->crudinsert = new CRUDInsert();
        }
        return $this->crudinsert;
    }

    /**
     * Summary of initUpdate
     * @return CRUDUpdate|null
     */
    private function initUpdate():CRUDUpdate|null
    {
        if ($this->crudupdate === null) {
            $this->crudupdate = new CRUDUpdate();
        }
        return $this->crudupdate;
    }

    /**
     * Summary of initDelete
     * @return CRUDdelete|null
     */
    private function initDelete():CRUDdelete|null
    {
        if ($this->cruddelete === null) {
            $this->cruddelete = new CRUDdelete();
        }
        return $this->cruddelete;
    }

    /**
     * Summary of __clone
     * @return void
     */
    public function __clone()
    {
        if ($this->buildClause !== null) {
            $this->buildClause = clone $this->buildClause;
        }

        if ($this->buildJoin !== null) {
            $this->buildJoin = clone $this->buildJoin;
        }

        if ($this->buildaggregate !== null) {
            $this->buildaggregate = clone $this->buildaggregate;
        }

        if ($this->buildUnion !== null) {
            $this->buildUnion = clone $this->buildUnion;
        }

        if ($this->crudinsert !== null) {
            $this->crudinsert = clone $this->crudinsert;
        }

        if ($this->crudupdate !== null) {
            $this->crudupdate = clone $this->crudupdate;
        }

        if ($this->cruddelete !== null) {
            $this->cruddelete = clone $this->cruddelete;
        }

    }
}
