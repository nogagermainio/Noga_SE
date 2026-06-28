<?php declare(strict_types=1);
namespace Src\Db;

use PDO;
use Src\Db\DB;

class Sqlite extends DB
{
    public function __construct(protected string $database = '')
    {
        $this->driver   = 'sqlite';
        $this->database = $this->database ?: ng('Lite_db');

        // chemin ABSOLU obligatoire App/Models/QueryConstruct/database/SqlLite/Sqlite.db
        $this->database = __DIR__.'/../database/SqlLite/'.$this->database ?: $this->database;

        $this->set_session = "PRAGMA foreign_keys = ON";
    }

    protected function getDsn(): string
    {
        return "sqlite:" . $this->database;
    }

    protected function getUsername(): string { return ''; }
    protected function getPassword(): string { return ''; }

    protected function getOptions(): array
    {
        return ng('Lite_options') ?: [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
}
