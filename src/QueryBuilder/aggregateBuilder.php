<?php
namespace Src\QueryBuilder;

use InvalidArgumentException;
use Src\Sql;

class AggregateBuilder
{
    /**
     * Summary of function
     * @var string
     */
    private string $function;
    /**
     * Summary of columns
     * @var string
     */
    private string $columns;
    /**
     * Summary of alias
     * @var string
     */
    private string $alias;
    /**
     * Summary of sql
     * @var Sql
     */
    private Sql $sql;

    public function __construct()
    {
        $this->function = '';
        $this->columns = '';
        $this->alias = '';
        $this->sql = new Sql();
    }

    private function this()
    {
        $clone = clone $this;
        return $clone;
    }

    /**
     * Summary of count
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public function count(string|Sql|callable $value, ?string $alias = ''):string
    {
        $clone = $this->this();
        return $clone->agregate('COUNT', $value, $alias);
    }

    /**
     * Summary of sum
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public function sum(string|Sql|callable $value, ?string $alias = ''):string
    {
        $clone = $this->this();
        return $clone->agregate('SUM', $value, $alias);
    }

    /**
     * Summary of avg
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public function avg(string|Sql|callable $value, ?string $alias = ''):string
    {
        $clone = $this->this();
        return $clone->agregate('AVG', $value, $alias);
    }

    /**
     * Summary of max
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public function max(string|Sql|callable $value, ?string $alias = ""):string
    {
        $clone = $this->this();
        return $clone->agregate('MAX', $value, $alias);
    }

    /**
     * Summary of min
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @return string
     */
    public function min(string|Sql|callable $value, ?string $alias = ""):string
    {
        $clone = $this->this();
        return $clone->agregate('MIN', $value, $alias);
    }

    public function coalesce(string|Sql|callable $value,string|int $concat, ?string $alias =""):string{
        $clone = $this->this();
       return $clone->agregate("COALESCE",$value,$alias,$concat);
    }

    /**
     * Summary of agregate
     * @param string $function
     * @param string|Sql|callable $value
     * @param mixed $alias
     * @throws InvalidArgumentException
     * @return string
     */
    private function agregate(string $function, string|Sql|callable $value, ?string $alias = '',string|int $def = ""):string
    {
        $clone = $this->this();
        $clone->alias = !empty($alias) ? "AS $alias" : '';
        $clone->function = $function;

        if (\is_callable($value)) {
            $values = $value($clone->sql);

            $val = $values instanceof Sql ? $values : $values();
            if (!($val instanceof Sql)) throw new InvalidArgumentException('the agregate callback return Sql');

            if($function == "COALESCE"){
                  $clone->columns = " $function({$val->getSql()},{$def}) {$clone->alias} ";
            }else{
                $clone->columns = " $function({$val->getSql()}) {$clone->alias} ";
            }
            
        } else if (is_string($value)) {
            if($function == "COALESCE"){
                 $clone->columns = " {$clone->function}($value,{$def}) {$clone->alias} ";
            }else
            {
                  $clone->columns = " {$clone->function}($value) {$clone->alias} ";
            }
          
        }

        return $clone->columns;
    }
}
