<?php
namespace Src\QueryBuilder;

use Src\Sql;
use Src\QueryBuilder\ClauseBuilder;
class UnionBuilder
{
    public array $unions = [];
    protected array $table = [];
    protected array $columns = [];
    protected array $conditions = [];
    protected array $params = [];
    protected array $rows = [];
    protected array $cols = [];
    protected bool $distinct = false;
    protected array $groups = [];

    protected ?ClauseBuilder $clauseBuilder = null;
    private ?Sql $sql = null;

    public function __construct()
    {
       $this->sql = new Sql();
       $this->unions = [];
       
    }

    public function instance(array $cols,?bool $distinct = false):static{
             $this->cols = $cols;
             $this->distinct = $distinct;
        return $this;
    }
    public function getUnion(bool $all = false):array
    { 
        $all = $all ? "ALL":"";
        $sql = "";
        
        $col = !empty($this->columns) ? $this->columns : $this->cols;
        $cols = !empty($col) ? $col : ["*"];
        $dist = $this->distinct ? "DISTINCT":"";
        $group = !empty($this->group) ? \implode(',',$this->groups) : "";
        foreach($this->table ?? [] as $table){
            $sql .= " UNION {$all}  SELECT {$dist} ".implode(",",$cols)." FROM {$table} {$group} ";
        }

        if(!empty($this->rows)){
            foreach($this->rows as $row){
                $sql .= " UNION {$all}  $row ";
            }
        }
        
        return [$sql,$this->params];
    }
    /**
     * Summary of table
     * @param array $array
     * @return UnionBuilder
     */
    public function from(...$array):UnionBuilder{
        $clone = clone $this;
        $clone->table = $array;
        return $clone;
    }
    /**
     * Summary of where
     * @param array $conditions
     * @return UnionBuilder
     */
    public function where(array $conditions):UnionBuilder{
     $clone = clone $this;
     $clone->conditions = $conditions;

     return $clone;   
    }

    public function groupBy(array $array):UnionBuilder{
        $clone = clone $this;
        $clone->groups[] = $array;
        return $clone;
    }
    /**
     * Summary of select
     * @param array $columns
     * @return UnionBuilder
     */
    public function select(...$columns):UnionBuilder{
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }

    public function distinct(bool $distinct = false):UnionBuilder{
        $clone = clone $this;
        $clone->distinct = $distinct;
        return $clone;
    }

    public function add(array $row):UnionBuilder{
        $clone = clone $this;
        $state = [];
      foreach($row as $r){
        if(\is_callable($r) || $r instanceof Sql){
            $clone->rows[] = $r->getSql();
            $clone->params = \array_merge($clone->params,$r->getParams());

        }else if(\is_string($r)){
            $clone->rows[] = $r;
            $state[] = $clone->rows;
        }
      }

        return $clone;
    }
}
