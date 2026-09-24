# Phase 5 reservation engine

`ReservationService` is the only application entry point for new reservations and lifecycle transitions. Phase 6 callers should pass an approved `ResellerProfile`, a list of `{product_variant_id, quantity}` pairs, and a caller-owned creation idempotency key. Price, identity snapshots, expiry, and status are derived on the server.

Creation first normalizes and sorts unique variant IDs, then opens one transaction. It locks and checks the profile and user, inserts the unique idempotency row, and processes variants in ascending ID order. Each variant is locked before its snapshot is read. `InventoryService::reserve()` locks that variant and its inventory item, and writes the movement in a nested savepoint. The reservation item is inserted after that reserve succeeds. Any failure rolls back the reservation, items, balances, and movement rows together. A matching committed key replays the existing reservation; a different fingerprint produces an idempotency conflict. The fingerprint includes reseller ID, sorted item IDs and quantities, and notes. Reservation numbers use `RSV-YYYYMMDD-ULID` plus the database unique constraint.

Inventory keys are `reservation:{reservation_id}:reserve:{variant_id}`, `reservation:{reservation_id}:release:{variant_id}`, and `reservation:{reservation_id}:complete:{variant_id}`. The stable movement reference is `reservation` and the reservation ID. Creation records the reseller user as actor. Lifecycle operations may accept an admin or system actor ID. These operations never change stock directly.

Allowed transitions:

| Current state | Next state | Inventory effect |
| --- | --- | --- |
| pending_review | confirmed | None |
| pending_review | cancelled or expired when due | Release all items |
| confirmed | preparing or cancelled | Release only on cancellation |
| preparing | shipped or cancelled | Release only on cancellation |
| shipped | completed | Consume all reserved items |
| completed, cancelled, expired | Terminal | None |

Every transition locks and rereads the reservation row. Repeated terminal actions are safe, and the inventory keys provide a second idempotency layer. `released_at` marks cancellation or expiration; completion consumes stock and leaves it null. New reservations require an active RES user, active reseller profile, and `reservation_enabled=true`. Existing reservations may still be released or completed after suspension, reservation disabling, or variant deactivation.

`reservation_timeout_minutes=null` means no automatic expiry. A positive value sets `expires_at` on creation. Only due `pending_review` rows are expired. `reservations:expire` scans them in batches and calls the service. It runs every minute with `withoutOverlapping()`. Production cron must invoke `php artisan schedule:run` every minute. Multiple servers need an appropriately shared scheduler cache/lock store before relying on cross-server overlap prevention.

The regular suite runs on SQLite. It verifies rollback, ordering, idempotency, money arithmetic, and state handling, but SQLite does not prove InnoDB row-lock behavior. The opt-in `B2BPhaseFiveMySqlConcurrencyTest` uses two independent PHP processes and connections for four races: Phase 4 reserve vs reserve, Phase 5 create vs create, same-key creation replay, and confirm vs expire. It intentionally runs `migrate:fresh` only when `B2B_MYSQL_CONCURRENCY_DATABASE` names a disposable database ending `_test` or `_testing` and `B2B_MYSQL_CONCURRENCY_ALLOW_FRESH=1`. Configure normal MySQL connection credentials for that database before running it. Passing this gate on the production MySQL/MariaDB version and InnoDB configuration remains a release requirement.

Phase 6 should expose the service through authorized reseller and admin workflows, supply stable caller keys, and display the immutable item snapshots. It should not edit item quantities after creation.
