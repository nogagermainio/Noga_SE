<?php declare(strict_types=1);
namespace Noga\QueryBuilder\Select\Aggregate;

use InvalidArgumentException;
use Noga\QueryBuilder\Select\Select;

class Aggregate
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
     * Summary of Select
     * @var Select
     */
    private Select $sql;

    public function __construct()
    {
        $this->function = '';
        $this->columns = '';
        $this->alias = '';
        $this->sql = new Select();
    }

    private function this()
    {
        $clone = clone $this;
        return $clone;
    }

    /**
     * Summary of count
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public function count(string|Select|callable $value, ?string $alias = ''):string
    {
        $clone = $this->this();
        return $clone->agregate('COUNT', $value, $alias);
    }

    /**
     * Summary of sum
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public function sum(string|Select|callable $value, ?string $alias = ''):string
    {
        $clone = $this->this();
        return $clone->agregate('SUM', $value, $alias);
    }

    /**
     * Summary of avg
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public function avg(string|Select|callable $value, ?string $alias = ''):string
    {
        $clone = $this->this();
        return $clone->agregate('AVG', $value, $alias);
    }

    /**
     * Summary of max
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public function max(string|Select|callable $value, ?string $alias = ""):string
    {
        $clone = $this->this();
        return $clone->agregate('MAX', $value, $alias);
    }

    /**
     * Summary of min
     * @param string|Select|callable $value
     * @param mixed $alias
     * @return string
     */
    public function min(string|Select|callable $value, ?string $alias = ""):string
    {
        $clone = $this->this();
        return $clone->agregate('MIN', $value, $alias);
    }

    public function coalesce(string|Select|callable $value,string|int $concat, ?string $alias =""):string{
        $clone = $this->this();
       return $clone->agregate("COALESCE",$value,$alias,$concat);
    }

    /**
     * Summary of agregate
     * @param string $function
     * @param string|Select|callable $value
     * @param mixed $alias
     * @throws InvalidArgumentException
     * @return string
     */
    private function agregate(string $function, string|Select|callable $value, ?string $alias = '',string|int $def = ""):string
    {
        $clone = $this->this();
        $clone->alias = !empty($alias) ? "AS $alias" : '';
        $clone->function = $function;

        if (\is_callable($value)) {
            $values = $value($clone->sql);

            $val = $values instanceof Select ? $values : $values();
            if (!($val instanceof Select)) throw new InvalidArgumentException('the agregate callback return Select');

            if($function == "COALESCE"){
                  $clone->columns = " $function({$val->getQuery()},{$def}) {$clone->alias} ";
            }else{
                $clone->columns = " $function({$val->getQuery()}) {$clone->alias} ";
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
