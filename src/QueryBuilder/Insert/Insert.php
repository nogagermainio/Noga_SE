<?php
namespace Src\QueryBuilder\Insert;

use finfo;
use PDOException;
use PDOStatement;
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
    private string $sql = "";
    private PDOStatement $stmt;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function table(string $table):static{
        return clone new static($table);
    }

    public function columns(string ...$columns):static
    {
        $clone = clone $this;
        $clone->columns = $columns;
        return $clone;
    }

    public function values(string|int|bool ...$values):static{
        $clone = clone $this;
        $clone->values = array_merge($clone->values,$values);
        return $clone;
    }

    private function binding():static{
        foreach($this->columns as $k => $cols){

            $keys = BindHashing::hash('in',$cols);

            if(\count($this->columns) !== \count($this->values)){
                throw new RuntimeException("Error Columns as different number ".
                implode(',',$this->columns).
                " of the values ".implode(',',$this->values)
            );

            }

            $this->binding[] = $keys;
            $this->params[$keys] = $this->values[$k];  
        }

        return $this;
    }

    public function from(string $file){
       
    }

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
    public function exc():string|bool{
        $this->stmt = $this->db()->execute($this->sql,$this->params);
         if(!$this->stmt){
            throw new PDOException("Erreur  : ".$this->stmt->errorInfo());
         }

         return $this->db()->lastId();
    }

    public function getSql():string{
        return $this->compile();
    }

    public function getParams():array{
        return $this->params;
    }

    public function debugSql():array{
        return [
            "sql"=>$this->compile(),
            "params"=>$this->params,
            "driver"=>$this->driver,
            "table"=>$this->table,
            "columns"=>$this->columns,
            "values"=>$this->values,
            "binding"=>$this->binding
        ];
    }


    // /**
    //  * Summary of bulkInsert
    //  * @param string $file fichier a inclur
    //  * @throws RuntimeException
    //  * @return array
    //  */
    // public function bulkInsert(string $table,string $file = ""):array
    // {
    //     $finfo = new finfo(\FILEINFO_MIME_TYPE);

    //     $mime = $finfo->file($file, \FILEINFO_MIME_TYPE);

    //     if (\file_exists($file)) {
    //         if ($mime !== 'application/json')
    //             throw new RuntimeException("Erreur le fichier n'est pas un json !");
            
    //     } else {
    //         throw new RuntimeException('Erreur fichier est introvable ! ');
    //     }

    //     $json = file_get_contents($file);
    //     $data = \json_decode($json, true);

    //     if (!\is_array($data)) {
    //         throw new RuntimeException('JSON invalide.');
    //     }

    //     // Supprimer les clés qu'on veut ignorer (comme "id")
    //     $champsAExclure = ['id', 'ID', 'cles', 'CLES'];  // tu peux en mettre plusieurs ici
    //     $colonnes = array_diff(array_keys($data[0]), $champsAExclure);
    //     $placeholders = implode(', ', array_fill(0, \count($colonnes), '?'));

    //     $this->insertSql = "INSERT INTO {$table} (" . implode(', ', $colonnes) . ") 
    //                         VALUES ($placeholders)";
    //     $allData = [];
    //     foreach ($data as $ligne) {
    //         $valeurs = [];
    //         foreach ($colonnes as $col) {
    //             $valeurs[] = $ligne[$col] ?? null;
    //         }

    //         $allData[] = $valeurs;
    //     }

    //     return [
    //         $this->insertSql,
    //         $allData
    //     ];
    // }


}
