# Phase 4 inventory contract

`InventoryItem` is the authoritative B2B balance for a `ProductVariant`. Available
quantity is exactly `stock_on_hand - reserved_quantity`. Legacy `products.quantity`,
`product_colors.quantity`, and `product_sizes.quantity` remain transitional B2C data;
they are neither read nor synchronized by the B2B engine.

`InventoryService` is the boundary for real quantity changes. Each public method
requires a caller-owned nonempty idempotency key and accepts optional structured
`reference_type`, `reference_id`, `actor_user_id`, and `metadata` context. It returns
the created movement or the original movement for an exact replay. Keys are global
across variants and operations. A SHA-256 fingerprint binds a key to the requested
variant, operation, amount or absolute target, reference, actor, and metadata. Old
movements lacking a fingerprint cannot be replayed through Phase 4.

The service uses a nested Laravel transaction. It checks for an existing key, locks
the variant and inventory row with `FOR UPDATE`, validates from the locked balances,
updates the item, then inserts exactly one movement. The key lookup is nonlocking to
avoid an absent-key gap lock. The unique key index is the
final race arbiter. Only a duplicate violation on the movement idempotency index is
treated as a possible replay; the failed transaction rolls back before the movement
is reloaded and its fingerprint checked. Other SQL failures propagate and roll back.
Phase 5 can wrap several calls in one outer transaction and must acquire variants in
ascending variant ID order to limit deadlocks. It must not treat sequential calls
without an outer transaction as one reservation.

`stockIn`, `stockOut`, and `adjustStockTo` are allowed for inactive variants so an
operator can manage physical stock. New `reserve` calls require an active variant;
`release` and `consumeReserved` remain available after deactivation. `consumeReserved`
decreases on-hand and reserved quantities in one operation and uses the existing
`order_completed` movement type. A no-change absolute adjustment is rejected because
there is no real mutation to ledger. `low_stock_threshold` is outside the quantity
ledger.

New movements have signed stock and reserved deltas plus resulting balance snapshots.
Historical movements retain nulls in these columns. Eloquent instance updates and
deletes are rejected. Raw SQL and bulk query-builder operations bypass model events;
database access must be restricted operationally if stronger ledger immutability is
required.

The backfill creates only zeroed `InventoryItem` rows where absent, using the unique
variant key to avoid duplicates. It creates no movements and copies no legacy stock.
No additive `CHECK` constraint was added: Laravel's portable schema API does not
provide one, and rebuilding SQLite tables or issuing driver-specific DDL in an
additive migration is fragile. MySQL unsigned columns, model guards, and locked
service validation are mandatory defenses. Direct database writes remain outside the
service boundary.

SQLite tests cover arithmetic, validation, idempotency, rollback, immutability,
backfill, outer transactions, and admin access. SQLite does not verify MySQL row
locks. Before Phase 5 ships, run a dedicated MySQL integration test with two
independent connections attempting `reserve(1)` on a one-unit inventory item;
exactly one must succeed.
