<?php
namespace Noga\Tests\Units\Builder;
use PHPUnit\Framework\TestCase;

abstract class QueryTest extends TestCase
{
    protected function assertSqlEquals(string $expected, string $actual): void
    {
        $normalize = static function (string $sql): string {
            $sql = preg_replace('/\s+/', ' ', trim($sql));
            $sql = preg_replace('/\(\s+/', '(', $sql);
            $sql = preg_replace('/\s+\)/', ')', $sql);

            return $sql;
        };

        $this->assertSame(
            $normalize($expected),
            $normalize($actual)
        );
    }
}