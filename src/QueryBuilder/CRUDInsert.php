<?php
namespace Src\QueryBuilder;

use finfo;
use RuntimeException;

class CRUDInsert
{
    public string $insertSql;
    public array $cols = [];
    public array $values = [];
    public array $params = [];

    public function __construct()
    {
        $this->insertSql = '';
        $this->cols = [];
        $this->values = [];
        $this->params = [];
    }

    public function colonnes(array $cols = []):static
    {
        foreach ($cols ?? [] as $k => $v) {
            $this->cols[] = $k;
            $this->values[] = ":$k";
            $this->params[":$k"] = $v;
        }
        return $this;
    }

    public function insertData(string $table):array
    {
        $this->insertSql = "INSERT INTO {$table}(" . implode(',', $this->cols) . ')';
        $this->insertSql .= ' VALUES(' . implode(',', $this->values) . ')';

        return [
            $this->insertSql,
            $this->params
        ];
    }

    /**
     * Summary of bulkInsert
     * @param string $file fichier a inclur
     * @throws RuntimeException
     * @return array
     */
    public function bulkInsert(string $file = "", string $table):array
    {
        $finfo = new finfo(\FILEINFO_MIME_TYPE);

        $mime = $finfo->file($file, \FILEINFO_MIME_TYPE);

        if (\file_exists($file)) {
            if ($mime !== 'application/json')
                throw new RuntimeException("Erreur le fichier n'est pas un json !");
            
        } else {
            throw new RuntimeException('Erreur fichier est introvable ! ');
        }

        $json = file_get_contents($file);
        $data = \json_decode($json, true);

        if (!\is_array($data)) {
            throw new RuntimeException('JSON invalide.');
        }

        // Supprimer les clés qu'on veut ignorer (comme "id")
        $champsAExclure = ['id', 'ID', 'cles', 'CLES'];  // tu peux en mettre plusieurs ici
        $colonnes = array_diff(array_keys($data[0]), $champsAExclure);
        $placeholders = implode(', ', array_fill(0, \count($colonnes), '?'));

        $this->insertSql = "INSERT INTO {$table} (" . implode(', ', $colonnes) . ") 
                            VALUES ($placeholders)";
        $allData = [];
        foreach ($data as $ligne) {
            $valeurs = [];
            foreach ($colonnes as $col) {
                $valeurs[] = $ligne[$col] ?? null;
            }

            $allData[] = $valeurs;
        }

        return [
            $this->insertSql,
            $allData
        ];
    }
}
