<?php
namespace Src\Db;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

abstract class Db{
    private static ?PDO $pdo = null;
    protected array $instanceDb = [];
    protected string $host;
    protected ?int $port;
    protected string $username;
    protected string $password;
    protected string $database;
    protected string $charset = "utf8_mb4";
    protected string $driver;
    protected string $collation = "utf8mb4_general_ci";
    protected string $set_session = "SET SESSION sql_mode=''";
    protected array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
       ];

    public function __construct(){}

    /**
     * Summary of connect 
     * centrale connexion method that 
     * establish a connection to the database using PDO and returns the PDO instance.
     *  It checks if a connection already exists and reuses it if available, 
     * otherwise it creates a new connection using the provided configuration parameters.
     *  It also handles any exceptions that may occur during the connection process and
     *  throws a RuntimeException with an appropriate error message.
     * @throws RuntimeException
     * @return PDO|null
     */

  public function connect(){
    if(!static::$pdo instanceof PDO){
        try {
            static::$pdo = new PDO(
                $this->getDsn(), 
            $this->getUsername(),
                $this->getPassword(),
                $this->getOptions()
            );
            static::$pdo->exec($this->set_session);

        } catch(PDOException $e) {
            throw new RuntimeException("error connexion : ".$e->getMessage());
        }
    }
    return static::$pdo;
}

    /**
     * Summary of disconnect
     * 
     * this is a method that is responsible for closing the database connection by setting 
     * the static property $pdo to null.
     * 
     * @return null
     */
    public function disconnect(){
       return static::$pdo = null;
    }
    /**
     * Summary of fais
     * this method is responsible for executing a SQL query with optional parameters.
     * It prepares the SQL statement using the PDO connection, executes it with the provided parameters,
     * and returns the resulting PDOStatement object. If any exceptions occur during the execution of the query,
     * it catches the PDOException and throws a RuntimeException with an appropriate error message.
     * @param string $sql
     * @param array $params
     * @throws RuntimeException
     * @return bool|\PDOStatement
     */
    public function execute(string $sql,array $params = []){
       try{

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
         return $stmt;

       }catch(PDOException $e){
        throw new RuntimeException("Erreur lors de l'exécution de la requête : ".$e->getMessage());
       }
       
    }
    /**
     * Summary of One
     * this method is responsible for executing a SQL query and fetching a single result.
     * It takes a SQL query as a string, an optional array of parameters for the query,
     *  and an optional fetch mode (defaulting to PDO::FETCH_OBJ).
     * The method prepares the SQL statement using the PDO connection, 
     * executes it with the provided parameters, and returns the fetched result based on the specified fetch mode. 
     * If any exceptions occur during the execution of the query, it catches the PDOException and 
     * throws a RuntimeException with an appropriate error message.
     * @param string $sql
     * @param array $params
     * @param int $fetchMode
     */
    public function One(string $sql,array $params = [],int $fetchMode = PDO::FETCH_OBJ){
        $stmt = $this->execute($sql,$params);
        return $stmt->fetch($fetchMode);
    }

    /**
     * Summary of All
     * this method is responsible for executing a SQL query and fetching all results.
     * It takes a SQL query as a string, an optional array of parameters for the query,
     *  and an optional fetch mode (defaulting to PDO::FETCH_OBJ).
     * The method prepares the SQL statement using the PDO connection, 
     * executes it with the provided parameters, and returns all fetched results based on the specified fetch mode.
     * If any exceptions occur during the execution of the query, 
     * it catches the PDOException and throws a RuntimeException with an appropriate error message.
     * @param string $sql
     * @param array $params
     * @param int $fetchMode
     * @return array
     */
   public function All(string $sql, array $params = [],int $fetchMode = PDO::FETCH_OBJ) {

    $stmt = $this->execute($sql, $params);

    return $stmt->fetchAll($fetchMode);
}

/**
 * Summary of stream
 * this method is responsible for executing a SQL query and streaming the results one by one.
 * It takes a SQL query as a string, an optional array of parameters for the query, 
 * and an optional fetch mode (defaulting to PDO::FETCH_OBJ).
 * @param string $sql
 * @param array $params
 * @param int $fetchMode
 * @return \Generator
 */
public function stream(string $sql, array $params = [],int $fetchMode = PDO::FETCH_OBJ){
     $stmt = $this->execute($sql, $params);
       while ($row = $stmt->fetch($fetchMode)) {
        yield $row;
    }
}
 
    /**
     * Summary of lastId
     * this method is responsible for retrieving the last inserted ID from the database.
     * @return bool|string
     */
    public function lastId(){
        return $this->connect()->lastInsertId();
    }

    /**
     * Summary of create
     * this method is responsible for executing a SQL query without parameters and 
     * returning the resulting PDOStatement object.
     * It takes a SQL query as a string, prepares it using the PDO connection, executes it,
     *  and returns the resulting PDOStatement object. 
     * If any exceptions occur during the execution of the query, 
     * it catches the PDOException and throws a RuntimeException with an appropriate error message.
     * @throws RuntimeException
     * @param string $sql
     * @return bool|\PDOStatement
     */
    public function create(string $sql){
        try{
            
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute();
            return $stmt;

        }catch(PDOException $e){
            throw new RuntimeException($e->getMessage());
        }
       
    }

    /**
     * Summary of transaction
     * this method is responsible for executing a series of database operations within a transaction.
     * It takes a callable as an argument, which represents the operations to be performed within the transaction.
     * The method begins a transaction using the PDO connection, executes the provided callback function,
     * and commits the transaction if all operations are successful. 
     *If any exceptions occur during the execution of the callback,
     * it rolls back the transaction and throws a RuntimeException with an appropriate error message.
     * @param callable $callback 
     * @throws RuntimeException
     * @return void
     */
    public function totransaction(callable $callback){
        $this->connect()->beginTransaction();
        try{

            \call_user_func($callback,$this);

            $this->connect()->commit();
        }catch(Throwable $e){
            $this->connect()->rollBack();
            throw new RuntimeException("Erreur de transaction : ".$e->getMessage());
        }
    }

    /**
     * Summary of getDsn
     * this method is responsible for constructing and returning the Data Source Name (DSN) string used 
     * for connecting to the database.
     * The DSN string typically includes the database driver, host, port, database name, 
     * charset, and other relevant connection parameters.
     * The specific format of the DSN string may vary depending on the database driver being used (e.g., MySQL, SQLite).
     * This method is abstract and must be implemented by subclasses to provide 
     * the appropriate DSN string based on their specific configuration.
     * @throws RuntimeException
     * @return string
     */
    abstract protected function getDsn():string;

    /**
     * Summary of getUsername
     * this method is responsible for construct and returning the usersName in mysql and SQLite
     * @return string
     */
    abstract protected function getUsername():string;
    abstract protected function getPassword():string;
    abstract protected function getOptions():array;
}