<?php declare (strict_types = 1);

namespace Src\QueryBuilder;

use InvalidArgumentException;
use RuntimeException;
use Src\Core\BindHashing;
use Src\QueryBuilder\Select\Case\Cases;
use Src\QueryBuilder\Select\Join\Join;
use Src\QueryBuilder\Select\Select;
use Src\QueryBuilder\Select\Union\Union;
use Src\Traits\BuidlerAttr;

class Builder
{
   use BuidlerAttr;

    public function __construct()
    {
        $this->sql = new Select();
    }

    /**
     * Summary of toWith CTE
     * @param string $cte
     * @param null|Select|callable $callback
     * @param bool $recursive
     * @return array
     */
    public function toWith(
        string $cte,
        null | Select | callable $callback = null,
        bool $recursive = false
    ): array {
        $clone = $this->cloneSelf();

        $sub          = $callback;
        $s            = $sub instanceof Select ? $sub : $sub($clone->sql);
        $clone->cte[] = [
            'name'      => $cte,
            'params'    => $s->getParams(),
            'sql'       => $s->getQuery(),
            'recursive' => $recursive,
        ];

        return $clone->cte;
    }

    /**
     * Summary of toform
     * @param string|Select|callable $table
     * @param mixed $alias
     * @throws InvalidArgumentException
     * @return string
     */
    public function toform(string | Select | callable $table, ?string $alias = ''): string
    {
        $clone = $this->cloneSelf();

        if (is_callable($table)) {
            $sub = $table($clone->sql);
            if (! ($sub instanceof Select)) {
                throw new InvalidArgumentException('Subquery must return Sql instance');
            }

            $clone->params = array_merge($clone->params, $sub->getParams());
            $clone->table  = "({$sub->getQuery()}) AS " . ($alias ?: 't');
        } elseif ($table instanceof Select) {
            $clone->table  = "({$table->getQuery()}) AS " . ($alias ?: 't');
            $clone->params = array_merge($clone->params, $table->getParams());
        } else {
            $clone->table = $alias ? "$table AS $alias" : $table;
        }

        return $clone->table;
    }

    /**
     * Summary of toselect
     * @param array $cols
     * @return array[]
     */
    public function toselect(array $cols): array
    {
        $clone       = $this->cloneSelf();
        $selectParts = [];

        foreach ($cols as $i => $c) {
            if (\is_string($c)) {

                if(\str_contains($c,"-")){
                    $selectParts[] = "'{$c}'";
                }else{
                    $selectParts[] = "{$c}";
                }
                
            } else
            if (is_callable($c) || $c instanceof Select) {
                $sub           = $c;
                $s             = $sub instanceof Select ? $sub : $sub($clone->sql);
                $clone->params = array_merge($clone->params, $s->getParams());

                $selectParts[] = '(' . $s->getQuery() . ") AS cols_$i";
            } else 
            if (\is_array($c) && is_callable($c[0]) || $c[0] instanceof Select) {
                $alias         = $c[1] ?? "'cols_$i'";
                $sub           = $c[0];
                $s             = $sub instanceof Select ? $sub : $sub($clone->sql);
                $clone->params = array_merge($clone->params, $sub->getParams());

                $selectParts[] = '(' . $sub->getQuery() . ") AS '$alias'";
            }
        }

        $clone->cols = array_merge($clone->cols, $selectParts);
        return [$clone->cols, $clone->params];
    }

    /**
     * Summary of toCase
     * @param callable $callback
     * @param string $cols
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function toCase(callable|Cases $callback, string $cols): array
    {
        $clone = $this->cloneSelf();
       
        if($callback instanceof Cases){
             [$sql, $params] = $callback->toColumn($cols);
        $clone->cols    = array_merge($clone->cols, [$sql]);
        $clone->params  = array_merge($clone->params, $params);
       
        }

        return [$clone->cols, $clone->params];
    }

    /**
     * Summary of towhere
     * @param array $cols
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function towhere(array $cols = []): array
    {
        $clone = $this->cloneSelf();

        foreach ($cols as $k => $v) {
            if (preg_match('/(\w+)\s*([\=\>\!\<]+)/', $k, $m)) {
                $sign = $m[2];
                $key  = $m[1];
            } else {
                $sign = '=';
                $key  = $k;
            }
            $param = BindHashing::hash('wh', $key);

            if (is_callable($v)) {
                $sub = $v($clone->sql);
                if (! ($sub instanceof Select)) {
                    throw new InvalidArgumentException('Callable must return Sql instance');
                }

                $clone->params       = array_merge($clone->params, $sub->getParams());
                $clone->conditions[] = "$key $sign (" . $sub->getQuery() . ')';
            } else {
                $clone->conditions[]   = "$key $sign $param";
                $clone->params[$param] = $v;
            }
        }

        return [$clone->conditions, $clone->params];
    }

    /**
     * Summary of towhereColumn
     * @param string $col1
     * @param string|int $value
     * @param string $sign
     * @return array
     */
    public function towhereColumn(string $col1, string | int $value, string $sign = '='): array
    {
        $clone               = $this->cloneSelf();
        $clone->conditions[] = "$col1 $sign $value";
        return $clone->conditions;
    }

    /**
     * Summary of towhereOr
     * @param array $cols
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function towhereOr(array $cols = []): array
    {
        $clone = $this->cloneSelf();
        $or    = [];

        foreach ($cols as $k => $v) {
            $param = BindHashing::hash('whOr', $k);
            $sign  = '=';
            if (preg_match('/(\w+)\s*([\=\>\!\<]+)/', $k, $m)) {
                $sign = $m[2];
                $k    = $m[1];
            }

            if (is_callable($v)) {
                $sub = $v($clone->sql);
                if (! ($sub instanceof Select)) {
                    throw new InvalidArgumentException('Callable must be returned Select instance');
                }

                $clone->params = array_merge($clone->params, $sub->getParams());
                $or[]          = "$k $sign (" . $sub->getQuery() . ')';
            } else {
                $or[]                  = "$k $sign $param";
                $clone->params[$param] = $v;
            }
        }

        $clone->conditions[] = '(' . implode(' OR ', $or) . ')';
        return [$clone->conditions, $clone->params];
    }

    /**
     * Summary of towhereLike
     * @param array $cols
     * @return array[]
     */
    public function towhereLike(array $cols = []): array
    {
        $clone = $this->cloneSelf();
        $likes = [];

        foreach ($cols as $k => $v) {
            $param                 = BindHashing::hash("LIKE", $k);
            $likes[]               = "$k LIKE $param";
            $clone->params[$param] = "%$v%";
        }

        $clone->conditions[] = '(' . implode(' OR ', $likes) . ')';
        return [$clone->conditions, $clone->params];
    }

    /**
     * Summary of whereBetweenInternal
     * @param array $cols
     * @param bool $not
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function towhereBetween(array $cols, bool $not): array
    {
        $clone = $this->cloneSelf();
        $parts = [];

        foreach ($cols as $col => $range) {
            if (! \is_array($range) || \count($range) !== 2) {
                throw new InvalidArgumentException("whereBetween value must be [min,max] for $col");
            }
            $paramMin                 = BindHashing::hash('whbt', $col) . "_min";
            $paramMax                 = BindHashing::hash('whbt', $col) . "_max";
            $op                       = $not ? 'NOT BETWEEN' : 'BETWEEN';
            $parts[]                  = "$col $op $paramMin AND $paramMax";
            $clone->params[$paramMin] = $range[0];
            $clone->params[$paramMax] = $range[1];
        }

        $clone->conditions[] = implode(' AND ', $parts);
        return [$clone->conditions, $clone->params];
    }

    /**
     * Summary of toNull
     * @param string $col
     * @return string
     */
    public function toNull(string $col): string
    {
        return "$col IS NULL";
    }

    /**
     * Summary of toNotnull
     * @param string $col
     * @return string
     */
    public function toNotnull(string $col): string
    {
        return "$col IS NOT NULL";
    }

    /**
     * Summary of toExists
     * @param callable $callback
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function toExists(callable $callback, bool $not): array
    {
        $isNot = $not ? "NOT" : "";
        $clone = $this->cloneSelf();
        $sub   = $callback($clone->sql);
        if (! ($sub instanceof Select)) {
            throw new InvalidArgumentException('EXISTS callback must return Sql instance');
        }

        $clone->conditions[] = "EXISTS $isNot (" . $sub->getQuery() . ")";
        $clone->params       = array_merge($clone->params, $sub->getParams());

        return [
            $clone->conditions,
            $clone->params
        ];
    }

    /**
     * Summary of towhereIn
     * @param string $col
     * @param bool $not
     * @param callable|Select|array $values
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function towhereIn(string $col, callable | Select | array $values, bool $not): array
    {
        $clone = $this->cloneSelf();

        $isNot = $not ? "NOT" : "";
        if (\is_array($values)) {
            if (empty($values)) {
                throw new RuntimeException('whereIn values cannot be empty');
            }

            $placeholders = [];
            foreach ($values as $i => $v) {
                $param                 = BindHashing::hash('whin', $col) . "_$i";
                $placeholders[]        = $param;
                $clone->params[$param] = $v;
            }
            $clone->conditions[] = "$col $isNot IN(" . implode(',', $placeholders) . ')';
        } elseif (is_callable($values)) {
            $sub = $values($clone->sql);
            if (! ($sub instanceof Select)) {
                throw new InvalidArgumentException('Callable must return Sql instance');
            }

            $clone->params       = array_merge($clone->params, $sub->getParams());
            $clone->conditions[] = "$col $isNot IN(" . $sub->getQuery() . ')';
        } elseif ($values instanceof Select) {
            $clone->params       = array_merge($clone->params, $values->getParams());
            $clone->conditions[] = "$col $isNot IN(" . $values->getQuery() . ')';
        }

        return [$clone->conditions, $clone->params];
    }

    /**
     * Summary of toHaving
     * @param array $cols
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function toHaving(array $cols = []): array
    {
        $clone = $this->cloneSelf();
        foreach ($cols as $k => $v) {
            if (is_callable($v)) {
                $sub = $v($clone->sql);
                if (! ($sub instanceof Select)) {
                    throw new InvalidArgumentException('Callable must return Sql instance');
                }

                $clone->having[] = "$k = (" . $sub->getQuery() . ')';
                $clone->params   = array_merge($clone->params, $sub->getParams());
            } else {
                $param                 = BindHashing::hash('hav', $k);
                $clone->having[]       = "$k = $param";
                $clone->params[$param] = $v;
            }
        }

        return [$clone->having, $clone->params];
    }

    /**
     * Summary of toGroupsBy
     * @param array $groups
     * @return array
     */
    public function toGroupsBy(array $groups = []): array
    {
        $clone         = $this->cloneSelf();
        $clone->groups = $groups;
        return $clone->groups;
    }

    /**
     * Summary of toOrderBy
     * @param string $key
     * @param string $order
     * @throws RuntimeException
     * @return string
     */
    public function toOrderBy(string $key = '', string $order = 'ASC'): string
    {
        $clone = $this->cloneSelf();
        $order = strtoupper($order);
        if (! \in_array($order, ['ASC', 'DESC'])) {
            throw new RuntimeException('Invalid order value');
        }

        $clone->order = "$key $order";
        return $clone->order;
    }

    /**
     * Summary of toLimit
     * @param mixed $limit
     * @return int|null
     */
    public function toLimit(?int $limit = null): ?int
    {
        $clone        = $this->cloneSelf();
        $clone->limit = $limit;
        return $clone->limit;
    }

    /**
     * Summary of toOffset
     * @param mixed $offset
     * @return int|null
     */
    public function toOffset(?int $offset = null): ?int
    {
        $clone         = $this->cloneSelf();
        $clone->offset = $offset;
        return $clone->offset;
    }

    /**
     * Summary of toJoin
     * @param string $type
     * @param callable|Join $callback
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function toJoin(string $type, callable | Join $callback): array
    {
        $clone   = $this->cloneSelf();
        $joinObj = $callback instanceof Join ? $callback : $callback(new Join());
        if (! ($joinObj instanceof Join)) {
            throw new InvalidArgumentException('join must be returned class Join');
        }
        $sql = "";
        $params = [];
        
        [$sql, $params] = $joinObj->getJoin($type);
        $clone->joins[]         = $sql;

        $clone->params = array_merge($clone->params, $params);
        return [$clone->joins, $clone->params];
    }

    /**
     * Summary of toUnion
     * @param bool $all
     * @param callable|Union|array $query
     * @param array $cols
     * @param bool $dist
     * @throws InvalidArgumentException
     * @return array[]
     */
    public function toUnion(bool $all, callable | Union | array $query, array $cols, bool $dist): array
    {
        $clone = $this->cloneSelf();

        $union = $query instanceof Union ? $query : $query(new Union());
        if (! ($union instanceof Union)) {
            throw new InvalidArgumentException('union must be returned class Union');
        }

        $union->instance($cols)->distinct($dist);
        [$sql, $params] = $union->getUnion($all);
        $clone->union[] = $sql;
        $clone->params  = array_merge($clone->params, $params);
        return [$clone->union, $clone->params];
    }

    /**
     * Summary of toexplain
     * @param callable|Select|string $explain
     * @param string $mode
     * @return array<array|string>
     */
    public function toexplain(callable | Select | string $explain, string $mode = ''): array
    {
        $clone         = $this->cloneSelf();
        $sql           = '';
        $clone->params = [];

        if (is_callable($explain)) {
            $sub = $explain($clone->sql);
            if ($sub instanceof Select) {
                $sql           = 'EXPLAIN ' . ($mode ? " {$mode} " : '') . $sub->getQuery();
                $clone->params = $sub->getParams();

            }
        } elseif ($explain instanceof Select) {
            $sql           = 'EXPLAIN ' . ($mode ? " {$mode} " : '') . $explain->getQuery();
            $clone->params = $explain->getParams();
        } else {
            $sql = 'EXPLAIN ' . ($mode ? " {$mode} " : '') . $explain;
        }

        return [$sql, $clone->params];
    }

    /**
     * Summary of toSub
     * @param callable|Select|string $subquery
     * @param string $alias
     * @throws InvalidArgumentException
     * @return string
     */
    public function toSub(callable | Select | string $subquery, string $alias): string
    {
        $clone = $this->cloneSelf();
        if (is_callable($subquery)) {
            $sub = $subquery($clone->sql);
            if (! ($sub instanceof Select)) {
                throw new InvalidArgumentException('Subquery callable must return Sql');
            }

            $sql = '(' . $sub->getQuery() . ') AS ' . ($alias ?: 'alias');
        } elseif ($subquery instanceof Select) {
            $sql = '(' . $subquery->getQuery() . ') AS ' . ($alias ?: 'alias');
        } else {
            $sql = " (  $subquery ) AS " . ($alias ?: 'alias');
        }

        return $sql;
    }

    /**
     * Summary of cloneSelf
     * @return self
     */
    private function cloneSelf(): self
    {
        $clone             = clone $this;
        $clone->sql        = clone $this->sql;
        $clone->cols       = array_merge([], $this->cols);
        $clone->params     = array_merge([], $this->params);
        $clone->conditions = array_merge([], $this->conditions);
        $clone->groups     = array_merge([], $this->groups);
        $clone->having     = array_merge([], $this->having);
        $clone->cte        = array_merge([], $this->cte);
        $clone->joins      = array_merge([], $this->joins);
        return $clone;
    }
}
