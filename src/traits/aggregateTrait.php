<?php declare(strict_types=1);

namespace Noga\Traits;

use Noga\QueryBuilder\Select\Aggregate\Aggregate;
use Noga\QueryBuilder\Select\Select;

trait AggregateTrait{

    protected ?Aggregate $buildaggregate = null;
 
      /**
     * Summary of initaggregate
     * @return Aggregate|null
     */
    private function initaggregate(): Aggregate
    {
        if ($this->buildaggregate === null) {
            $this->buildaggregate = new Aggregate();
        }
        return $this->buildaggregate;
    }

    /**
     * Summary of sum
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function sum(string | Select | callable $value, ?string $alias = ''): string
    {
        $instance = new static();

        return $instance->initaggregate()
            ->sum($value, $alias);
    }

    /**
     * Summary of avg
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function avg(string | Select | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->avg($value, $alias);
    }

    /**
     * Summary of max
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function max(string | Select | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->max($value, $alias);
    }

    /**
     * Summary of min
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function min(string | Select | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->min($value, $alias);
    }

    /**
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public static function count(string | Select | callable $value, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->count($value, $alias);
    }

    /**
     * Summary of coalesce
     * @param string|Select|callable $value
     * @param string|int $concat
     * @param mixed $alias
     * @return string
     */
    public static function coalesce(string | Select | callable $value, string | int $concat, ?string $alias = ''): string
    {
        $instance = new static();
        return $instance->initaggregate()
            ->coalesce($value, $concat, $alias);
    }
}