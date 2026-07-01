# Changelog

All notable changes to Noga_SE will be documented in this file.

---

## [1.0.0] - 2026-07-01

### Added

- Fluent SQL Query Builder (SELECT, INSERT, UPDATE, DELETE)
- Immutable query architecture (safe cloning system)
- Automatic parameter binding (SQL injection protection)
- Secure BindHashing system (randomized parameter keys)
- JOIN support (INNER, LEFT, RIGHT, CROSS)
- Subqueries support (callables, QueryBuilder, raw SQL)
- Aggregations (GROUP BY, HAVING, COUNT, SUM, AVG, etc.)
- UNION / UNION ALL support
- CTE (Common Table Expressions) with recursion
- ORDER BY, LIMIT, OFFSET support
- Query caching system
- Debug mode via `viewState()`
- Query inspection (`getQuery`, `getParams`)
- Multi-driver architecture (MySQL, PostgreSQL, SQLite ready)

### Security

- Fully parameterized queries
- No direct SQL concatenation
- Protected dynamic bindings
- Safe immutability layer

### Infrastructure

- PSR-4 autoloading (Noga\ namespace)
- Composer-ready package
- PHPUnit test structure initialized
- CLI entry point (basic)

---

## [Unreleased]

- Query profiler
- Migration system
- Schema builder
- Performance benchmark tools