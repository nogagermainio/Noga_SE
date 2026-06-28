<?php
namespace Src\Schema;

use Src\Sql;
use InvalidArgumentException;
use RuntimeException;
use Src\Schema\Schema;

class Alter{
    private array $column = [];
    private string $table = "";

    public function __construct(string $table) {
        $this->table = $table;
    }
    

    //table getter
    public static function table(string $table){
       return new static($table);
    }

    // add method
    public function add_column(string|array $column,
        string|array $type,
        bool $nullable = false,
        string|array $default = "",
        string $comment = ""){
            $clone = clone $this;
             if(empty($column))
            throw new RuntimeException("column cannot empty ! ");

         $null = $nullable ? " NULL " : " NOT NULL ";
         $comment = !empty($comment) ? " COMMENT '{$comment}' ":"";
        
        if(\is_array($column)){

            foreach($column as $k => $cols){
                $def =  \is_array($default) ? $default[$k] : $default;
                $default = !empty($def) ? " DEFAULT {$def} ":"";

                $clone->column[] = " ADD COLUMN {$cols} ".\is_array($type) ?
                  $type[$k] : $type." 
                  {$null} {$default} 
                  {$comment} ";
            }

        }else if(is_string($column)){
             $default = !empty($default) ? " DEFAULT '{$default}' ":"";

                $clone->column[] = " ADD COLUMN {$column} 
                 {$type} {$null} 
                 {$default} {$comment} ";
        }

        return $clone;

        }

         public function add_Index(string $index,array|string $columns){
            $clone = clone $this;
        $cols = is_array($columns) ? implode(',',$columns) : $columns;
        $clone->column[] = "ADD INDEX {$index}($cols)";
        return $clone;
     }

    public function add_Primary(string|array $primary_key){
        $clone = clone $this;
        $primary_key = is_array($primary_key) ? implode(',',$primary_key): $primary_key;
        $clone->column[] = " ADD PRIMARY KEY ($primary_key) ";
        return $clone;
    }
    public function add_Unique(string $index,string $columns){
        $clone = clone $this;
        $clone->column[] = " ADD UNIQUE {$index}($columns) ";
        return $clone;
    }

    public function add_FullText(string $index,string $column){
        $clone = clone $this;
        $clone->column[] = " ADD FULLTEXT {$index} ($column) ";
        return $clone;
    }

    public function add_Spatial(string $index,string $column){
        $clone = clone $this;
        $clone->column[] = " ADD SPATIAL {$index} ($column) ";
        return $clone;
    }

    public function add_Foreign(string $cols,callable|Sql $callback){
        $clone = clone $this;
        if ($callback instanceof Schema) {
            $clone->column[] = " ADD ".$callback->buildForeignKey($cols);
        } else if (is_callable($callback)) {
            $call = $callback($clone);

            $clone->column[] = $call instanceof Schema
                ?" ADD ".$call->buildForeignKey($cols)
                : throw new InvalidArgumentException('Error callback return Sql');
        } else if (\is_string($callback)) {
            $clone->column[] = " ADD {$callback} ";
        }

        return $clone;
    }

    // edit method
    public function change_column(
        string $new_name,
        string $old_name,
        string $type,
        bool $nullable = false
        ){
            $clone = clone $this;
        $clone->column[] = " CHANGE COLUMN {$old_name} {$new_name} TYPE [$type] ";
        return $clone;
    }
    
    public function modify_column(string $column,array|string $type,string $comment = ""){
        $clone = clone $this;    
        $clone->column[] = " MODIFY COLUMN {$column} TYPE [{$type}] ";
            return $clone;
    }  

    public function set_Default(string $column,string $values){
        $clone = clone $this;
        $clone->column[] = " {$column} SET DEFAULT {$values} ";
        return $clone;
    }
    
    //Drop method
    public function drop_column(string $column){
        $clone = clone $this;
        $clone->column[] = " DROP COLUMN {$column} ";
        return $clone;
    }

    public function drop_Default(string $column){
       $clone = clone $this;
        $clone->column[] = " {$column} DROP DEFAULT ";
        return $clone;
    }

    public function table_Engine(string $engine){
        $clone = clone $this;
        $clone->column[] = " ENGINE = {$engine} ";
        return $clone;
    }

    public function table_Charset(string $charset){
        $clone = clone $this;
        $clone->column[] = " CHARSET {$charset} ";
        return $clone;
    } 

    public function table_Rename(string $new_name){
        $clone = clone $this;
        $clone->column[] = " RENEME TO {$new_name} ";
        return $clone;
    }

    public function table_Comment(string $comment){
        $clone = clone $this;
        $clone->column[] = " COMMENT = '{$comment}' ";
        return $clone;
    }

    public function column_Order(string $column,string $after){
        $clone = clone $this;
        $clone->column[] = " MODIFY COLUMN {$column} TYPE [AFTER {$after}] ";
        return $clone;
    }

    public function column_To_First(string $column){
        $clone = clone $this;
        $clone->column[] = " MODIFY COLUMN {$column} TYPE FIRST ";
        return $clone;
    }
    
    public function column_To_Last(string $column){
        $clone = clone $this;
        $clone->column[] = " MODIFY COLUMN {$column} TYPE LAST ";
        return $clone;
    }

    public function buildAlter():string{
        $alter = "ALTER TABLE {$this->table} ";
        $alter .= !empty($this->column) ? implode(' , ',$this->column) :"";

        return $alter;
    }

}