<?php declare(strict_types=1);
namespace Noga\Db;

use Noga\Db\Db;
class Mysql extends Db{
    public function __construct(protected string $database = '')
    {
            $this->driver = ng('MY_DRIVER') ?? 'mysql';
            $this->host = ng('MY_HOST') ?? '127.0.0.1';
            $this->port = ng('MY_PORT') ?: 3306;
            $this->database = $this->database ?: ng('MY_DATABASE');
            $this->charset = ng('MY_CHARSET') ?? 'utf8mb4';
            $this->set_session = ng('MY_SET_SESSION') ?? "SET SESSION sql_mode=''";

    }

    protected function getDsn(): string
    {
        return sprintf(
            "%s:host=%s;port=%d;dbname=%s;charset=%s",
            $this->driver,
            $this->host,
            $this->port,
            $this->database,
            $this->charset
        );
    }

    protected function getUsername(): string
    {
        return ng('MY_USERNAME') ?? 'root';
    }

    protected function getPassword(): string
    {
        return ng('MY_PASSWORD') ?? '';
    }

    protected function getOptions(): array
    {
        return ng('MY_OPTIONS') ?: $this->options;
    }


    /**
     * Summary of connect
     * @return \PDO|null
     */
    public function connect():\PDO|null
    {
        return parent::connect();
    }
    /**
     * Summary of disconnect
     * @return null
     */
    public function disconnect():null
    {
        return parent::disconnect();
    }

}