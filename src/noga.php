<?php
namespace Noga;

use Noga\Facade\Facade;
use Noga\QueryBuilder\Crud\Delete\Delete;
use Noga\QueryBuilder\Crud\Insert\Insert;
use Noga\QueryBuilder\Crud\Update\Update;
use Noga\QueryBuilder\Select\Select;

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
     private static array $config = [
        'base_path' => '',
        'cache_path' => '',
        'driver' => 'mysql',
    ];

 /**
  * Summary of config
  * @param string $basePath `__DIR__`
  * @param string $cachePath 
  * @param string $driver 
  * @return void
  */
 public static function config(
        string $basePath,
        string $cachePath,
        string $driver = 'mysql'
    ): void {
        self::$config = [
            'base_path' => rtrim($basePath, DIRECTORY_SEPARATOR),
            'cache_path' => rtrim($cachePath, DIRECTORY_SEPARATOR),
            'driver'     => strtolower($driver),
        ];
    }

    /**
     * Summary of get
     * @var array{base_path:string,cache_path:string,driver:string}
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): ?string
    {
       
        return self::$config[$key] ?? null;
    }

  public function getProcessClass(): array
    {
        return [
           Select::class
        ];
    } 
  
}
