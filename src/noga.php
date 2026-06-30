<?php
namespace Src;

use Src\Facade\Facade;
use Src\Sql;

/**
 * Summary of Noga design pattern static 
 * 
 * @method static Sql table(string|Sql|callable $table, ?string $alias = '')
 * @method static Sql use_query(string $key)
 * @method static Sql add_query(string $key)
 * @method static Sql with(string $cte, Sql|callable $callback,?bool $recursive = false)
 * @method static Sql sub(callable|Sql|string $subquery, string $alias)
 * @method static Sql driver(string $driver)
 * @method static Sql explain(callable|Sql|string $explain, string $mode = '')
 * @mixin Sql
 */

final class Noga extends Facade
{
    public function getProcessClass(): array
    {
        return [Sql::class];
    } 

  
  
}
