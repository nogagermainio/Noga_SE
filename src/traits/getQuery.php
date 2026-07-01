<?php
namespace Noga\Traits;

use Generator;

trait GetQuery{
    use DbTrait;
    
    public function getSql(): string
    {
        return (! empty($this->request) &&
            isset($this->request['sql'])) ?
        $this->request['sql'] :
        $this->compiler();
    }

    /**
     * Summary of getParams
     * @return array
     */
    public function getParams(): array
    {
        return (! empty($this->request) &&
            ! empty($this->request['params'])) ?
        $this->request['params'] :
        $this->params;
    }

    public function getDriver(): string
    {
        return (! empty($this->request) &&
            ! empty($this->request['driver'])) ?
        $this->request['driver'] :
        $this->driver;
    }

    /**
     * Summary of getTable
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Summary of getColumn
     * @return array
     */
    public function getColumn(): array
    {
        return $this->cols;
    }

    public function cteDebug(): array
    {
        return $this->cte;
    }

    /**
     * Summary of get
     * @param int $fetchMode
     * @return array
     */
    public function get(int $fetchMode = \PDO::FETCH_OBJ): array
    {
        return $this->db()->All(
            $this->getSql(),
            $this->getParams(),
            $fetchMode
        );
    }

    /**
     * Summary of getStream
     * @param int $fetchMode
     * @return Generator
     */
    public function getStream(int $fetchMode = \PDO::FETCH_OBJ): Generator
    {
        return $this->db()->stream(
            $this->getSql(),
            $this->getParams(),
            $fetchMode
        );
    }

    /**
     * Summary of getOne
     * @param int $fetchMode
     */
    public function getOne(int $fetchMode = \PDO::FETCH_OBJ)
    {
        return $this->db()->One(
            $this->getSql(),
            $this->getParams(), $fetchMode
        );
    }
}