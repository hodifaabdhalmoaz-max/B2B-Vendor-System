# Phase 9 — B2B verification and stabilization

## Baseline and scope

Baseline: `afb646d4611f61856835b5c6b9622434ab5bf444`, verified against fetched `origin/main` on 2026-09-26. Branch: `codex/phase-9-b2b-stabilization`. The implementation commit is the commit containing this document (`git log -1 -- docs/B2B_PHASE9.md`).

No new business functionality, B2C retirement, external channels, payments, or outbox. No canonical migration defect was found and no migrations were added. ReservationService, InventoryService, ProductVariantService and the reseller notification service/repository retain their existing mutation boundaries.

## Changes and findings

- Added the read-only `php artisan b2b:verify-schema` command. Exit 0 means the inspected contract and Phase 1–8 migration history match; exit 1 means missing/incompatible structure, pending migrations, an unsupported driver, or failed inspection. It never prompts or runs migrations, DDL or data writes. Database inspection errors are reported generically without exception messages, SQL, connection strings or credentials.
- Added Phase 9 integration, strict-loading, schema-drift, query-growth, CSRF and focused architecture regression tests. All new normal variant and inventory fixtures use the domain services. Intentional schema corruption is limited to guarded SQLite `:memory:` databases.
- Fixed a verification defect in the MySQL worker: independent stock contenders previously shared an idempotency key. Successful idempotent replay could therefore produce two successes without overselling. Independent inventory contenders now have different keys; the separate same-key reservation scenario remains unchanged.
- Hardened MySQL test targeting: clear the connection URL override, assert the selected database name before refresh, validate worker opt-in, preserve `_test`/`_testing` naming and explicit fresh authorization. MySQL fixtures now use ProductVariantService and InventoryService. Reserve-ledger assertions exclude the legitimate initial stock-in movement.
- Added MySQL prerequisites: schema verifier success, InnoDB for all five critical tables, session foreign-key enforcement, and reported session isolation. Added winning transition/creation notification uniqueness assertions without changing business lock ordering.
- Phase 8's real-commit test setup now explicitly refuses fresh migration unless the connection is SQLite `:memory:`.
- Ran requested Pint normalization on ProductVariantController and ProductVariantService. Their diff ignoring whitespace is empty. **No existing production business behavior was changed.** The new diagnostic command is the only new production behavior.

## Schema contract

Laravel's schema metadata APIs inspect SQLite and MySQL tables, columns, unique indexes and foreign keys. Index names are not assumed: logical ordered column sets and uniqueness are checked. SQLite partial unique indexes cannot satisfy the required durable constraints. The authoritative complete list is `VerifyB2BSchema::COLUMNS`, `UNIQUE_KEYS`, and `FOREIGN_KEYS`.

| Table | Required fields |
| --- | --- |
| users | id, name, username, mobile, password, utype, is_active, force_password_change |
| reseller_profiles | id, user_id, business_name, whatsapp, governorate, group_name, notes, status, reservation_enabled, reservation_timeout_minutes, created_at, updated_at |
| products | id, name, slug, SKU, regular_price, sale_price |
| product_variants | id, product_id, color_id, size_id, sku, variant_key, price_adjustment, is_active, created_at, updated_at |
| inventory_items | id, product_variant_id, stock_on_hand, reserved_quantity, low_stock_threshold |
| inventory_movements | id, product_variant_id, type, quantity, stock_delta, reserved_delta, stock_on_hand_after, reserved_quantity_after, reference_type, reference_id, actor_user_id, idempotency_key, request_fingerprint, metadata, created_at |
| reservations | id, reservation_number, reseller_profile_id, idempotency_key, request_fingerprint, status, expires_at, confirmed_at, cancelled_at, released_at, notes, created_at, updated_at |
| reservation_items | id, reservation_id, product_variant_id, quantity, unit_price, product_name_snapshot, sku_snapshot, variant_snapshot, created_at, updated_at |
| notifications | id, type, notifiable_type, notifiable_id, data, read_at, event_key, created_at, updated_at |
| wishlists | id, user_id, product_id |
| migrations | id, migration, batch |

Required uniqueness: reseller_profiles(user_id); product_variants(sku) and (product_id, variant_key); inventory_items(product_variant_id); inventory_movements(idempotency_key); reservations(reservation_number) and (idempotency_key); reservation_items(reservation_id, product_variant_id); notifications(event_key); wishlists(user_id, product_id).

Required restrictive FKs: reseller profile → user, variant → product, inventory item/movement → variant, reservation → reseller profile, reservation item → reservation and variant. Child product_id targets **products.id**, never products.product_id. Existing Phase 3 tests additionally exercise product deletion protection and variant deletion refusal for reservation history, ledger history and nonzero stock.

The monetary contract matches the canonical migrations exactly: `products.regular_price` and `products.sale_price` require MySQL `decimal(8,2)` (the Laravel 11 default used by `2024_09_28_145026_create_products_table.php`); `product_variants.price_adjustment` and `reservation_items.unit_price` require `decimal(12,2)`. `VerifyB2BSchema::MONETARY_TYPES` records these per-column expectations and checks both precision and scale. SQLite retains its numeric-affinity check because it does not preserve decimal precision/scale metadata. Movement deltas require signed MySQL bigint or SQLite integer. SQLite does not prove MySQL precision enforcement or lock behavior. This is a focused schema contract, not an exhaustive database/data-integrity analyzer; it does not certify every legacy field, CHECK constraint, trigger, collation or production query plan.

## Migrations and local runtime

Full fresh migration chain passed on explicitly controlled SQLite `:memory:`. The fresh database passes the schema verifier and migrate:status. Negative tests cover recorded-but-missing stock_delta, missing event uniqueness, pending migration, partial uniqueness, missing notifications table, and incompatible delta type. Verification does not repair any of these conditions; a query-log assertion checks that successful verification emits no mutation statements.

Phase 8's migration preservation test passed: old notification rows, IDs and types survive addition/removal of the nullable event key and its indexes. The Phase 4 balance migration exists and its stock_delta/reserved_delta/balances/fingerprint fields are checked.

Read-only checks of the ordinary local runtime found:

- `notifications.event_key` and its unique index missing.
- `2026_09_26_000001_add_reservation_event_key_to_notifications` is **Pending**, confirmed by `php artisan migrate:status`.
- Phase 4 inventory balances are present; their migration is recorded as run.
- All other inspected requirements passed. No local data or schema was modified.

Operator remediation for this runtime: review `php artisan migrate:status`, then apply the pending canonical migration with `php artisan migrate`, and rerun `php artisan b2b:verify-schema`. A permanent repair migration is not warranted. If a field is absent despite a recorded migration, investigate drift and restore from the correct schema/backup; rebuild only a database explicitly declared disposable.

Fresh SQLite reproduction (PowerShell, environment limited to the test shell):

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
$env:DB_URL = ''
php artisan migrate:fresh --env=testing --force
php artisan test --compact tests/Feature/B2BPhaseNineStabilizationTest.php
```

Each separate `:memory:` process starts empty; the Phase 9 test migrates and verifies within the same application process. Never run fresh on an ordinary development database. Historical legacy SQLite down-migration debt remains separate from the verified forward chain.

## Functional, authorization and isolation results

The Phase 9 HTTP workflow covers catalog visibility, cart submission, same-key replay, pending_review → confirmed → preparing → shipped → completed, and repeated admin actions. Ten stocked units with two reserved end at eight physical and zero reserved, with exactly stock_in/reserve/order_completed ledger rows. Unit price is exactly 10.30 from 10.10 + 0.20 using existing DecimalMoney semantics. Historical reseller/admin details retain original product, SKU and color snapshots after legal edits. No Order or OrderItem is created.

Cancellation and scheduled expiry release stock once, leave physical stock unchanged, and notify once. Repeated reseller cancellation is rejected by the existing pending-only portal rule; direct domain replay remains harmless. Admin lifecycle replay and invalid transitions remain covered by Phases 5, 7 and 8.

Passing existing tests reverify admin/reseller/USR, inactive and forced-password-change access; foreign reservation/notification IDs; profile-scoped carts and user-scoped wishlists; idempotency payload conflicts; append-only ledger; inventory invariants; admin movement actors; product/variant deletion protection; and legacy MessageCenter/Order isolation. Phase 9 additionally executes notification read/read-all with Laravel's test CSRF bypass disabled and verifies 419, while GET leaves read state unchanged. Focused route/source guards protect explicit lifecycle routes, absent public signup, controller service delegation and absence of Order dependencies.

Phase 7's injected database-error test confirms generic admin feedback without private SQL/database details. Phase 8's notification failure test confirms committed business state survives delivery failure and logs event metadata without the exception payload. No notification recipient is derived from the actor.

## Lazy loading and query baseline

Lazy-loading prevention remains enabled. Phase 9 additionally marks retrieved ProductVariant instances strict, covering Laravel's normal single-row hydration exemption. The actual variant Blade renders effectivePrice with existing stock and detached historical color; a real update request reaches stock/history inspection and rejects an identity change without lazy loading. No protection was disabled.

Measured warm full HTTP requests under local SQLite tests:

| Page | Small fixture | Larger fixture |
| --- | ---: | ---: |
| Reseller catalog | 7 | 8 |
| Reseller reservation list | 4 | 4 |
| Reseller reservation detail | 3 | 3 |
| Reseller notifications | 3 | 3 |
| Admin reservation list | 6 | 6 |
| Admin reservation detail | 7 | 7 |
| Admin variant management | 5 | 6 |

Small: one reservation/item/default variant. Larger: eight reservations, six items in the measured detail, seven variants on the managed product and eight lifecycle notifications. The one-query increase on catalog/variants loads colors that did not exist in the small fixture; it is not per-row growth. Tests allow at most one additional query. Existing Phase 6/7/8 pagination/query checks also pass. These are structural local baselines, not production latency claims.

Index review: variant product_id/is_active and inventory variant uniqueness support catalog loading; wishlist user/product uniqueness supports ownership; reservation profile/status and status/expiry support history/expiry; reservation-item reservation/variant uniqueness supports item loading; movement reference_type/reference_id supports the ledger; recipient/read_at and recipient/created_at indexes support notifications. Leading-wildcard searches and broad date sorting may scan/sort at scale; no measured evidence warranted speculative new indexes. Production EXPLAIN/volume measurements remain future operational work.

## MySQL release gate

**MYSQL CONCURRENCY GATE = NOT EXECUTED.** No explicit disposable MySQL database or fresh opt-in was configured. No database was created, dropped or inferred disposable.

- Database used: none.
- InnoDB verification: not executed.
- Session isolation: not observed.
- Foreign-key enforcement: not observed on MySQL.
- MySQL schema-verifier result: not executed.
- MySQL concurrency/notification interaction: not verified by a real run.

The gate still covers independent InventoryService competition, distinct reseller profiles competing for one unit, identical reservation keys resolving to one reservation/hold, and confirm versus expire. It requires successful schema verification and InnoDB/foreign-key checks before races. It records effective session isolation without modifying global settings. Notification assertions are after worker completion; no application transaction or lock ordering was changed.

An operator must first provision and explicitly designate an **empty disposable** database named `b2b_concurrency_testing` on the intended MySQL server, with host/user/password supplied securely through the existing MySQL environment/configuration. Then run in a dedicated PowerShell shell:

```powershell
$env:B2B_MYSQL_CONCURRENCY_DATABASE = 'b2b_concurrency_testing'
$env:B2B_MYSQL_CONCURRENCY_ALLOW_FRESH = '1'
php artisan test --compact tests/Feature/B2BPhaseFiveMySqlConcurrencyTest.php
```

This deliberately destroys/rebuilds that named test database's tables. Do not substitute development/production databases. The command runs schema verification in the same selected MySQL connection after migration. Record the emitted database, engine, FK and isolation evidence here before accepting the release gate. SQLite success cannot substitute for this run.

## Validation and remaining debt

Initial Phase 9 combined suite: **149 passed, 2 skipped, 1,696 assertions**. Includes the original 12 Phase 9 tests, Phases 1–8, AuthenticationHardening, MessageCenter and the opt-in MySQL gate. Additional ProductVariantSelectionTest, ProductServiceTest and Unit/ProductTest: **18 passed, 84 assertions**.

Monetary-verifier correctness patch based on `74189d0091741cd749ab91696190117f41b0b8ff`: corrected the overly strict product-price expectation to the canonical `decimal(8,2)`, while keeping the two B2B monetary columns at `decimal(12,2)`. No historical migration, database precision, or ordinary local database was changed. The added command-level regression supplies controlled MySQL introspection metadata: the canonical four-column contract passes, and eight cases with incorrect precision or scale fail on the affected column. Existing fresh SQLite verification still passes with numeric affinity. These metadata tests do not replace the unexecuted real MySQL gate.

Patch validation: **168 passed, 1 skipped, 1,829 assertions**, combining all requested Phase 1–9, authentication and MessageCenter suites with ProductVariantSelectionTest, ProductServiceTest and Unit/ProductTest. Phase 9 now has 13 tests. The skip is Phase 4's SQLite concurrency reminder; the separate opt-in MySQL gate was not included in this patch run. Pint on both changed PHP files and git diff --check passed. No Blade or translation files changed, so no additional compilation or translation validation was needed.

Known skipped tests: Phase 4's SQLite concurrency reminder and Phase 5's opt-in MySQL gate. Existing PHPUnit doc-comment deprecations remain.

ProductControllerTest separately reproduces **8 failures**: first expected 200 receives 302, followed by seven active-transaction errors. The test requests shop.index as a guest; the existing route intentionally redirects /shop to categories.index. This route predates the B2B phases and is unchanged. No B2C routing was altered to satisfy the obsolete expectation.

Pint on changed PHP files, Blade view compilation, parsing/alignment of lang/ar.json and lang/en.json, and git diff --check passed. Logs are local ignored storage/phase9-*.log artifacts.

After-commit behavior is proven by Phase 8 tests using real commits: callbacks wait for outer commit, rollback suppresses notifications, listener execution occurs at transaction level zero, replay does not duplicate, and delivery exceptions do not undo inventory. Review confirms recipient lookup remains a plain lookup and notification INSERT occurs after commit. There is **no eventual-delivery guarantee**: a crash or insert failure after commit can lose a notification. No outbox/retry mechanism was introduced.

Remaining debt: unexecuted real MySQL gate, unapplied local Phase 8 migration, legacy down-migration issues, unrelated ProductControllerTest failures, doc-comment warnings, notification delivery gap, and production-scale query-plan measurement. The referenced `.Codex/rules/database.md` is absent in this checkout; supplied AGENTS.md database rules were followed.

## File manifest

Created:

- app/Console/Commands/VerifyB2BSchema.php
- tests/Feature/B2BPhaseNineStabilizationTest.php
- docs/B2B_PHASE9.md

Modified:

- app/Http/Controllers/Admin/ProductVariantController.php (formatting only)
- app/Services/ProductVariantService.php (formatting only)
- tests/Feature/B2BPhaseEightNotificationTest.php (fresh-database safety assertions)
- tests/Feature/B2BPhaseFiveMySqlConcurrencyTest.php (gate prerequisites, fixtures, assertions and target safety)
- tests/Support/reservation_concurrency_worker.php (independent inventory keys and target safety)

## Phase 9.5A readiness

**NOT READY FOR PHASE 9.5A**

Concrete verification blockers: the mandatory real MySQL/InnoDB race gate has not run, so production row-lock behavior is unproven; the inspected local runtime must apply its pending Phase 8 notification migration and pass the verifier. No new B2B functional blocker was observed in the SQLite regressions. Phase 9.5 has not started.
