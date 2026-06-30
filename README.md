# 🗄️ Noga_SE - SQL Query Builder

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1+-green.svg)](https://php.net)

**Noga_SE** est un QueryBuilder SQL moderne, fluide et immutable construit en PHP. Il offre une API élégante pour construire des requêtes SELECT, INSERT, UPDATE et DELETE avec sécurité (protection contre les injections SQL) et maintenabilité.

---

## ✨ Caractéristiques

### 🔧 Opérations CRUD Complètes
- **SELECT** - Requêtes complexes avec jointures, sous-requêtes, agrégations
- **INSERT** - Insertions simples et en masse
- **UPDATE** - Mises à jour sécurisées avec conditions
- **DELETE** - Suppressions avec protection

### 🛡️ Sécurité
- **Paramètres bindés** - Protection contre les injections SQL
- **Hachage des paramètres** - Binding sécurisé avec `BindHashing`
- **Immutabilité** - Clonage automatique pour éviter les mutations

### ⚙️ Fonctionnalités Avancées
- **Clauses WHERE complexes** - AND, OR, LIKE, BETWEEN, IN, EXISTS
- **Jointures** - INNER, LEFT, RIGHT, CROSS
- **Sous-requêtes** - Callables, instances Sql, chaînes
- **Agrégations** - GROUP BY, HAVING, fonctions d'agrégation
- **Unions** - UNION, UNION ALL
- **CTE (Common Table Expressions)** - Requêtes WITH récursives
- **Groupage & Tri** - ORDER BY, GROUP BY, LIMIT, OFFSET
- **Cache de requêtes** - Réutilisez les requêtes compilées

### 🔗 API Fluide
```php
$query = Noga::table('users')
    ->select('id', 'name', 'email')
    ->where(['status' => 'active'])
    ->orderBy('created_at', 'DESC')
    ->limit(10);
```

---

## 📦 Installation

### Via Composer
```bash
composer require nogagermainio/noga-se
```

### Configuration de l'autoloading
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

## 🚀 Guide de Démarrage Rapide

### 1️⃣ SELECT - Requêtes de Lecture

#### Requête Simple
```php
use Src\Noga;

$users = Noga::table('users')
    ->select('id', 'name', 'email')
    ->get();
```

#### Avec Conditions
```php
$activeUsers = Noga::table('users')
    ->select('*')
    ->where(['status' => 'active', 'age >=' => 18])
    ->get();
```

#### Avec Joins
```php
$userPosts = Noga::table('users')
    ->select('users.name', 'posts.title')
    ->innerJoin(Noga::joins('posts', 'p')
        ->on('users.id', '=', 'p.user_id'))
    ->get();
```

#### Avec Sous-requêtes
```php
$query = Noga::table('users')
    ->select('id', 'name')
    ->whereIn('id', fn($q) => $q->table('posts')
        ->select('user_id')
        ->where(['published' => true]))
    ->get();
```

#### Avec Agrégations
```php
$stats = Noga::table('orders')
    ->select('user_id', 'COUNT(*) as total')
    ->groupBy(['user_id'])
    ->having(['total >' => 5])
    ->get();
```

#### Avec ORDER BY & LIMIT
```php
$topUsers = Noga::table('users')
    ->select('*')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->offset(0)
    ->get();
```

### 2️⃣ INSERT - Créer des Données

#### Insertion Simple
```php
use Src\QueryBuilder\Insert\Insert;

$result = Insert::table('users')
    ->columns('name', 'email', 'status')
    ->values('John Doe', 'john@example.com', 'active')
    ->exc();
```

#### Insertion Multiple
```php
$insert = Insert::table('users')
    ->columns('name', 'email', 'status');

$result = $insert->values('Alice', 'alice@example.com', 'active')
    ->values('Bob', 'bob@example.com', 'inactive')
    ->exc();
```

#### Déboguer l'Insertion
```php
$debug = Insert::table('users')
    ->columns('name', 'email')
    ->values('Test', 'test@example.com')
    ->debugSql();

// Résultat:
// [
//     "sql" => "INSERT INTO users( name,email ) VALUES(:in_xxxxx,:in_yyyyy)",
//     "params" => [":in_xxxxx" => "Test", ":in_yyyyy" => "test@example.com"],
//     "binding" => [":in_xxxxx", ":in_yyyyy"]
// ]
```

### 3️⃣ UPDATE - Mettre à Jour les Données

#### Mise à Jour Simple
```php
use Src\QueryBuilder\CRUDUpdate;

$update = new CRUDUpdate();
$update->set(['name' => 'Jane', 'status' => 'active']);
// À utiliser avec Sql pour les conditions WHERE
```

#### Via Noga
```php
$result = Noga::table('users')
    ->set_cols(['status' => 'active'])
    ->where(['id' => 1])
    ->update();
```

### 4️⃣ DELETE - Supprimer des Données

#### Suppression Simple
```php
use Src\QueryBuilder\CRUDdelete;

$delete = new CRUDdelete();
$sql = $delete->DeleteData('users', ['id = 1']);
// Exécuter via la base de données
```

#### Via Noga
```php
$result = Noga::table('users')
    ->where(['id' => 1])
    ->delete();
```

---

## 📚 Documentation Complète

### Classe Sql (Requêtes SELECT)

#### Méthodes de Sélection
```php
$query = Noga::table('users')
    ->select('id', 'name', 'email')           // Colonnes à sélectionner
    ->distinct(true)                          // DISTINCT
    ->selectCase(fn($case) => ..., 'status'); // CASE WHEN
```

#### Méthodes WHERE
```php
->where(['id' => 1, 'status' => 'active'])    // WHERE avec AND
->whereOr(['status' => 'pending', 'status' => 'draft']) // OR
->whereLike(['name' => 'john'])                // LIKE
->whereIn('id', [1, 2, 3])                    // IN
->whereNotIn('id', [10, 20])                  // NOT IN
->whereBetween(['age' => [18, 65]])           // BETWEEN
->whereColumn('created_at', '>', 'updated_at') // Comparaison colonnes
->isNull('deleted_at')                        // IS NULL
->isNotnull('verified_at')                    // IS NOT NULL
->whereExists(fn($q) => ...)                  // EXISTS
```

#### Méthodes de Jointure
```php
->innerJoin(Noga::joins('posts', 'p')
    ->on('users.id', '=', 'p.user_id'))

->leftJoin(Noga::joins('comments', 'c')
    ->on('posts.id', '=', 'c.post_id'))

->rightJoin(Noga::joins('categories', 'cat')
    ->on('posts.category_id', '=', 'cat.id'))

->crossJoin(Noga::joins('departments', 'd'))
```

#### Groupage & Agrégation
```php
->groupBy(['status', 'created_at'])
->having(['count >' => 5])
```

#### Tri & Pagination
```php
->orderBy('created_at', 'DESC')  // ASC ou DESC
->limit(10)
->offset(20)
```

#### Union
```php
->union(Noga::u()->table('admins')->select('id', 'name'))
->unionAll(Noga::u()->table('moderators')->select('id', 'name'))
```

#### CTE (Common Table Expressions)
```php
->with('recent_users', 
    fn($q) => $q->table('users')
        ->where(['created_at >' => 'NOW()'])
)
```

#### Exécution
```php
->get()                    // Tous les résultats (PDO::FETCH_OBJ)
->getOne()                 // Une seule ligne
->getStream()              // Générateur pour gros volumes
->getSql()                 // SQL compilé
->getParams()              // Paramètres bindés
->sqlDebug()               // Débogage complet
```

---

## 🗂️ Architecture du Projet

```
src/
├── Sql.php                          # Classe principale de requête
├── Noga.php                         # Facade statique
├── QueryBuilder/
│   ├── Insert/
│   │   └── Insert.php              # INSERT immutable
│   ├── ClauseBuilder.php           # Construction des clauses WHERE, SELECT
│   ├── CRUDUpdate.php              # UPDATE builder
│   ├── CRUDdelete.php              # DELETE builder
│   ├── CRUDInsert.php              # INSERT builder (legacy)
│   ├── JoinBuilder.php             # Construction des JOINS
│   ├── UnionBuilder.php            # Construction des UNIONS
│   ├── CaseBuilder.php             # CASE WHEN expressions
│   └── SubBuilder.php              # Sous-requêtes
├── Core/
│   └── BindHashing.php             # Hachage sécurisé des paramètres
├── Traits/
│   ├── DbTrait.php                 # Gestion de la base de données
│   ├── Aggregate.php               # Fonctions d'agrégation
├── cache/
│   └── CacheManager.php            # Cache des requêtes compilées
├── database/
│   └── ...                         # Configuration BD
└── helpers/
    └── helpers.php                 # Fonctions utilitaires
```

---

## 🔒 Sécurité

### Paramètres Bindés
Tous les paramètres sont automatiquement bindés pour prévenir les injections SQL:

```php
// ❌ MAUVAIS - Injection possible
$query = "SELECT * FROM users WHERE id = $id";

// ✅ BON - Sécurisé
$query = Noga::table('users')
    ->where(['id' => $id])  // Paramètre sécurisé
    ->get();
```

### Hachage de Paramètres
Les paramètres sont hachés pour éviter les collisions:
```php
// Les clés de paramètres sont générées comme:
// :in_xxxxx, :wh_yyyyy, :HAV_zzzzz
```

---

## 🧪 Tests

```bash
# Exécuter la suite de tests
./noga test

# Windows
noga.bat test
```

### Fichiers de Test
- `test/` - Suite de tests PHPUnit

---

## 💡 Exemples Avancés

### Requête Complexe Multilevel
```php
$query = Noga::table('orders')
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
    ->limit(20);

$results = $query->get();
```

### Avec Sous-requête
```php
$topCustomers = Noga::table('customers')
    ->select('id', 'name', 'email')
    ->whereIn('id', fn($q) =>
        $q->table('orders')
            ->select('customer_id')
            ->groupBy(['customer_id'])
            ->having(['COUNT(*) >' => 10])
    )
    ->get();
```

### Avec CTE Récursive
```php
$hierarchy = Noga::table('categories')
    ->with('category_tree', 
        fn($q) => $q->table('categories')
            ->select('id', 'name', 'parent_id')
            ->where(['parent_id' => null]),
        true  // Récursive
    )
    ->select('*')
    ->get();
```

### Cache de Requête
```php
// Enregistrer une requête
Noga::table('users')
    ->select('id', 'name')
    ->add_query('get_active_users');

// Réutiliser
$users = Noga::use_query('get_active_users')->get();

// Supprimer du cache
Noga::removeCache('get_active_users');
Noga::removeAllCache();
```

---

## 🏗️ Immutabilité et Pattern Builder

### Immutable par Design
Chaque méthode retourne un **nouvel objet cloné**, garantissant l'immuabilité:

```php
$base = Noga::table('users');
$active = $base->where(['status' => 'active']);
$admins = $base->where(['role' => 'admin']);

// $base, $active et $admins sont 3 objets différents
// Les modifications n'affectent pas la requête originale
```

### Chainable
```php
$result = Noga::table('users')
    ->select('id', 'name')
    ->where(['active' => 1])
    ->orderBy('name')
    ->limit(10)
    ->get();
```

---

## 📋 Configuration

### Fichier .env (optionnel)
Les paramètres de connexion doivent être configurés via les traits DbTrait.

---

## 🤝 Contribution

Les contributions sont bienvenues! Veuillez:

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 👨‍💻 Auteur

**nogagermainio** - [GitHub Profile](https://github.com/nogagermainio)

---

## 🔗 Ressources Connexes

- **Noga_Http** - Framework HTTP complémentaire
- **PHP PDO** - [Documentation PDO](https://www.php.net/manual/fr/book.pdo.php)

---

## 🆘 Support & Issues

Vous rencontrez un bug? Ouvrez une [issue](https://github.com/nogagermainio/Noga_SE/issues) avec:
- Description du problème
- Code de reproduction
- Environnement (PHP, OS, BD)

---

## 📊 Roadmap

- [x] SELECT avec clauses avancées
- [x] INSERT avec binding sécurisé
- [x] UPDATE avec conditions
- [x] DELETE avec protection
- [x] Jointures (INNER, LEFT, RIGHT, CROSS)
- [x] Sous-requêtes
- [x] Unions & Intersections
- [x] CTE récursives
- [ ] Refactorisation CRUD en architecture immutable/registry
- [ ] Builders CRUD standardisés
- [ ] Support des triggers & stored procedures
- [ ] Middleware de logging

---

**Made with ❤️ by nogagermainio**
