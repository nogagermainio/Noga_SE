<?php declare(strict_types=1);
namespace Noga\Traits;

use PDOStatement;
use RuntimeException;
use Noga\Core\CacheManager;
use Noga\Db\Db;
use Noga\Db\MySQL;
use Noga\Db\Postgres;
use Noga\Db\Sqlite;


trait DbTrait{
    protected string $driver = "mysql";
    protected PDOStatement $stmt; 
    protected const DRIVER   = [
        "mysql",
        "sqlite",
        "pgsql",
    ];

      /**
     * Summary of db
     * @var Db
     */
    private ?Db $db = null;

    private string $cacheDir = "sql";


     /**
     * Summary of driver
     * @param string $driver
     * @param string $database
     * @return static
     */
    public function driver(string $driver, string $database = ""):static
    {
        $clone         = clone $this;
        $clone->driver = $driver;

        if (! \in_array($driver, self::DRIVER)) {
            throw new RuntimeException("error your driver {$driver} is not supported ! ");
        }

        $clone->driver = strtolower($clone->driver);

        if ($clone->driver === "mysql") {
            $clone->db = new MySQL($database);
            return $clone;

        } else if ($clone->driver === "sqlite") {

            $clone->db = new Sqlite($database);
            return $clone;
        } else if ($clone->driver === "pgsql") {
            $clone->db = new Postgres($database);
        }

        return $clone;
    }

    /**
     * Summary of db
     * @return Db|null
     */
    private function db(): ?Db
    {
        if ($this->db === null) {
            $this->db = new MySQL();
        }
        return $this->db;
    }


     private function cache(string $key):CacheManager{
        return CacheManager::key($key)->dir($this->cacheDir);
    }
}