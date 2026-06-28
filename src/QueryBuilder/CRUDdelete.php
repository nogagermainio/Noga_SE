<?php
namespace Src\QueryBuilder;
use RuntimeException;

class CRUDdelete
{
    protected string $DeleteSql = '';
    private bool $cascade = false;

    public function __construct()
    {
        $this->DeleteSql = '';
    }

    public function DeleteData(string $table, array $condition):string
    {
        $this->DeleteSql = "DELETE FROM {$table} ";
        if (!empty($condition)) {
            $this->DeleteSql .= ' WHERE ' . implode(' AND ', $condition) . ' ';
        } else {
            throw new RuntimeException('Erreur => parametre clause where est obligatoire pour la suppression ! ');
        }

        return $this->DeleteSql;
    }
}
