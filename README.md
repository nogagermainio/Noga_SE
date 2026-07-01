# 🗄️ Noga_SE - Modern SQL QueryBuilder

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1+-green.svg)](https://php.net)
[![Composer](https://img.shields.io/badge/Composer-Ready-brightgreen.svg)](https://packagist.org)

**Noga_SE** is a modern, fluent, and immutable SQL QueryBuilder built in PHP 8.1+. It provides an elegant and secure API for building SELECT, INSERT, UPDATE, and DELETE queries with automatic parameter binding to prevent SQL injections.

---

## ✨ Key Features

### 🔧 Complete CRUD Operations
- **SELECT** - Complex queries with joins, subqueries, aggregations, CTEs
- **INSERT** - Single and batch insertions with secure binding
- **UPDATE** - Safe updates with WHERE conditions
- **DELETE** - Protected deletions with conditions

### 🛡️ Advanced Security
- **Parameter Binding** - Automatic binding prevents SQL injections
- **BindHashing** - Cryptographically random parameter keys (`:prefix_hexrand_colname`)
- **Immutability** - Automatic cloning prevents mutations
- **Type Safety** - Strict type checking with exceptions

### ⚙️ Powerful Features
- **Complex WHERE Clauses** - AND, OR, LIKE, BETWEEN, IN, EXISTS, NOT IN
- **Joins** - INNER, LEFT, RIGHT, CROSS joins with multiple tables
- **Subqueries** - Nested queries via callables, Select instances, or strings
- **Aggregations** - GROUP BY, HAVING, COUNT, MAX, MIN, SUM, AVG
- **Unions** - UNION, UNION ALL for combining results
- **CTEs** - Common Table Expressions with recursive support
- **Sorting & Pagination** - ORDER BY, GROUP BY, LIMIT, OFFSET
- **Query Caching** - Reuse compiled queries efficiently

### 🔗 Fluent API
```php
$query = Noga::table('users')
    ->select('id', 'name', 'email')
    ->where(['status' => 'active'])
    ->orderBy('created_at', 'DESC')
    ->limit(10);
```

### 🎨 Design Patterns
- **Facade Pattern** - Unified static API
- **Builder Pattern** - Chainable query construction
- **Immutable Pattern** - Safe object cloning
- **Singleton Pattern** - Single instances for managers
- **Traits** - Reusable functionality (conditions, aggregations)

---

## 📦 Installation

### Via Composer
```bash
composer require nogagermainio/noga-se
```

### Manual Installation
1. Clone the repository
2. Configure autoloading in `composer.json`:
```json
{
  "autoload": {
    "psr-4": {
      "Src\\": "src/"
    }
  }
}
```

---

## 🚀 Quick Start Guide

### 1️⃣ SELECT - Reading Data

#### Basic Query
```php
use Src\Noga;

$users = Noga::table('users')
    ->select('id', 'name', 'email')
    ->get();
```

#### With Conditions
```php
$activeUsers = Noga::table('users')
    ->select('*')
    ->where(['status' => 'active', 'age >=' => 18])
    ->get();
```

#### With Joins
```php
$userPosts = Noga::table('users')
    ->select('users.name', 'posts.title')
    ->innerJoin(Noga::joins('posts', 'p')
        ->on('users.id', '=', 'p.user_id'))
    ->get();
```

#### With Subqueries
```php
$topUsers = Noga::table('users')
    ->select('id', 'name')
    ->whereIn('id', fn($q) => 
        $q->table('orders')
            ->select('user_id')
            ->where(['status' => 'completed'])
    )
    ->get();
```

#### With Aggregations
```php
$stats = Noga::table('orders')
    ->select('user_id', 'COUNT(*) as total_orders')
    ->groupBy(['user_id'])
    ->having(['total_orders >' => 5])
    ->get();
```

#### With Sorting & Pagination
```php
$topUsers = Noga::table('users')
    ->select('*')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->offset(20)
    ->get();
```

### 2️⃣ INSERT - Creating Data

#### Single Insertion
```php
use Src\Noga;

$result = Noga::insert('users')
    ->columns('name', 'email', 'status')
    ->values('John Doe', 'john@example.com', 'active')
    ->exc();
```

#### Multiple Insertions
```php
$result = Noga::insert('users')
    ->columns('name', 'email', 'status')
    ->values('Alice', 'alice@example.com', 'active')
    ->values('Bob', 'bob@example.com', 'inactive')
    ->exc();
```

#### Debug Insertion
```php
$debug = Noga::insert('users')
    ->columns('name', 'email')
    ->values('Test', 'test@example.com')
    ->debugSql();

// Output:
// [
//     "sql" => "INSERT INTO users( name,email ) VALUES(:in_xxxxx,:in_yyyyy)",
//     "params" => [":in_xxxxx" => "Test", ":in_yyyyy" => "test@example.com"],
//     "binding" => [":in_xxxxx", ":in_yyyyy"]
// ]
```

### 3️⃣ UPDATE - Modifying Data

#### Simple Update
```php
$result = Noga::table('users')
    ->set_cols(['status' => 'active', 'updated_at' => 'NOW()'])
    ->where(['id' => 5])
    ->update();
```

#### Update with Complex Conditions
```php
$result = Noga::table('users')
    ->set_cols(['verified' => true])
    ->where([
        'email' => 'test@example.com',
        'status' => 'inactive'
    ])
    ->update();
```

### 4️⃣ DELETE - Removing Data

#### Simple Deletion
```php
$result = Noga::table('users')
    ->where(['id' => 1])
    ->delete();
```

#### Safe Deletion with Limits
```php
$result = Noga::table('users')
    ->where(['status' => 'inactive', 'last_login <' => '2023-01-01'])
    ->limit(100)
    ->delete();
```

---

## 📚 Complete API Reference

### SELECT Methods

#### Column Selection
```php
->select('id', 'name', 'email')           // Specific columns
->select('*')                             // All columns
->distinct(true)                          // Remove duplicates
->selectCase(fn($case) => ..., 'status')  // CASE WHEN expressions
```

#### WHERE Clauses
```php
->where(['id' => 1, 'status' => 'active'])              // AND condition
->whereOr(['status' => 'pending', 'status' => 'draft']) // OR condition
->whereLike(['name' => 'john'])                         // LIKE search
->whereIn('id', [1, 2, 3])                              // IN clause
->whereNotIn('id', [10, 20])                            // NOT IN clause
->whereBetween(['age' => [18, 65]])                     // BETWEEN range
->whereColumn('created_at', '>', 'updated_at')          // Column comparison
->isNull('deleted_at')                                  // IS NULL
->isNotnull('verified_at')                              // IS NOT NULL
->whereExists(fn($q) => ...)                            // EXISTS subquery
->whereNotExists(fn($q) => ...)                         // NOT EXISTS
```

#### Joins
```php
->innerJoin(Noga::joins('posts', 'p')
    ->on('users.id', '=', 'p.user_id'))

->leftJoin(Noga::joins('comments', 'c')
    ->on('posts.id', '=', 'c.post_id'))

->rightJoin(Noga::joins('categories', 'cat')
    ->on('posts.category_id', '=', 'cat.id'))

->crossJoin(Noga::joins('departments', 'd'))
```

#### Grouping & Aggregation
```php
->groupBy(['status', 'created_at'])
->having(['count >' => 5])
```

#### Sorting & Pagination
```php
->orderBy('created_at', 'DESC')  // ASC or DESC
->limit(10)                       // Limit results
->offset(20)                      // Skip results
```

#### Unions
```php
->union(Noga::u()->table('admins')->select('id', 'name'))
->unionAll(Noga::u()->table('moderators')->select('id', 'name'))
```

#### CTEs (Common Table Expressions)
```php
->with('recent_users', 
    fn($q) => $q->table('users')
        ->where(['created_at >' => 'NOW()'])
)
->with('category_tree', 
    fn($q) => $q->table('categories')
        ->select('id', 'name', 'parent_id')
        ->where(['parent_id' => null]),
    true  // Recursive
)
```

#### Execution Methods
```php
->get()                    // All results as objects
->getOne()                 // Single row
->getStream()              // Generator for large datasets
->getSql()                 // Compiled SQL string
->getParams()              // Bound parameters array
->sqlDebug()               // Complete debugging info
```

---

## 🏗️ Project Architecture

```
src/
├── Noga.php                             # Main Facade
├── QueryBuilder/
│   ├── builder.php                     # Clause construction
│   ├── select/                         # SELECT implementation
│   └── crud/                           # INSERT, UPDATE, DELETE
├── Core/
│   ├── BindHashing.php                 # Secure parameter hashing
│   ├── CacheManager.php                # Query caching
│   ├── Sqlast.php                      # SQL parsing & AST
│   ├── NgManager.php                   # Configuration management
│   └── DateManager.php                 # Date formatting (FR/EN)
├── Db/
│   ├── DB.php                          # Database abstraction
│   ├── mysql.php                       # MySQL driver
│   ├── postgres.php                    # PostgreSQL driver
│   └── sqlite.php                      # SQLite driver
├── Traits/
│   ├── BuidlerAttr.php                 # Builder attributes
│   ├── aggregate.php                   # Aggregation functions
│   ├── condition.php                   # Condition building
│   └── dbTrait.php                     # Database connection
├── helpers/
│   └── helpers.php                     # Utility functions
├── CLI/
│   ├── kernel.php                      # Command dispatcher
│   └── commands/                       # Custom commands
├── cache/                              # Query cache storage
├── exceptions/                         # Custom exceptions
└── mapping/                            # Data mapping
```

---

## 🔒 Security in Depth

### Parameter Binding (SQL Injection Prevention)
```php
// ❌ UNSAFE - Vulnerable to SQL injection
$query = "SELECT * FROM users WHERE id = $id";

// ✅ SAFE - Parameters are bound
$query = Noga::table('users')
    ->where(['id' => $id])  // Automatically bound
    ->get();
```

### BindHashing Mechanism
```
Parameter Key Generation:
Input: ['id' => 5, 'status' => 'active']

↓ BindHashing::hash()

Output: 
  :wh_a1b2c3d4_id => 5
  :wh_e5f6g7h8_status => "active"

Each key is:
- Prefixed (:wh_ for WHERE)
- Random hex (a1b2c3d4)
- Column name
- Cryptographically secure
- Impossible to predict or inject
```

### Immutability for Safety
```php
$base = Noga::table('users');
$active = $base->where(['status' => 'active']);
$admins = $base->where(['role' => 'admin']);

// $base, $active, $admins are 3 different objects
// No side effects or shared state
```

---

## 🧪 Testing

### Run Tests
```bash
# Unix/Linux/Mac
./noga test

# Windows
noga.bat test
```

### Test Files
- `test/NogaTest.php` - PHPUnit test suite
- `test/test.php` - Basic examples
- `test/users.php` - User table examples

### Test Configuration
```xml
<!-- phpunit.xml -->
<phpunit bootstrap="vendor/autoload.php">
    <testsuites>
        <testsuite name="Builder SQL Suite">
            <directory>./test</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

---

## 💡 Advanced Examples

### Complex Multi-Level Query
```php
$results = Noga::table('orders')
    ->select(
        'orders.id',
        'orders.total',
        'customers.name',
        'COUNT(items.id) as item_count'
    )
    ->innerJoin(Noga::joins('customers', 'c')
        ->on('orders.customer_id', '=', 'c.id'))
    ->innerJoin(Noga::joins('order_items', 'items')
        ->on('orders.id', '=', 'items.order_id'))
    ->where([
        'orders.status' => 'completed',
        'orders.created_at >=' => '2024-01-01'
    ])
    ->groupBy(['orders.id', 'customers.name'])
    ->having(['item_count >' => 3])
    ->orderBy('orders.total', 'DESC')
    ->limit(20)
    ->get();
```

### Recursive CTE
```php
$hierarchy = Noga::table('categories')
    ->with('category_tree', 
        fn($q) => $q->table('categories')
            ->select('id', 'name', 'parent_id')
            ->where(['parent_id' => null]),
        true  // Recursive
    )
    ->select('*')
    ->get();
```

### Query Caching
```php
// Register query
Noga::table('users')
    ->select('id', 'name')
    ->where(['status' => 'active'])
    ->add_query('get_active_users');

// Reuse from cache
$users = Noga::use_query('get_active_users')->get();

// Clear cache
Noga::removeCache('get_active_users');
Noga::removeAllCache();
```

### EXPLAIN Query Analysis
```php
$analysis = Noga::explain(
    Noga::table('users')
        ->select('*')
        ->where(['id' => 1]),
    'FORMAT=JSON'
);
```

---

## 🔧 Configuration

### Database Configuration
Configure via `NgManager` or environment file:

```php
// Initialize configuration
$config = NgManager::getInstance('path/to/.env');

// Access parameters
$host = ng('db_host');
$port = ng('db_port', 3306);
```

### Cache Configuration
```php
CacheManager::key("my_query")
    ->dir("queries")
    ->delay(3600)  // 1 hour
    ->data($result)
    ->put();
```

---

## 🎯 Use Cases

✅ **Modern Web Applications** - Build safe, fluent queries  
✅ **API Development** - Complex data retrieval with filters  
✅ **Data Analysis** - Aggregations, CTEs, subqueries  
✅ **Reporting Systems** - Multi-table joins and summaries  
✅ **Admin Dashboards** - Dynamic filtering and sorting  
✅ **Microservices** - Database abstraction layer  

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the project
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Setup
```bash
# Clone repository
git clone https://github.com/nogagermainio/Noga_SE.git
cd Noga_SE

# Install dependencies
composer install

# Run tests
./noga test

# Check code
./noga lint
```

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2026 nogagermainio

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions above.
```

---

## 👨‍💻 Author

**nogagermainio**

- GitHub: [@nogagermainio](https://github.com/nogagermainio)
- Email: [your-email@example.com](mailto:contact@nogagermainio.dev)

---

## 🔗 Related Projects

- **Noga_Http** - HTTP Framework companion
- **Noga_CLI** - Command-line interface tools
- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)

---

## 🆘 Support & Issues

Found a bug? Want a feature? Please [open an issue](https://github.com/nogagermainio/Noga_SE/issues) with:

- ✅ Clear problem description
- ✅ Minimal code reproduction
- ✅ Expected vs actual behavior
- ✅ PHP version and OS

### Common Issues

**Q: How do I prevent SQL injection?**  
A: All parameters are automatically bound via BindHashing. Never concatenate user input!

**Q: Can I cache queries?**  
A: Yes! Use `->add_query('name')` and `Noga::use_query('name')`

**Q: Is it compatible with my database?**  
A: Noga_SE supports MySQL, PostgreSQL, SQLite, and is easily extended for others.

**Q: How do I contribute?**  
A: Fork the repo, create a feature branch, and submit a pull request.

---

## 📊 Roadmap

### ✅ Completed
- [x] SELECT with advanced clauses
- [x] INSERT with secure binding
- [x] UPDATE with conditions
- [x] DELETE with protection
- [x] Joins (INNER, LEFT, RIGHT, CROSS)
- [x] Subqueries & nesting
- [x] Unions & intersections
- [x] Recursive CTEs
- [x] Immutability & cloning
- [x] Query caching

### 🔄 In Progress
- [ ] Enhanced error reporting
- [ ] Performance profiling
- [ ] Query optimization hints
- [ ] Additional database drivers

### 📋 Planned
- [ ] Trigger & stored procedure support
- [ ] Database schema builders
- [ ] Migration system
- [ ] Query logging middleware
- [ ] Batch optimization
- [ ] Full-text search support

---

## 📈 Performance Tips

1. **Use Pagination** - Limit large result sets
   ```php
   ->limit(20)->offset($page * 20)
   ```

2. **Cache Frequently Used Queries** - Reuse compiled queries
   ```php
   ->add_query('active_users')
   ```

3. **Use Indexes** - Create database indexes on WHERE columns
   ```sql
   CREATE INDEX idx_status ON users(status);
   ```

4. **Batch Operations** - Insert multiple rows at once
   ```php
   ->values(...)->values(...)->values(...)
   ```

5. **Select Only Needed Columns** - Avoid SELECT *
   ```php
   ->select('id', 'name', 'email')  // Not SELECT *
   ```

---

## 📞 Community & Discussions

- 💬 [GitHub Discussions](https://github.com/nogagermainio/Noga_SE/discussions)
- 🐛 [Issue Tracker](https://github.com/nogagermainio/Noga_SE/issues)
- 📚 [Wiki & Documentation](https://github.com/nogagermainio/Noga_SE/wiki)

---

## 🌟 Show Your Support

If you find Noga_SE helpful, please consider:

- ⭐ Star the repository
- 🍴 Fork and contribute
- 📢 Share with your network
- 💬 Leave feedback

---

**Made with ❤️ by nogagermainio**

Last updated: 2026-07-01  
Version: 1.0.0
