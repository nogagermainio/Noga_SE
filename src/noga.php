<?php
namespace Src;

use Src\Facade\Design;
use Src\Schema\Alter;
use Src\Schema\Schema;
use Src\QueryBuilder\UnionBuilder;
use Src\Sql;

/**
 * Summary of Noga design pattern static 
 * 
 * @method static Sql table(string|Sql|callable $table, ?string $alias = '')
 * @method static Sql use_req(string $key)
 * @method static Sql create_req(string $key)
 * @method static Sql with(string $cte, Sql|callable $callback,?bool $recursive = false)
 * @method static Sql sub(callable|Sql|string $subquery, string $alias)
 * @method static Sql driver(string $driver)
 * @method static Sql explain(callable|Sql|string $explain, string $mode = '')
 * @method static Sql schema(string $table,callable|Sql|string $schema)
 * @method static Sql id(string $colonne, ?int $n = null)
 * @method static Sql constraint(string $constraint)
 * @method static Sql foreign_key(string $column, callable|string|Sql $callback)
 * @method static Sql alterTable(string $table,callable|Sql|string $callback)
 * @method static Sql add(string|array $column,string|array $type,bool $nullable = false,string|array $default = "",string $comment = "")
 * @method static Sql addIndex(string $index,array|string $columns)
 * @method static Sql addPrimary(string|array $primary_key)
 * @method static Sql addUnique(string $index,string $columns)
 * @method static Sql addFullText(string $index,string $column)
 * @method static Sql addSpatial(string $index,string $column)
 * @method static Sql addForeign($cols,callable|Sql $callback)
 * @method static Sql change(string|array $column,string|array $newColumn,string $type,string $character = "",string $collete = "",bool $nullable = false,string|array $default = "",string $comment = "")
 * @method static Sql modify(string $column,array|string $type,string $comment = "")
 * @method static Sql setDefault(string $column,string $values)
 * @method static Sql drop(string $column)
 * @method static Sql dropDefault(string $column)
 * @method static Sql tableEngine(string $engine)
 * @method static Sql tableCharset(string $character)
 * @method static Sql tableRename(string $new_name)
 * @method static Sql tableComment(string $comment)
 * @method static Sql columnOrder(string $column,string $after)
 * @method static Sql columnToFirst(string $column)
 * @method static Sql columnToLast(string $column)
 * @method  static Schema schema(string $table,bool $notExist = true)
 * @method static UnionBuilder u()
 * @mixin Sql
 */

final class Noga
{
    // public function registreInstanceClass(): string
    // {
    //     return Sql::class;
    // } 

    public static function Schema(string $table,bool $notExist = true):Schema{
        return (new Schema())->schema($table,$notExist);
    }

    public static function table(string $table,?string $alias = ""):Sql{
        return (new Sql())->table($table,$alias);
    }

    public static function with(string $cte, Sql|callable $callback, ?bool $recursive = false):Sql{
        return (new Sql())->with($cte,$callback,$recursive);
    }

    public static function union():UnionBuilder{
        return (new UnionBuilder());
    }

    public static function use_req(string $key){
        return (new Sql())->use_req($key);
    }

    public static function removeCache(string $key):?string{
        return Sql::removeCache($key);
    }
  
}
