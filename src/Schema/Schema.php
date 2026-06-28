<?php
namespace Src\Schema;

use PDOException;
use RuntimeException;
use Src\StateBuilder\QueryState;
class Schema
{
   use \Src\Traits\DbTrait;
   use \Src\Schema\AlterTable;
    protected mixed $creates = null;
    protected string $table = '';
    protected bool $notExist;
    protected string $engine = '';
    protected string $charset = '';
    protected string $collate = '';
    protected array $column = [];
    protected string $schema = '';
    protected string $comment = '';
    protected string $constraint = '';
    protected string $cols_key = '';
    protected string $ref_table = '';
    protected string $ref_key = '';
    protected string $key_on = '';
    protected string $on_delete = '';
    protected string $on_update = '';
    protected bool $drop = false;
    private static ?self $instance = null;


    public function __construct()
    {
       $this->table = "";
       $this->notExist = true;
    }

    public static function state():QueryState{
    return QueryState::active("Schema");
    }

    /**
     * Summary of schema
     * @param string $table
     * @return Schema
     */
    public function schema(string $table,bool $notExist = true): Schema
    {
        $clone = clone $this;
        $clone->table = $table;
        $clone->notExist = $notExist;
        $clone->state()->collect("TABLE",["table"=>$table,"notExist"=>$notExist]);
        return $clone;
    }

    /**
     * Summary of getColumns
     * @return array
     */
    public function getColumns(): array
    {
        return $this->column;
    }

    /**
     * Summary of id
     * @param string $colonne
     * @param mixed $n
     * @param string $comment
     * @return Schema
     */
    public function id(string $colonne, ?int $n = null, string $comment = ''): Schema
    {
        $clone = clone $this;
        $comment = !empty($comment) ? " COMMENT '{$comment}' " : '';
        $clone->column[] = ' ' . clean($colonne) . " INT($n) NOT NULL AUTO_INCREMENT PRIMARY KEY {$comment}";
        $clone->state()->collect("COLUMN",["type"=>"ID","colonnes"=>$colonne,"int_n"=>$n,"comment"=>$comment]);
        return $clone;
    }

    /**
     * Summary of int
     * @param string $colonne
     * @param int $n
     * @param mixed $null
     * @param mixed $default
     * @param string $comment
     * @return Schema
     */
    public function int(string $colonne, int $n, ?bool $null = false, ?string $default = '', string $comment = ''): Schema
    {
        $clone = clone $this;
        $null = $null ? 'NULL' : 'NOT NULL';
        $default = !empty($default) ? "DEFAULT '$default'" : '';
        $comment = !empty($comment) ? " COMMENT '{$comment}' " : '';
        $clone->state()->collect("COLUMN",["type"=>"INT","colonne"=>$colonne,"int_n"=>$n,"nullable"=>$null,"default"=>$default,"comment"=>$comment]);
        $clone->column[] = ' ' . clean($colonne) . " INT($n) {$null} {$default} {$comment}";
        return $clone;
    }

    /**
     * Summary of varchar
     * @param string $colonne
     * @param int $n
     * @param mixed $null
     * @param mixed $default
     * @param mixed $comment
     * @return Schema
     */
    public function varchar(string $colonne, int $n, ?bool $null = false, ?string $default = '', ?string $comment = ''): Schema
    {
        $clone = clone $this;
        $null = $null ? 'NULL' : 'NOT NULL';
        $default = !empty($default) ? "DEFAULT '$default'" : '';
        $clone->state()->collect("COLUMN",["type"=>"VARCHAR","colonne"=>$colonne,"int_n"=>$n,"nullable"=>$null,"default"=>$default,"comment"=>$comment]);
        $comment = !empty($comment) ? " COMMENT '{$comment}' " : '';
        $clone->column[] = ' ' . clean($colonne) . " VARCHAR($n) {$null} {$default} {$comment}";
        return $clone;
    }

    /**
     * Summary of text
     * @param string $colonne
     * @param mixed $null
     * @param mixed $default
     * @param string $comment
     * @return Schema
     */
    public function text(string $colonne, ?bool $null = false, ?string $default = '', string $comment = ''): Schema
    {
        $clone = clone $this;
        $null = $null ? ' NULL ' : ' NOT NULL';
        $clone->state()->collect("COLUMN",["type"=>"TEXT","colonne"=>$colonne,"nullable"=>$null,"default"=>$default,"comment"=>$comment]);
        $default = !empty($default) ? "DEFAULT '$default' " : '';

        $comment = !empty($comment) ? " COMMENT '{$comment}' " : '';

        $clone->column[] = ' ' . clean($colonne) . " TEXT {$null} {$default} {$comment}";

        return $clone;
    }

    /**
     * Summary of enum
     * @param string $colonne
     * @param array $enumerate
     * @param mixed $null
     * @param mixed $default
     * @param string $comment
     * @return Schema
     */
    public function enum(string $colonne, array $enumerate = [],
     ?bool $null = false, ?string $default = '', string $comment = ''): Schema
    {
        $clone = clone $this;
        $null = $null ? 'NULL' : 'NOT NULL';
        $default = !empty($default) ? "DEFAULT '$default' " : '';
        $comment = !empty($comment) ? " COMMENT '{$comment}' " : '';
        $clone->state()->collect("COLUMN",["type"=>"ENUM","colonne"=>$colonne,"enumerate"=>$enumerate,"nullable"=>$null,"default"=>$default,"comment"=>$comment]);
        if (!empty($enumerate)) {
            foreach ($enumerate ?? [] as $enum) {
                $enm[] = $enum ? implode(',', $enum) : [];
            }
            $clone->column[] = ' ' . clean($colonne) . ' ENUM(' . implode(',', $enm ?? []) . ") {$null} {$default} {$comment}";
        }
        return $clone;
    }

    /**
     * Summary of set
     *
     * @param string $colonne
     * @param mixed $set
     * @param mixed $null
     * @param mixed $default
     * @param string $comment
     * @return Schema
     */
    public function set(string $colonne, ?array $set = null,
     ?bool $null = false, ?string $default = '', string $comment): Schema
    {
        $clone = clone $this;
        $null = $null ? 'NULL' : 'NOT NULL';
        $default = !empty($default) ? "DEFAULT '$default' " : '';
        $comment = !empty($comment) ? " COMMENT '{$comment}' " : '';
        $clone->state()->collect("COLUMN",["type"=>"SET","colonne"=>$colonne,"set"=>$set,"nullable"=>$null,"default"=>$default,"comment"=>$comment]);
        if (!empty($set)) {
            $enm = [];
            foreach ($set as $enum) {
                $enm[] = " '{$enum}' ";
            }

            $clone->column[] = ' ' . clean($colonne) . ' SET (' . implode(',', $enm) . ") {$null} {$default} {$comment}";
        }
        return $clone;
    }

    /**
     * Summary of timesTamp
     * @param string $colonne
     * @param mixed $null
     * @param mixed $default
     * @param string $comment
     * @return Schema
     */
    public function timesTamp(string $colonne, ?bool $null = false, 
    ?string $default = 'CURRENT_TIMESTAMP', string $comment = ''): Schema
    {
        $clone = clone $this;
        $null = $null ? 'NULL' : 'NOT NULL';
        $default = !empty($default) ? "DEFAULT $default " : '';
        $comment = !empty($comment) ? " COMMENT '{$comment}' " : '';
        $clone->state()->collect("COLUMN",["type"=>"TIMESTAMP","colonne"=>$colonne,"nullable"=>$null,"default"=>$default,"comment"=>$comment]);
        $clone->column[] = ' ' . clean($colonne) . " TIMESTAMP {$null} {$default} {$comment}";
        return $clone;
    }

    /**
     * Summary of varcharArray
     * @param array $varchar
     * @param array $n
     * @param mixed $null
     * @param mixed $default
     * @return Schema
     */
    public function varcharArray(array $varchar, array $n, ?bool $null = false, ?array $default = []): Schema
    {
        $clone = clone $this;
        $null = $null ? 'NULL' : 'NOT NULL';
        foreach ($varchar as $k => $char) {
            $defString = !empty($default) ? "DEFAULT '{$default[$k]}' " : '';
            $nb = !empty($n) ? $n[$k] : 100;
            $clone->column[] = ' ' . clean($char) . " VARCHAR($nb) {$null} {$defString} ";
            $clone->state()->collect("COLUMN",["type"=>"ENUM","colonne"=>$char,"int_n"=>$nb,"nullable"=>$null,"default"=>$defString,"comment"=>""]);
        }

        return $clone;
    }

    /**
     * Summary of engine
     * @param string $engine
     * @return Schema
     */
    public function engine(string $engine): Schema
    {
        $clone = clone $this;
        $clone->engine .= $engine;
         $clone->state()->collect("ATTR",["type"=>"ENGINE","value"=>$engine]);
        return $clone;
    }

    /**
     * Summary of charset
     * @param string $charset
     * @return Schema
     */
    public function charset(string $charset): Schema
    {
        $clone = clone $this;
        $clone->charset = $charset;
         $clone->state()->collect("ATTR",["type"=>"CHARSET","value"=>$charset]);
        return $clone;
    }

    /**
     * Summary of collate
     * @param string $collate
     * @return object
     */
    public function collate(string $collate): Schema
    {
        $clone = clone $this;
        $clone->collate = $collate;
         $clone->state()->collect("ATTR",["type"=>"COLLATE","value"=>$collate]);
        return $clone;
    }

    /**
     * Summary of comment
     * @param string $comment
     * @return object
     */
    public function comment(string $comment): Schema
    {
        $clone = clone $this;
        $clone->comment = $comment;
        $clone->state()->collect("ATTR",["type"=>"COMMENT","value"=>$comment]);
        return $clone;
    }

    /**
     * Summary of buildSchema
     * @return string
     */
    private function buildSchema(): string
    {
        $not = $this->notExist ? " IF NOT EXISTS " : "";
        $this->schema = "CREATE TABLE {$not} {$this->table} ("; 
        $this->schema .= implode(',', $this->column);
        $this->schema .= ' ) ';
        $this->schema .= !empty($this->engine) ? " ENGINE = {$this->engine}  " : ' ENGINE = InnoDB ';
        $this->schema .= !empty($this->charset) ? " DEFAULT CHARSET = {$this->charset} " : ' DEFAULT CHARSET utf8mb4 ';
        $this->schema .= !empty($this->collate) ? " COLLATE = {$this->collate} " : ' COLLATE=utf8mb4_general_ci ';
        $this->schema .= !empty($this->comment) ? " COMMENT '{$this->comment}' " : '';

        if($this->drop){
             $this->schema = " DROP TABLE {$this->table} ";
        }
       
        return $this->schema;
    }


    /**
     * Summary of references
     * @param string $ref
     * @param string $key
     * @return Schema
     */
    public function references(string $ref, string $key): Schema
    {
        $this->ref_table = $ref;
        $this->ref_key = $key;
        $this->state()->collect("FOREIGN_KEY",["type"=>"reference",["ref"=>$ref,"key"=>$key]]);
        return $this;
    }

    /**
     * Summary of constraint
     * @param string $const
     * @return Schema
     */
    public function constraint(string $const = ""): Schema
    {
       $this->constraint = $const;
        $this->state()->collect("FOREIGN_KEY",["type"=>"constraint","constraint"=>$const]);
        return $this;
    }

    /**
     * Summary of onDelete
     * @param string $mode
     * @return Schema
     */
    public function onDelete(string $mode): Schema
    {
        $this->on_delete = !empty($mode)
            ? " ON DELETE {$mode} "
            : '';
        $this->state()->collect("FOREIGN_KEY",["type"=>"onDelete","mode"=>$mode]);
        return $this;
    }

    /**
     * Summary of onUpdate
     * @param string $mode
     * @return Schema
     */
    public function onUpdate(string $mode): Schema
    {
        $this->on_update = !empty($mode)
            ? " ON UPDATE {$mode} "
            : '';
        $this->state()->collect("FOREIGN_KEY",["type"=>"onUpdate","mode"=>$mode]);
        return $this;
    }

    /**
     * Summary of foreign_key
     * @param string $column
     * @param callable|string|Schema $callback
     * @return Schema
     */
    public function foreign_key(string $column, callable|string|Schema $callback): Schema
    {
        $clone = clone $this;

        if ($callback instanceof Schema) {
            $clone->column[] = $callback->buildForeignKey($column);
        } else
         if (is_callable($callback)) {
            $call = $callback($clone);

            $clone->column[] = $call instanceof Schema
                ? $call->buildForeignKey($column)
                : throw new \InvalidArgumentException('Error callback return Schema');
        } else 
        if (\is_string($callback)) {
            $clone->column[] = $callback;
        }

        return $clone;
    }

    /**
     * Summary of buildForeignKey
     * @param string $cols
     * @return string
     */
    public function buildForeignKey(string $cols): string
    {
        $this->cols_key = $cols;
        $constraint = !empty($this->constraint) ? $this->constraint : "fk_{$this->table}_{$this->ref_key}";

        $foreign  =  "CONSTRAINT {$constraint} ";
        $foreign .= " FOREIGN KEY ({$this->cols_key}) ";
        $foreign .= " REFERENCES {$this->ref_table}({$this->ref_key}) ";
        $foreign .= " {$this->on_delete} ";
        $foreign .= " {$this->on_update} ";

        return $foreign;
    }


    public function getSql():string{
        return $this->buildSchema();
    }

    public function getState(){
        return $this->state()->getState();
    }

    public function exec(){
        try{
                $create = $this->db()->create($this->getSql());
                $message = "";
           if($create){
               $message = [
                    "message"=>"successfull !"
                ];
            }

             return $message;

        }catch(PDOException $e){
            throw new RuntimeException($e->getMessage());
        }

    }


    public  function drop():Schema{
       $this->drop = true;
        return $this;
    }

}
