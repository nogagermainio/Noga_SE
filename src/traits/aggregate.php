<?php

namespace Src\Traits;
use Src\Sql;
use Src\QueryBuilder\AggregateBuilder;

trait Aggregate{

    protected ?AggregateBuilder $buildaggregate = null;
 
      /**
     * Summary of initaggregate
     * @return AggregateBuilder|null
     */
    private function initaggregate(): AggregateBuilder
    {
        if ($this->buildaggregate === null) {
            $this->buildaggregate = new AggregateBuilder();
        }
        return $this->buildaggregate;
    }

    /**
     * Summary of sum
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function sum(string | Sql | callable $value, ?string $alias = ''): string
    {
        $instance = new static();

        return $instance->initaggregate()
            ->sum($value, $alias);
    }

    /**
     * Summary of avg
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function avg(string | Sql | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->avg($value, $alias);
    }

    /**
     * Summary of max
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function max(string | Sql | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->max($value, $alias);
    }

    /**
     * Summary of min
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function min(string | Sql | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->min($value, $alias);
    }

    /**
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function count(string | Sql | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->count($value, $alias);
    }

    /**
     * Summary of coalesce
     * @param string|Sql|callable $value
     * @param string|int $concat
     * @param mixed $alias
     * @return string
     */
    public static function coalesce(string | Sql | callable $value, string | int $concat, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->coalesce($value, $concat, $alias);
    }
}