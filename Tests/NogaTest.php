<?php
namespace Noga\Tests;
use PHPUnit\Framework\TestCase;
use Noga\Noga;

final class NogaTest extends TestCase
{

    protected function assertSqlContains(string $needle,mixed $builder)
    {
        $sql = $builder->getSql();
        $this->assertStringContainsString($needle, $this->normalizeSql($sql));
    }

protected function normalizeSql(string $sql): string
{
    // supprime les espaces et retours à la ligne multiples
    $sql = preg_replace('/\s+/', ' ', trim($sql));

    // normalise les placeholders dynamiques
    $sql = preg_replace('/:wh_[a-f0-9]+(_[a-z]+)?/', ':wh_', $sql);

    return $sql;
}


    public function testBasicSelect()
    {
        $builder = Noga::table('product','p')->select(['id','name']);

        $this->assertSqlContains('SELECT id,name FROM product AS p', $builder);
    }

    public function testWhereClause()
    {
        $builder = Noga::table('product','p')->select(['id','name']);
        $builder2 = $builder->where(['id'=>10]);

        // Immutabilité : builder d'origine intact
        $this->assertStringNotContainsString('WHERE', $builder->getQuery());
        $this->assertStringContainsString('WHERE id =', $builder2->getQuery());
    }

    public function testGroupByAndSum()
    {
        $builder = Noga::table('product','p')
            ->select(['id', Noga::sum('id','total_id')])
            ->groupBy(['id']);

        $this->assertSqlContains('GROUP BY id', $builder);
        $this->assertSqlContains('SUM(id) AS total_id', $builder);
    }

   public function testRecursiveCTE()
{
    $builder = Noga::with(
        "categories",
        Noga::table("categories", "c")
            ->select(["id", "parent_id", "name"])
            ->unionAll(fn($u) => 
                $u->from(["categories"])
                  ->select(["id", "parent_id", "name"])
            )
    ,true)
    ->table("products", "p")
    ->select(["id", "name", "category_id"])
      ->where(["active" => 1]);

    // SQL attendu (placeholders normalisés)
    $expected = <<<SQL
WITH RECURSIVE categories AS (
    SELECT id,parent_id,name FROM categories AS c
    UNION ALL
    SELECT id,parent_id,name FROM categories
)
SELECT id,name,category_id FROM products AS p WHERE active = :wh_
SQL;
$sql = $this->normalizeSql($builder->getQuery());
$expectedNormalized = $this->normalizeSql($expected);

$this->assertStringContainsString($expectedNormalized, $sql);

    // Immutabilité : builder original intact
    $builder2 = $builder->where(["id"=>10]);
    $this->assertStringNotContainsString("id =", $this->normalizeSql($builder->getQuery()));
    $this->assertStringContainsString("id =", $this->normalizeSql($builder2->getQuery()));
}


    public function testIsNullMethod()
    {
        $builder = Noga::table('product','p')->select(['id','name']);
        $builder2 = $builder->isNull('name');

        $this->assertStringNotContainsString('IS NULL', $builder->getQuery());
        $this->assertStringContainsString('name IS NULL', $builder2->getQuery());
    }

    public function testInnerJoin()
    {
        $builder = Noga::table('product','p')
            ->innerJoin(
                Noga::joins('table','t')->on('t.id','12')
            );

        $this->assertStringContainsString('INNER JOIN table AS t ON t.id = 12', $builder->getQuery());
    }


}
