<?php declare(strict_types=1);
namespace Noga\QueryBuilder\Select\Join;
use InvalidArgumentException;
use Noga\QueryBuilder\Select\Select;

class Join
{
    protected array $joins = [];
    protected string $table = "";
    protected string $type = "";
    protected string $as = "";
    protected array $on = [];
    protected array $params = [];
    protected string $sql = ""; 
    protected ?Select $req = null;

    public function __construct()
    {

        $this->params = [];
        $this->req = new Select();
    }

    public function type(string $type):Join{
        $clone = clone $this;
        $clone->type = \strtoupper($type);
        return $clone;
    }
    /**
     * Summary of table
     * @param callable|Select|string $table
     * @return Join
     */
    public function table(string|Select|callable $table):Join{
        $clone = clone $this;
        if(\is_string($table)){
              $clone->table = $table;

        }else if(\is_callable($table) || $table instanceof Select){

          $sub = $table instanceof Select ? $table : $table($clone->req);
           if(!$sub instanceof Select) 
            throw new InvalidArgumentException("Error must be return Sql instance");

          $clone->table = "(".$sub->getQuery().")";

          $clone->params = \array_merge($clone->params,$sub->getParams() ?? []);
        }

        return $clone;
       
    }

    public function as(string $alias):Join{
        $clone = clone $this;
        $clone->as = $alias;
        return $clone;
    }

    public function on(string $cols1,string $cols2,string $comparatif = "="):Join{
        $clone = clone $this;
        $clone->on[] = "{$cols1} {$comparatif} {$cols2}";

        return $clone;
    }

    public function andOn(string $cols1,string $cols2,string $comparatif = "="):Join{
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
