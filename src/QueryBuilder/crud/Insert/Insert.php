<?php
namespace Src\QueryBuilder\Crud\Insert;

use finfo;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Src\Core\BindHashing;
use Src\Traits\DbTrait;

class Insert
{
    use DbTrait;
    private string $table;
    private array $columns = [];
    private array $values = [];
    private array $params = [];
    private array $binding = [];
    private string $file = "";
    private array $except = [];
    private array $data = [];
    private string $sql = "";


    /**
     * Summary of table
     * @param string $table
     * @return static
     */
    public function table(string $table):static{
        $clone = clone $this;
        $clone->table = $table;
        return $clone;
    }

    /**
     * Summary of columns
     * @param string[] $columns
     * @return static
     */
    public function columns(string ...$columns):static
    {
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }

    /**
     * Summary of values
     * @param string|int|bool[] $values
     * @return static
     */
    public function values(string|int|bool ...$values):static{
        $clone = clone $this;
        $clone->values = array_merge($clone->values,$values);
        return $clone;
    }

     /**
      * Summary of from
      * @param string $file
      * @throws InvalidArgumentException
      * @throws RuntimeException
      * @return static
      */
     public function from(string $file):static{
        $clone = clone $this;
        $finfo = new finfo(\FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file,\FILEINFO_MIME_TYPE);

        if(\file_exists($file)){
            if($mime !== "application/json"){
                throw new InvalidArgumentException("Invalid type mime json validate !");
            }
        }else{
            throw new RuntimeException("failed to open stream : {$file} ");
        }

        $clone->file = $file;
        
        return $clone;
    }

    /**
     * Summary of except
     * @param string[] $columns
     * @return static
     */
    public function except(string ...$columns){
        $clone = clone $this;
        $clone->except = \array_merge($this->except,$columns);
        return $clone;
    }

    /**
     * Summary of take
     * @throws RuntimeException
     * @return static
     */
    public function take():static{
        $clone = clone $this;

        $json = \file_get_contents($this->file);
        $data = \json_decode($json,true);
        
        if(!\is_array($clone->data)){
            throw new RuntimeException("Invalide JSON values. ");
        }
        
         $columns = \array_diff(array_keys($data[0]),$this->except);
        $clone->columns = $columns;

        foreach ($data as $raws) {
            $values = [];
            foreach ($columns as $col) {
                $values[] = $raws[$col] ?? null;
            }

            $clone->values[] = $values;
        }

       return $clone;

    }

    /**
     * Summary of binding
     * @return static
     */
    private function binding():static{
        $key = [];
        $keys = "";
        foreach($this->columns as $k => $cols){
            $keys = BindHashing::hash('in',$cols);          
            if(\is_array($this->values[0])){
                  $key[] = $keys;
               $this->params = $this->bulkBinding($key,$this->values);
                
            }
              $this->params[$keys] = $this->values[$k]; 
              
         $this->binding[] = $keys;   
        }

        return $this;
    }

    /**
     * Summary of bulkBinding
     * @param array $key
     * @param array $values
     * @return array[]
     */
    private function bulkBinding(array $key,array $values):array{
        $params = [];
        
        foreach($values as $k => $v){
            foreach($v as $ke => $c){
                $s = $key[$ke] ?? null;
                $params[$k][$s] = $c;
            }
        }
        
        return $params;
    }

    /**
     * Summary of compile
     * @return string
     */
    private function compile():string{
        $this->binding();

        $cols = implode(',',$this->columns);
        $val = implode(',',$this->binding);

        $this->sql = "INSERT INTO {$this->table}(";   
        $this->sql .=" {$cols} ) ";
        $this->sql .= " VALUES({$val})";

        return $this->sql;
    }

    /**
     * Summary of exc
     * @throws PDOException
     * @return bool|string
     */
    public function exec():string|bool{
        $this->stmt = $this->db()
            ->execute($this->compile(),$this->params);
         if(!$this->stmt) throw new PDOException("Erreur  : ".$this->stmt->errorInfo());

         return $this->db()->lastId();
    }

    public function getQuery():string{
        return $this->compile();
    }

    public function getValues():array{
        return $this->values;
    }

    /**
     * Summary of debugSql
     * @return array{binding: array, columns: array, driver: string, params: array, sql: string, table: string, values: array}
     */
    public function viewState():array{
        return [
            "sql"=>$this->getQuery(),
            "params"=>$this->params,
            "driver"=>$this->driver,
            "table"=>$this->table,
            "columns"=>$this->columns,
            "values"=>$this->getValues(),
            "binding"=>$this->binding
        ];
    }





}
