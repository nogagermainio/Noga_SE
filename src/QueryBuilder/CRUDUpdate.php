<?php
namespace Src\QueryBuilder;

/**
 * Summary of CRUDUpdate
 */
class CRUDUpdate
{
    protected string $UpdateSql = '';
    protected array $set        = [];
    protected array $params     = [];

    public function __construct()
    {
        $this->UpdateSql = '';
        $this->set       = [];
        $this->params    = [];
    }

    /**
     * Summary of set
     * @param array $cols colonne en table
     * @return static
     */
    public function set(array $cols = []): static
    {
        foreach ($cols ?? [] as $k => $v) {
            $key                       = $k ? \str_replace('.', '_', $k) : '';
            $this->set[]               = "$k = :set_$key";
            $this->params[":set_$key"] = $v;
        }
        return $this;
    }

    /**
     * Summary of updateData
     * @return array
     */
    public function updateData(string $table, array $condition): array
    {
        $this->UpdateSql = " UPDATE {$table} SET " . implode(',', $this->set) . '';
        $this->UpdateSql .= ' WHERE ' . implode(' AND ', $condition) . ' ';

        return [
            $this->set,
            $this->UpdateSql,
            $this->params,
        ];
    }
}
