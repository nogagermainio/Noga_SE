<?php
namespace Src\QueryBuilder\Crud\Update;

use PDOException;
use PDOStatement;
use RuntimeException;
use Src\Core\BindHashing;
use Src\Traits\Condition;
use Src\Traits\DbTrait;
/**
 * Summary of CRUDUpdate
 */
class Update
{
    use Condition;
    use DbTrait;
    private string $table = '';
    private string $sql = '';
    private array $set        = [];

    
    public function __construct(string $table)
    {
       $this->table = $table;
    }

    public static function table(string $table):static{
        return clone new static($table);
    }

    /**
     * Summary of set
     * @param array $cols colonne en table
     * @return static
     */
    public function set(array $cols = []): static
    {
        $clone = clone $this;
        foreach ($cols ?? [] as $k => $v) {
            $key                       = BindHashing::hash("set",$k);
            $clone->set[]               = "$k = $key";
            $clone->params[$key] = $v;
        }

        return $clone;
    }

    public function compile(){
        $set = implode(',',$this->set);
        $condition = implode(' AND ',$this->conditions);
        $this->sql = "UPDATE {$this->table} ";
        $this->sql .= " SET  {$set} ";
        if(empty($this->conditions)) throw new RuntimeException(" cannot use update if conditions is empty ");
        $this->sql .=" WHERE {$condition} ";

        return $this->sql;
    }


    public function getQuery():string{
        return $this->compile();
    }

    public function getParams():array{
        return $this->params ?? [];
    }

    public function getSetter():array{
        return $this->set ?? [];
    }

    public function viewState():array{
        return [
            "Query"=>$this->getQuery(),
            "params"=>$this->getParams(),
            "table"=>$this->table,
            "driver"=>$this->driver,
            "set"=>$this->getSetter()
        ];
    }

    public function exec():bool|PDOStatement{
        $this->stmt = $this->db()
        ->execute($this->compile(),$this->params);
        if(!$this->stmt) throw new PDOException("Error : ".$this->stmt->errorInfo());
        return $this->stmt;
    }



}
