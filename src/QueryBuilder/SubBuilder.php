<?php declare(strict_types=1);
namespace Src\QueryBuilder;
use Src\Sql;
class SubBuilder {
    protected string $sql = "";
    protected array $params = [];

    public function __construct() {
        $this->sql = "";
        $this->params = [];
    }

    public function tosub(callable|Sql|string $rows, string $alias = ""):static {
        if (is_string($rows)) {
            $this->sql = "$rows AS $alias";
        } else if(\is_callable($rows) || $rows instanceof Sql){
            $sub = $rows instanceof Sql ? $rows : $rows(new Sql());
            $this->sql .= "(". $sub->getSql() .") AS $alias";
            $this->params = $sub->getParams();

        }

        return $this;
    }

    public function compileSub():array {
        return [$this->sql,$this->params];
    }
}

