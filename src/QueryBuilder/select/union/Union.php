<?php
namespace Noga\QueryBuilder\Select\Union;

use Noga\QueryBuilder\Builder;
use Noga\QueryBuilder\Select\Select;
class Union
{
    /**
     * Summary of unions
     * @var array
     */
    public array $unions = [];
    protected array $table = [];
    protected array $columns = [];
    protected array $conditions = [];
    protected array $params = [];
    protected array $rows = [];
    protected array $cols = [];
    protected bool $distinct = false;
    protected array $groups = [];

    protected ?Builder $clauseBuilder = null;
    private ?Select $sql = null;

    public function __construct()
    {
       $this->sql = new Select();
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
     * @return Union
     */
    public function from(string ...$array):Union{
        $clone = clone $this;
        $clone->table = $array;
        return $clone;
    }

    /**
     * Summary of groupBy
     * @param array $array
     * @return Union
     */
    public function groupBy(array $array):Union{
        $clone = clone $this;
        $clone->groups[] = $array;
        return $clone;
    }
    /**
     * Summary of select
     * @param array<string> $columns
     * @return Union
     */
    public function select(string ...$columns):Union{
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }

    /**
     * Summary of distinct
     * @param bool $distinct
     * @return Union
     */
    public function distinct(bool $distinct = false):Union{
        $clone = clone $this;
        $clone->distinct = $distinct;
        return $clone;
    }

    public function add(array $row):Union{
        $clone = clone $this;
        $state = [];
      foreach($row as $r){
        if(\is_callable($r) || $r instanceof Select){
            $clone->rows[] = $r->getQuery();
            $clone->params = \array_merge($clone->params,$r->getParams());

        }else if(\is_string($r)){
            $clone->rows[] = $r;
            $state[] = $clone->rows;
        }
      }

        return $clone;
    }
}
