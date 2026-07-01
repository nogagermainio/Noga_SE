<?php declare(strict_types=1);
namespace Noga\QueryBuilder\Select\Sub;

use Noga\QueryBuilder\Select\Select;

class Sub {
    protected string $sql = "";
    protected array $params = [];

    public function __construct() {
        $this->sql = "";
        $this->params = [];
    }

    public function tosub(callable|Select|string $rows, string $alias = ""):static {
        if (\is_string($rows)) {
            $this->sql = "$rows AS $alias";
        } else if(\is_callable($rows) || $rows instanceof Select){

            $sub = $rows instanceof Select ? $rows : $rows(new Select());
            $this->sql .= "(". $sub->getQuery() .") AS $alias";

            $this->params = $sub->getParams();

        }

        return $this;
    }

    public function compileSub():array {
        return [$this->sql,$this->params];
    }
}

