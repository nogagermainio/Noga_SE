<?php declare(strict_types=1);
namespace Src\Db;

use Src\Db\DB;

class Postgres extends DB
{
    public function __construct(protected string $database = '')
    {
        $this->driver   = ng('PG_DRIVER') ?? 'pgsql';
        $this->host     = ng('PG_HOST') ?? 'localhost';
        $this->port     = ng('PG_PORT') ?: 5432;
        $this->database = $this->database ?: ng('PG_DATABASE');
        $this->charset  = ng('PG_CHARSET') ?? 'UTF8';
        // PostgreSQL-safe session
        $this->set_session = "SET client_encoding = 'UTF8'";
    }

    protected function getDsn(): string
    {
        return sprintf(
            "%s:host=%s;port=%d;dbname=%s",
            $this->driver,
            $this->host,
            $this->port,
            $this->database
        );
    }

    protected function getUsername(): string
    {
        return ng('PG_USERNAME') ?? 'postgres';
    }

    protected function getPassword(): string
    {
        return ng('PG_PASSWORD') ?? '';
    }

    protected function getOptions(): array
    {
        return ng('PG_OPTIONS') ?: $this->options;
    }

    
}
