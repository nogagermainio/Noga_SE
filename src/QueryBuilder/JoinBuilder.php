<?php
namespace Src\QueryBuilder;
use InvalidArgumentException;

use Src\Sql;

class JoinBuilder
{
    protected array $joins = [];
    protected string $table = "";
    protected string $type = "";
    protected string $as = "";
    protected array $on = [];
    protected array $params = [];
    protected string $sql = ""; 
    protected ?Sql $req = null;

    public function __construct()
    {

        $this->params = [];
        $this->req = new Sql();
    }

    public function type(string $type):JoinBuilder{
        $clone = clone $this;
        $clone->type = \strtoupper($type);
        return $clone;
    }
    /**
     * Summary of table
     * @param callable|Sql|string $table
     * @return JoinBuilder
     */
    public function table(string|Sql|callable $table):JoinBuilder{
        $clone = clone $this;
        if(is_string($table)){
              $clone->table = $table;

        }else if(\is_callable($table) || $table instanceof Sql){

          $sub = $table instanceof Sql ? $table : $table($clone->req);
           if(!$sub instanceof Sql) 
            throw new InvalidArgumentException("Error must be return Sql instance");

          $clone->table = "(".$sub->getSql().")";

          $clone->params = \array_merge($clone->params,$sub->getParams() ?? []);
        }

        return $clone;
       
    }

    public function as(string $alias):JoinBuilder{
        $clone = clone $this;
        $clone->as = $alias;
        return $clone;
    }

    public function on(string $cols1,string $cols2,string $comparatif = "="):JoinBuilder{
        $clone = clone $this;
        $clone->on[] = "{$cols1} {$comparatif} {$cols2}";

        return $clone;
    }

    public function andOn(string $cols1,string $cols2,string $comparatif = "="):JoinBuilder{
         $clone = clone $this;
        $clone->on[] = "{$cols1} {$comparatif} {$cols2}";

        return $clone;
    }

   public function getJoin(string $type):array
{
    $this->type = $type;
    $sql = " ".PHP_EOL." {$this->type} JOIN {$this->table}";

    if ($this->as !== "") {
        $sql .= " AS {$this->as}";
    }

    if (!empty($this->on)) {
        $sql .= " ON ".implode(' AND ',$this->on)."";
    }


    return [$sql, $this->params];
}


}
