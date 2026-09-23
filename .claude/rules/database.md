# Database, Migration & Model Rules

Guidelines for database schema design, Eloquent models, performance optimization, and security practices to ensure scalability, ease of maintenance, and safety.

## 1. Security & Protection

* **Mass Assignment Protection:**
  * **Rule:** Never use `$guarded = []` or `$guarded = ['id']` on any model. Always explicitly declare `$fillable` to prevent Mass Assignment vulnerabilities.
* **SQL Injection Prevention:**
  * **Rule:** Never concatenate variables inside raw SQL queries (e.g., `DB::raw("SELECT * FROM users WHERE id = " . $id)`). Always use bindings: `DB::select('SELECT * FROM users WHERE id = ?', [$id])` or use standard Eloquent builder methods.
* **Data Security & Hashing:**
  * **Rule:** Sensitive columns like passwords must use the `'hashed'` cast. Personal Identifiable Information (PII) or API credentials must be encrypted using the `'encrypted'` cast.

## 2. Structure & Integrity

* **Naming Conventions:**
  * **Rule:** Table names must be plural `snake_case` (e.g., `order_items`). Model names must be singular `PascalCase` (e.g., `OrderItem`). Foreign keys must follow the pattern `table_singular_id` (e.g., `product_id`).
* **Monetary Values (Currency Precision):**
  * **Rule:** Never use float types for prices. Use decimal with a minimum scale of `12,2` (e.g., `$table->decimal('price', 12, 2)`) to accommodate high-value currencies and prevent numerical overflow.
* **Foreign Key Constraints:**
  * **Rule:** All relationships must have explicit foreign key constraints with safe cascading rules:
    * Use `onDelete('cascade')` only when the child record should not exist without the parent (e.g., `order_items` when `orders` is deleted).
    * Use `onDelete('set null')` for optional relationships, ensuring the foreign key column is defined as `$table->foreignId('...')->nullable()->change()`.

## 3. Performance & Scalability (High Traffic Support)

* **Indexing Rules:**
  * **Rule:** Apply database indexes (`$table->index()`) on:
    * Foreign keys (unless Laravel automatically indexes them, e.g., using `foreignId()`).
    * Columns frequently filtered in `WHERE` clauses (e.g., `status`, `type`, `stock_status`).
    * Columns used for ordering (e.g., `created_at`, `views`).
  * **Rule:** Create composite indexes for combined query filters (e.g., filter by category and sort by price requires an index on `['category_id', 'regular_price']`).
* **N+1 Query Prevention:**
  * **Rule:** Eager load relationships using `with()` when accessing related model attributes in loops or collections. Enable Lazy Loading Prevention in `AppServiceProvider` during local development: `Model::preventLazyLoading(!app()->isProduction());`.
* **Database Transactions:**
  * **Rule:** Wrap all multi-row write operations (e.g., creating an order, inserting items, charging transactions, and decrementing stock) in `DB::transaction()` to ensure atomicity.
* **Log & Metadata Bloat Prevention:**
  * **Rule:** Any logging or viewing tables (e.g., `user_activities`, `product_views`, `error_logs`) must implement Laravel's `Prunable` trait or have scheduled deletion routines in the weekly maintenance task to prevent database size inflation.

## 4. Maintainability & Standards

* **Model Casts:**
  * **Rule:** Dates, prices, boolean flags, and JSON fields must be cast to native PHP types via the `casts()` method on the model to guarantee type safety in PHP.
* **Factories & Seeders:**
  * **Rule:** Every model must have a corresponding Model Factory using realistic `Faker` data. Seeders must build logical associations (e.g., seeding users must also seed addresses and orders).
* **Migration Idempotency:**
  * **Rule:** All migrations must have a complete `down()` method that safely drops newly added tables, columns, or indexes in the reverse order of creation.
