<?php declare(strict_types=1);
namespace Src\QueryBuilder;

use Src\Sql;
class BuildeSub {
    protected string $sql = "";
    protected array $params = [];
    protected mixed $state = null;

    public function __construct() {
        $this->sql = "";
        $this->params = [];
    }

    public function tosub(callable|Sql|string $rows, string $alias = ""):static {
        if (is_string($rows)) {
            $this->sql = "$rows AS $alias";
            $this->state[] = $this->sql; 
        } else if(\is_callable($rows) || $rows instanceof Sql){

            $sub = $rows instanceof Sql ? $rows : $rows(new Sql());
            $this->sql .= "(". $sub->getSql() .") AS $alias";
            $sub->create_state("tmp_sub");
            $this->state[] = $sub->getState();

            $this->params = $sub->getParams();

        }

        return $this;
    }

    public function compileSub():array {
        return [$this->sql,$this->params,$this->state];
    }
}

