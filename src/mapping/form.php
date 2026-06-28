<?php
namespace Src\Mapping;
use Src\Noga;

abstract class Form {
    protected array $attributes = [];
    protected static string $table;
    protected ?Noga $sql = null;

    public function __construct(array $data = []) { $this->attributes = $data; }
    public function __set(mixed $key, mixed $value) { $this->attributes[$key] = $value; }
    public function __get(mixed $key) { return $this->attributes[$key] ?? null; }

    // ORM SQL Builder instance
    /**
     * Summary of sql
     * @param string|callable|null $table
     * @return \Src\Sql
     */
    public static function sql(string|callable|null $table = null):\Src\Sql{
         return Noga::table($table ?? static::$table); 
        }

    public static function find(int $id) {
        $data = static::sql()->where(['id' => $id])->getOne();
        return $data ? new static((array)$data) : null;
    }

    // Lazy / eager hasMany
    public function hasMany(string $relatedForm,string $foreignKey,string $localKey = 'id',?callable $callback = null,bool $lazy = true) {
        $localValue = $this->$localKey;
        $query = Noga::table($relatedForm::$table)
                    ->where([$foreignKey => $localValue]);

        if ($callback) $query = $callback($query);

        if ($lazy) return $query; // lazy: retourne SQL builder
        $results = $query->get();
        return array_map(fn($row) => new $relatedForm((array)$row), $results);
    }

    // Lazy / eager belongsTo
    public function belongsTo(string $relatedForm,string $foreignKey,string $ownerKey = 'id',bool $lazy = true) {
        $foreignValue = $this->$foreignKey;
        $query = Noga::table($relatedForm::$table)
                    ->where([$ownerKey => $foreignValue]);

        if ($lazy) return $query; // lazy
        $data = $query->getOne();
        return $data ? new $relatedForm((array)$data) : null;
    }

    // withCount pour hasMany
    public function withCount(string $relationName,?string $alias = null,?callable $callback = null) {
        $relationQuery = $this->$relationName(...);
        if ($callback) $relationQuery = $callback($relationQuery);

        $alias ??= $relationName.'_count';
        $countSubquery = Noga::sub(fn($q) => $relationQuery->select(['COUNT(*)']), $alias);
        $this->attributes[$alias] = $countSubquery;
        return $this;
    }

    // aggregate sur relation
    public function aggregate(string $relationName,string $function,string $column,string $alias,?callable $callback = null) {
        $relationQuery = $this->$relationName(...);
        if ($callback) $relationQuery = $callback($relationQuery);

        $aggSubquery = Noga::sub(fn($q) => $relationQuery->select(["$function($column)"]), $alias);
        $this->attributes[$alias] = $aggSubquery;
        return $this;
    }

    // Save / Update
    public function save() {
        if (isset($this->attributes['id'])) {
            return static::sql()->where(['id' => $this->attributes['id']])
                       ->set_cols($this->attributes)
                       ->Update();
        }
        return static::sql()->Columns($this->attributes)->insert();
    }

    // CTE récursif ou standard avec collection
    public static function collectionWithCTE(
        string $cteName,
        callable $cteQuery,
        callable $mainQuery
    ) {
        $sql = Noga::table(fn() => $mainQuery(new Noga()), 'main')
                  ->with($cteName,  $cteQuery,true);
        return $sql;
    }
}
