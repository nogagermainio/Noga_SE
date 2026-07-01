<?php
namespace Src\QueryBuilder\Select;

use Src\Core\CacheManager;
use Src\QueryBuilder\Crud\Delete\Delete;
use Src\QueryBuilder\Crud\Insert\Insert;
use Src\QueryBuilder\Crud\Update\Update;
use Src\QueryBuilder\Select\Case\Cases;
use Src\QueryBuilder\Select\Join\Join;
use Src\QueryBuilder\Select\Sub\Sub;
use Src\QueryBuilder\Select\Union\Union;
use Src\Traits\Aggregate;
use Src\Traits\Condition;
use Src\Traits\DbTrait;
use Src\Traits\SelectAttr;

class Select{
    use SelectAttr;
    use Aggregate;
    use DbTrait;
    use Condition;

    public function __construct()
    {
        $this->table = "";
    }

    /**
     * @param string|callable $table
     * @return Select
     */
    public function table(string | Select | callable $table, ?string $alias = ''): Select
    {
        $clone = clone $this;

        if (\is_string($table)) {
            if (! empty($alias)) {
                $clone->table = "$table AS $alias";
            } else {
                $clone->table = $table;
            }
        } else if (is_callable($table) || $table instanceof Select) {
            $sub          = $table;
            $sql          = $sub instanceof Select ? $sub->getQuery() : $sub($clone);
            $clone->table = " ($sql) AS " . ($alias ?: 't');
            if ($sub instanceof Select) {
                $clone->params = $clone->mergeParams($sub->params);
            }
        }

        return $clone;
    }

    /**
     * Summary of with
     * @param string $cte
     * @param mixed $recursive
     * @param Select|callable $callback
     * @return Select
     */
    public function with(string $cte, Select | callable $callback, ?bool $recursive = false): Select
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
     * @return Select
     */
    public function select(...$col): Select
    {
        $clone = clone $this;

        [$cols, $params] = $clone->initClause()
            ->toselect($col);

        $clone->cols = $clone->mergeColumn($cols);

        $clone->params = $clone->mergeParams($params);

        return $clone;
    }

    public function distinct(bool $distinct = false): Select
    {
        $clone           = clone $this;
        $clone->distinct = $distinct;
        return $clone;
    }

    /**
     * Summary of selectCase
     * @param Cases $case
     * @param string $colonne
     * @return Select
     */
    public function selectCase(Cases $case, string $colonne = ''): Select
    {
        $clone           = clone $this;
        [$cols, $params] = $clone->initClause()
            ->toCase($case, $colonne);

        $clone->cols   = $clone->mergeColumn($cols);
        $clone->params = $clone->mergeParams($params);
        return $clone;
    }

    /**
     * Summary of case
     * @return Cases
     */
    public static function cases():Cases{
        return (new Cases());
    }

    /**
     * Summary of from
     * @param string|Select|callable $table
     * @param string $alias
     * @return Select
     */
    public function from(string | Select | callable $table, string $alias = ''): Select
    {
        $clone       = clone $this;
        $clone->from = $clone->initClause()->toform($table, $alias);
        return $clone;
    }

    /**
     * Summary of groupBy
     * @param array $groups
     * @return Select
     */
    public function groupBy(array $groups): Select
    {
        $clone         = clone $this;
        $clone->groups = $clone->initClause()
            ->toGroupsBy($groups);
        return $clone;
    }

    /**
     * Summary of orderBy
     * @param string $key
     * @param string $order
     * @return Select
     */
    public function orderBy(string $key, string $order = 'ASC'): Select
    {
        $clone        = clone $this;
        $clone->order = $clone->initClause()
            ->toOrderBy($key, $order);
        return $clone;
    }

    /**
     * Summary of limit
     * @param int|null $limit
     * @return Select
     */
    public function limit(?int $limit = null): Select
    {
        $clone        = clone $this;
        $clone->limit = $clone->initClause()
            ->toLimit($limit);
        return $clone;
    }

    /**
     * Summary of offset
     * @param int|null $offset
     * @return Select
     */
    public function offset(?int $offset): Select
    {
        $clone         = clone $this;
        $clone->offset = $clone->initClause()
            ->toOffset($offset);
        return $clone;
    }

    /**
     * Summary of join
     * @param callable|Join $join
     * @return Select
     */
    private function join(string $type, callable | Join $join): Select
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
     * @param callable|Join $join
     * @return Select
     */
    public function innerJoin(callable | Join $join): Select
    {
        return $this->join('INNER', $join);
    }

    /**
     * Summary of leftJoin
     * @param callable|Join $join
     * @return Select
     */
    public function leftJoin(callable | Join $join): Select
    {
        return $this->join('LEFT', $join);
    }

    /**
     * Summary of rightJoin
     * @param callable|Join $join
     * @return Select
     */
    public function rightJoin(callable | Join $join): Select
    {
        return $this->join('RIGHT', $join);
    }

    /**
     * Summary of crossJoin
     * @param callable|Join $join
     * @return Select
     */
    public function crossJoin(callable | Join $join): Select
    {
        return $this->join('CROSS', $join);
    }

    /**
     * Summary of joins
     * @param string $table
     * @param string $alias
     * @return Join
     */
    public static function j(string $table, string $alias): Join
    {
        return (new Join())->table($table)
            ->as($alias);
    }

    /**
     * Summary of transaction
     * @param callable $callback
     * @return static
     */
    public function transaction(callable | Select $callback): static
    {
        $this->db->totransaction($callback);
        return $this;
    }

    /**
     * Summary of unions
     * @param bool $all
     * @param Union|callable $query
     * @return Select
     */
    private function Unions(Union | callable $query, bool $all = false): Select
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
     * @param Union|callable $query
     * @return Select
     */
    public function union(Union | callable $query):Select{
         $clone = clone $this;
        return $clone->Unions($query, false);
    }

    /**
     * Summary of unionAll
     * @param Union|callable $query
     * @return Select
     */
    public function unionAll(Union | callable $query): Select
    {
        $clone = clone $this;
        return $clone->Unions($query, true);

    }

    public static function u():Union{
        return (new Union());
    }

    /**
     * Summary of explain
     * @param callable|Select|string $explain
     * @param string $mode
     * @return Select
     */
    public function explain(callable | Select | string $explain, string $mode = ''): Select
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
     * @param callable|Select|string $subquery
     * @param string $alias
     * @return Select
     */

    public function sub(callable | Select | string $subquery, string $alias): Select
    {
        $clone          = clone $this;
        [$sql, $params] = (new Sub())
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

        if (! empty($clone->join))  $clone->sql .= $clone->join;
    
        if (! empty($clone->conditions)) $clone->sql .= ' WHERE ' . implode(' AND ', $clone->conditions) . ' ';

        if (! empty($clone->groups)) $clone->sql .= ' GROUP BY ' . implode(',', $clone->groups);

        if (! empty($clone->groups) && ! empty($clone->having)) $clone->sql .= ' HAVING ' . implode(' AND ', $clone->having);

        if (! empty($clone->union)) $clone->sql .= $clone->union;

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

    public function getQuery(): string
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

    public function viewCte(): array
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
            $this->getQuery(),
            $this->getParams(),
            $fetchMode
        );
    }

    /**
     * Summary of getStream
     * @param int $fetchMode
     * @return \Generator
     */
    public function getStream(int $fetchMode = \PDO::FETCH_OBJ): \Generator
    {
        return $this->db()->stream(
            $this->getQuery(),
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
            $this->getQuery(),
            $this->getParams(), $fetchMode
        );
    }

    /**
     * Summary of debugRequete
     * @return array{Sql: string, params: array}
     */
    public function viewState():array
    {
        return [
            'driver' => $this->getDriver(),
            'Sql'    => $this->getQuery(),
            'params' => $this->getParams(),
            'columns'=>$this->getColumn()
        ];
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
     * @return Select
     */
    public function add_query(string $key): Select
    {
        $clone = clone $this;

        $data = $this->deepCopy(
            ["driver" => $this->getDriver(),
                "sql"     => $clone->getQuery(),
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
     * @return Select
     */
    public function use_query(string $key):Select
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
        $instance = new static();
        $clearAll = CacheManager::clearAll($instance->cacheDir)
                    ->debug();
    
        return $clearAll;
   }

   /**
     * Summary of insert
     * @param string $table
     * @return Insert
     */
    public static function insert(string $table):Insert{
        return (new Insert())->table($table);
    }

    /**
     * Summary of update
     * @param string $table
     * @return Update
     */
    public static function update(string $table):Update{
        return (new Update($table));
    }

    /**
     * Summary of delete
     * @param string $table
     * @return Delete
     */
    public static function delete(string $table):Delete{
        return (new Delete($table));
    }

   
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

      
    }
}
