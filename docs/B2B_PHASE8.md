# B2B Phase 8: reservation lifecycle notifications

## Scope and baseline

Implemented on `407cc75` (which includes the approved Phase 7 ancestor `9fbf1cd7f51193174aa052e6fb71744574622a53`). ReservationService remains the sole mutation boundary. Inventory operations, locking order, lifecycle permissions, audit logging, and Phase 6/7 controllers remain intact. No external channels, providers, HTTP calls, broadcasting, or queue worker are introduced.

## Event contract

All classes are under `App\Events\B2B` and extend `ReservationLifecycleEvent`:

| Class | Actual transition |
| --- | --- |
| ReservationCreated | New reservation persisted as pending_review with inventory held |
| ReservationConfirmed | pending_review → confirmed |
| ReservationPreparing | confirmed → preparing |
| ReservationShipped | preparing → shipped |
| ReservationCompleted | shipped → completed after consuming reserved stock |
| ReservationCancelled | pending_review / confirmed / preparing → cancelled after releasing stock |
| ReservationExpired | Due pending_review → expired after releasing stock |

The readonly scalar payload contains reservation ID, reseller profile ID, owning user ID, reservation number, new status, optional actor user ID, and occurredAt (the saved lifecycle updated_at). There are no Eloquent graphs, request/session objects, fingerprints, inventory internals, or secrets. The actor never determines the recipient.

`ReservationService::emitLifecycle()` is the authoritative event construction and dispatch point. Its calls are inside each successful mutation transaction, after the final write/inventory operation. Replay/invalid paths never reach it. `DB::afterCommit()` registers the immutable snapshot; event dispatch waits for the outermost transaction commit. Outer rollback discards the callback. Existing reservation/profile/variant/inventory locking order is unchanged; recipient resolution adds a plain relation value lookup.

## Persistence and delivery

The existing Laravel `notifications` table and User's existing `Notifiable` trait are reused. `ResellerNotification` extends Laravel's `DatabaseNotification`, declares explicit fillable fields and casts, and writes the same standard polymorphic User ownership. No competing store exists.

`PersistResellerReservationNotification` subscribes to the seven explicit events in EventServiceProvider. It calls ResellerNotificationService → ResellerNotificationRepository. This is synchronous database-only delivery after commit; it requires no worker. Types are `reservation_created`, `reservation_confirmed`, `reservation_preparing`, `reservation_shipped`, `reservation_completed`, `reservation_cancelled`, and `reservation_expired`.

The additive migration `2026_09_26_000001_add_reservation_event_key_to_notifications.php` adds:

- Nullable unique `event_key`, preserving existing legacy rows with NULL keys.
- Recipient type + recipient ID + read_at index.
- Recipient type + recipient ID + created_at index.

The durable uniqueness boundary is `reservation:{reservation_id}:{event_name}`, for example `reservation:123:confirmed`. Eloquent firstOrCreate handles competing unique-key inserts. Redelivery does not overwrite data, timestamps, or read state. The original lifecycle timestamp is used as notification created_at, rather than delivery time. Notification type, key, JSON reservation ID, and timestamp support inspection in the database.

Data snapshots contain reservation_id, reservation_number, status, occurred_at, and actor_user_id. UI copy is translated when rendering, from the semantic type; it is not stored as translated text.

## Reliability decision and limitations

This implements the requested lightweight model B: committed events with durable, idempotent notification persistence. It does not introduce an outbox or asynchronous infrastructure for a single local database write.

The after-commit dispatcher catches delivery exceptions, logs an error with reservation_id, event_type, event_key, and exception_class, and lets the already committed operation return successfully. Exception messages, SQL, and payload dumps are not logged by this handler or shown in notification views. Notification failures never undo a committed reservation or its inventory operation.

There is deliberately **no automatic retry or durable pending-event journal**. A process crash between commit and dispatch, or a failed notification insert, can leave a missing notification. Lifecycle command replay will not regenerate an event because there is no new transition. An operator can redeliver a trusted original event snapshot through the listener/service; unique keys make successful deliveries safe to retry. Failed events do not have a complete snapshot saved by this phase. Do not promise guaranteed eventual delivery. Add a transactional outbox and retry/backoff before requiring guaranteed external delivery; this is remaining technical debt, not an implicit queue dependency.

Future independently queued WhatsApp/email adapters can subscribe to the explicit event classes without changing ReservationService. They must retain scalar snapshots, enforce their own per-channel idempotency, isolate failures, and never mutate reservation status or inventory. Parent-class event subscriptions are not automatically dispatched by Laravel; register concrete event classes. No external adapter is implemented here.

## Portal and security

All routes inherit web/CSRF, auth, reseller, force.password.change, reseller translations, and smart.throttle:user_dashboard:

| Method | Path | Name |
| --- | --- | --- |
| GET | /reseller/notifications | reseller.notifications.index |
| POST | /reseller/notifications/{notification}/read | reseller.notifications.read |
| POST | /reseller/notifications/read-all | reseller.notifications.read-all |

Recipient identity comes only from Request::user(). All list/count/read queries scope both notifiable columns and the B2B semantic type allowlist. Foreign/missing IDs return 404. IDs on the read route must be UUIDs. Read updates preserve an existing read_at; read-all performs a single scoped bulk update. Legacy notifications and other users are unaffected. GET never marks anything read.

The Bootstrap portal page displays 20 rows per page, newest first (created_at, then UUID for stable ties), localized titles/descriptions, reservation number, timestamp, read state, and reservation links. Missing/malformed JSON fields get safe fallbacks. Missing reservation rows do not prevent rendering the historical snapshot; the existing ownership-protected detail route returns 404 on access. There are no per-notification reservation queries.

One composer for reseller.layout performs one unread COUNT per page render and supplies the navigation badge. Normal navigation refreshes it; no polling or unread-count endpoint is needed. No additional dashboard section or admin center was added.

MessageCenterService, Order notifications, campaigns, promotions, recommendation behavior, and messages_cleared_at are untouched. Arabic and English root JSON dictionaries remain aligned.

## Verification

Combined required suite: **137 passed, 2 skipped, 1,402 assertions**:

- B2BPhaseEightNotificationTest (14 tests).
- B2BPhaseSevenAdminReservationTest, B2BPhaseSixPortalTest.
- B2BPhaseFiveReservationTest, B2BPhaseFourInventoryTest.
- B2BPhaseThreeProductVariantTest, AuthenticationHardeningTest.
- B2BPhaseTwoAuthTest, B2BPhaseOneDomainTest, MessageCenterTest.
- Existing optional MySQL gates retain their opt-in behavior and were not weakened.

Phase 8 coverage includes real commits (not synthetic RefreshDatabase commits), every lifecycle replay, listener redelivery and unique constraint, outer rollback and delayed commit, inventory validation rollback, delivery failure isolation/logging, cancellation release, multi-row expiry with a failing row and recovery, ownership and reservation links, read idempotency, bulk update count, pagination/query-growth bounds, malformed JSON, Arabic/English rendering, middleware, actor independence, Order isolation, and additive migration up/down preserving legacy data.

`php artisan migrate:fresh --env=testing --force` passed with explicitly scoped DB_CONNECTION=sqlite and DB_DATABASE=:memory:; no development/production database was refreshed. Pint on changed PHP files, Blade compilation, both translation JSON parsing/key alignment, and git diff --check passed.

The new tests use fresh isolated schemas with real transactions. The legacy DatabaseMigrations teardown path exposes pre-existing SQLite down-migration errors; the tests avoid invoking unrelated historical down migrations and explicitly exercise the Phase 8 down migration instead.

`ProductControllerTest` was run separately: the known first 302-versus-200 failure persists, followed by seven existing active-transaction errors. No B2C routing/test changes were made. Existing PHPUnit doc-comment deprecation warnings remain.

**MySQL release gate is NOT satisfied by SQLite tests.** Run unchanged B2BPhaseFiveMySqlConcurrencyTest against an explicitly disposable MySQL/InnoDB database with B2B_MYSQL_CONCURRENCY_DATABASE ending in `_test`/`_testing` and B2B_MYSQL_CONCURRENCY_ALLOW_FRESH=1. The operator must provide that database. No concurrency guarantees are inferred from notification tests.

## Recommended next phase

Plan durable delivery/outbox and independent channel adapters, then implement only the next separately approved channel scope. Complete the MySQL release gate before production rollout. Phase 8 stops here.

## File manifest

Created:

- `app/Events/B2B/ReservationCancelled.php`
- `app/Events/B2B/ReservationCompleted.php`
- `app/Events/B2B/ReservationConfirmed.php`
- `app/Events/B2B/ReservationCreated.php`
- `app/Events/B2B/ReservationExpired.php`
- `app/Events/B2B/ReservationLifecycleEvent.php`
- `app/Events/B2B/ReservationPreparing.php`
- `app/Events/B2B/ReservationShipped.php`
- `app/Http/Controllers/Reseller/ResellerNotificationController.php`
- `app/Listeners/PersistResellerReservationNotification.php`
- `app/Models/ResellerNotification.php`
- `app/Repositories/ResellerNotificationRepository.php`
- `app/Services/ResellerNotificationService.php`
- `database/migrations/2026_09_26_000001_add_reservation_event_key_to_notifications.php`
- `resources/views/reseller/notifications.blade.php`
- `tests/Feature/B2BPhaseEightNotificationTest.php`
- `docs/B2B_PHASE8.md`

Modified:

- `app/Providers/AppServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `app/Services/ReservationService.php`
- `lang/ar.json`
- `lang/en.json`
- `resources/views/reseller/layout.blade.php`
- `routes/web.php`
