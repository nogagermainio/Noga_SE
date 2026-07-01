<?php
namespace Src\QueryBuilder\Crud\Delete;
use PDOException;
use PDOStatement;
use RuntimeException;
use Src\Traits\Condition;
use Src\Traits\DbTrait;

class Delete
{
    use Condition;
    use DbTrait;
    protected string $sql = '';
    private string $table = '';
    public function __construct(string $table)
    {
       $this->table = $table;
    }

    public static function table(string $table):static{
        return clone new static($table);
    }

    private function compile(){
        $conditions = implode(' AND ',$this->conditions); 
        $this->sql = " DELETE FROM {$this->table} ";
        if(empty($this->conditions)) throw new RuntimeException("Cannot use delete if conditions is empty ");
        $this->sql .= " WHERE {$conditions} ";

        return $this->sql;
    }

    public function getQuery():string{
        return $this->compile();
    }

    public function getParams():array{
        return $this->params;
    }

    public function viewState():array{
        return [
            "Query"=>$this->getQuery(),
            "params"=>$this->getParams(),
            "table"=>$this->table,
            "driver"=>$this->driver
        ];
    }

    public function exec():bool|PDOStatement{
        $this->stmt = $this->db()
        ->execute($this->compile(),$this->getParams());

        if(!$this->stmt) throw new PDOException("Error : ".$this->stmt->errorInfo());

        return $this->stmt;
    }



}
