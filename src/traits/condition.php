<?php
namespace Src\Traits;

use Src\QueryBuilder\Builder;
use Src\QueryBuilder\Select\Select;

trait Condition{

    
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
    protected ?Builder $buildClause = null;
 /**
     * Summary of initClause
     * @return Builder|null
     */
    private function initClause(): Builder
    {
        if ($this->buildClause === null) {
            $this->buildClause = new Builder();
        }
        return clone $this->buildClause;
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
     * Summary of mergeParams
     * @param array $params
     * @return array
     */
    private function mergeParams(array $params): array
    {
        $this->params = array_merge($this->params, $params);
        return $this->params;
    }

     public function isNull(string $value): static
    {
        $clone             = clone $this;
        $conditions        = $clone->initClause()->toNull($value);
        $clone->conditions = $clone->mergeCond([$conditions]);
        return $clone;
    }

    /**
     * Summary of isNotnull
     * @param string $value
     * @return static
     */
    public function isNotnull(string $value): static
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
     * @return static
     */
    public function where(array $cols): static
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
     * @return static
     */
    public function whereOr(array $cols): static
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
     * @return static
     */
    public function whereLike(array $cols): static
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
     * @param callable|Select|array $values
     * @param bool $not
     * @return static
     */
    public function whereIn(string $cols, callable | Select | array $values, bool $not = false): static
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
     * @return static
     */
    public function whereBetween(array $cols): static
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
     * @return static
     */
    public function whereNotBetween(array $cols): static
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
     * @return static
     */
    public function whereColumn(string $cols, string | int $value, string $signComparaison = '='): static
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
     * @return static
     */
    public function whereExists(callable $callback): static
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
     * @return static
     */
    public function whereNotexists(callable $callback): static
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
     * @return static
     */
    public function having(array $cols): static
    {
        $clone                           = clone $this;
        [$clone->having, $clone->params] = $clone->initClause()
            ->toHaving($cols);
        return $clone;
    }

}