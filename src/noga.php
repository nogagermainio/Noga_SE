<?php
namespace Src;

use Src\End\Sql;
use Src\Facade\Facade;
use Src\QueryBuilder\Crud\Delete\Delete;
use Src\QueryBuilder\Crud\Insert\Insert;
use Src\QueryBuilder\Crud\Update\Update;
use Src\QueryBuilder\Select\Select;

/**
 * Summary of Noga design pattern static 
 * @method static Select table(string|callable $table,string $alias = '')
 * @method static Insert insert(string $table)
 * @method static Update update(string $table)
 * @method static Delete delete(string $table)
 * @method static Select with(string $cte, Select | callable $callback, ?bool $recursive = false)
 * @method static Select explain(callable | Select | string $explain, string $mode = '')
 * @mixin Select
 */
final class Noga extends Facade
{
    public function getProcessClass(): array
    {
        return [
           Select::class
        ];
    } 

  
  
}
