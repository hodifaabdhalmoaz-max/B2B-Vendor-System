# Phase 7 — Admin B2B reservation operations

Implemented from verified Phase 6 commit
`04eaa5190a42219e846617fc387d8f715f3ce675` on
`codex/phase-7-admin-reservations`. The final implementation commit is reported
in the task handoff; `git log -1 --format=%H` resolves it on this branch.

Administrators can locate B2B reservations, inspect historical snapshots and
reseller context, perform explicit lifecycle operations, and review the associated
inventory ledger. B2C Orders retain their separate controller, routes and records.

## Files created

- `app/Http/Controllers/Admin/B2BReservationController.php`
- `app/Http/Requests/Admin/FilterB2BReservationsRequest.php`
- `app/Http/Middleware/AdminReservationTranslations.php`
- `app/Repositories/AdminReservationRepository.php`
- `app/Services/AdminReservationService.php`
- `resources/views/admin/b2b/reservations/index.blade.php`
- `resources/views/admin/b2b/reservations/show.blade.php`
- `resources/views/admin/b2b/reservations/_feedback.blade.php`
- `resources/views/admin/b2b/reservations/_status.blade.php`
- `resources/views/admin/b2b/reservations/_styles.blade.php`
- `tests/Feature/B2BPhaseSevenAdminReservationTest.php`
- `docs/B2B_PHASE7.md`

## Files modified

- `routes/web.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/admin/resellers/index.blade.php`
- `resources/views/admin/resellers/edit.blade.php`
- `lang/ar.json`
- `lang/en.json`

No migrations or database changes were needed. No changes to ReservationService,
InventoryService, ProductVariant inventory architecture, the reseller cart,
the legacy Order domain, or the MySQL concurrency test.

## Routes and authorization

Every route retains the existing `web`, `auth`, `AuthAdmin`, and
`smart.throttle:admin` protection. Only active ADM accounts are accepted;
USR, RES and inactive ADM accounts are denied. Guests are redirected to login.
The web middleware provides CSRF protection for all six POST operations.

The URL prefix is `/admin/b2b/reservations`; names begin
`admin.b2b.reservations.`.

| HTTP | URL suffix | Route name / controller action | Domain method |
|---|---|---|---|
| GET | `/` | `index` | Read-only query service |
| GET | `/{reservation}` | `show` | Read-only query service |
| POST | `/{reservation}/confirm` | `confirm` | `confirm` |
| POST | `/{reservation}/preparing` | `preparing` | `markPreparing` |
| POST | `/{reservation}/ship` | `ship` | `markShipped` |
| POST | `/{reservation}/complete` | `complete` | `complete` |
| POST | `/{reservation}/cancel` | `cancel` | `cancel` |
| POST | `/{reservation}/expire` | `expire` | `expire` |

There is no generic status endpoint, item editing endpoint, delete route, or
inventory write control. GET requests never expire reservations or change stock.

## Controller, query service and repository

`B2BReservationController` validates list inputs with a FormRequest, calls the
read-only `AdminReservationService`, and renders views. That service coordinates
the repository queries, status labels and state-appropriate button presentation.
`AdminReservationRepository` owns all operational database queries.

Each lifecycle method in the controller explicitly calls the matching existing
ReservationService method with the Reservation and authenticated admin user ID.
The private `perform` helper only standardizes redirect/error presentation. It
does not select a domain method or accept a requested target status.

Domain ValidationExceptions return their field errors on the detail page,
including requests without a Referer. QueryExceptions are reported server-side
and replaced with a generic retry message, without SQL in the response. Manual
expiry returning false produces a clear not-due/already-processed message.

## Filtering and list performance

The list displays reservation number, reseller name/username, business name,
creation time, status, item count, exact total, expiration and a detail link.
It uses 20-row pagination and preserves query parameters, with a stable
`created_at DESC, id DESC` order.

Supported filters:

- All seven Reservation status constants, plus `all`.
- A reseller profile ID, also preselected by the reseller administration links.
- Search across reservation number, user name, username, mobile, business name
  and WhatsApp. All LIKE values are bound, and all search OR conditions are
  grouped inside the remaining AND filters. LIKE wildcards retain their normal
  SQL behavior; no raw user input is inserted into SQL syntax.
- Validated `Y-m-d` creation dates, including either endpoint independently.
  Start is inclusive at midnight and end is inclusive at the end of that day;
  reversed ranges are rejected.
- Overdue only: pending_review, non-null expires_at, expires_at <= now.

Dates use the configured application timezone, shown on both pages. Status cards
use **one grouped status query**, fill missing states with zero, and sum the
groups for `all`. They respect search/reseller/date/overdue filters and ignore
only the selected status. Each card changes the status and resets pagination.
No reporting cache or reporting subsystem was added.

The list eager loads resellerProfile.user with only its required user columns,
uses `withCount('reservationItems')`, and loads only item ID, reservation ID,
quantity and unit_price for the current page. This page-bounded item load lets
the existing Reservation.total and ReservationItem.line_total accessors use
DecimalMoney integer cents; SQL floating-point aggregates and per-row total
queries are avoided. The item count does not depend on loading item records.

## Details, snapshots and inventory ledger

Detail eager loading is intentionally limited to resellerProfile.user and
reservationItems. Historical rows use product_name_snapshot, sku_snapshot,
variant_snapshot color/size names, quantity, unit_price and line_total. Current
Product, Color, Size and pricing relationships are unnecessary for historical
rendering and are not loaded. The page also shows the reservation timestamps,
reseller note and total.

Reseller context includes name, username, mobile, business name, WhatsApp,
governorate, group, account status, profile status, reservation permission and
timeout. Passwords, tokens, request fingerprints and idempotency keys are not
rendered. A profile edit link reuses the existing reseller administration.

The ledger retrieves rows where **both** reference_type = `reservation` and
reference_id = the current reservation ID. One ledger query plus two batched
relationship queries load the current variant SKU and actor name. Rows show
movement type, quantity, stock/reserved deltas, post-movement balances and time.
The current ledger SKU is explicitly distinguished from historical item SKUs.
There are no movement edit/delete controls or new movement types.

Current variant inventory is optional in the specification and was not added.
The existing ledger provides operational audit visibility without another
source of pricing or stock information on the historical item table.

## Lifecycle behavior and stale requests

| Current state | Visible actions |
|---|---|
| pending_review | Confirm, cancel, expire when due |
| confirmed | Preparing, cancel |
| preparing | Ship, cancel |
| shipped | Complete |
| completed / cancelled / expired | None |

The UI only presents available actions. ReservationService locks and rereads
current database state, so stale submissions cannot override newer state.
Confirmation, preparation and shipment create no inventory movement. Completion
consumes held inventory exactly once per item. Cancellation and due expiry
release held inventory through the existing domain service only.

**Existing idempotency is preserved:** repeated confirm/preparing/ship/complete
requests already at their target state are harmless. A repeated cancel request
on an already-cancelled reservation also replays without a second release; it
does not perform a new cancellation. No cancel button appears for that state.
This follows the existing service contract rather than introducing a separate
controller state check that could race. Cancellation from shipped/completed/
expired is rejected. Manual expiry cannot force early expiry; already-processed
requests receive clear feedback without a second release.

## Admin navigation, localization and responsive layout

The existing B2C Orders menu remains. A separate B2B Reservations entry points
to the new area. Reseller index/edit pages link to the profile-filtered list
using relationships already loaded by those controllers, with no extra per-row
query or duplicated reseller management.

Arabic/English JSON dictionaries have matching keys. This application uses
resources/lang by default, so request-scoped AdminReservationTranslations loads
the root JSON dictionaries for the new reservation area. Other admin routes
receive **only the new B2B navigation label**, preserving their original
translation behavior. The original translator is restored after each request;
no reseller authorization or translation middleware is reused.

Views reuse the existing RTL admin layout, Cairo font, Bootstrap grid and
tf-button/wg-box styles. Filters and action buttons wrap on narrow screens;
tables scroll within focusable containers. Scoped styles wrap long identifiers
and notes, preserve minimum button sizes and add visible keyboard focus.
No frontend framework or asset build dependency was introduced.

## Verification

- Phase 7: 18 feature tests pass, including every route's authorization,
  real CSRF rejection with the test bypass disabled, grouped filters, inclusive
  date boundaries, pagination, grouped counts, snapshots after product/SKU/
  color/size renames, exact totals, state-specific buttons, lifecycle replay,
  multi-item completion, cancellation, due expiry, stale submissions, actor
  attribution, ledger scope, SQL error redaction, reseller links, localization,
  B2C isolation and successful rendering of the existing B2C Orders index.
- Query-growth tests render list/detail with increasing reservations and items.
  Queries remain bounded at no more than 14 per response and do not increase
  by more than one from the smaller fixtures.
- Final combined regression run: **148 passed, 2 skipped, 1,523 assertions**.
  The Phase 7 file separately passes **18 tests, 391 assertions**.
  The combined run covers Phase 1–7,
  AuthenticationHardeningTest, ProductVariantSelectionTest, ProductServiceTest,
  Unit/ProductTest, Api/ProductApiTest and the opt-in MySQL test.
- `php artisan migrate:fresh --env=testing --force` passes using explicitly
  overridden SQLite `:memory:`, array cache/session and a separate absent
  config-cache path. No persistent development/production database was reset.
- Changed PHP files pass Pint; Blade compilation, aligned JSON validation and
  `git diff --check` pass.

### MySQL concurrency gate

B2BPhaseFiveMySqlConcurrencyTest is unchanged, including the use of two distinct
reseller profiles and all destructive opt-in safeguards. No disposable MySQL
database/opt-in is configured, so it is explicitly skipped. The second skip is
the existing Phase 4 SQLite concurrency placeholder. SQLite functional tests
do not prove InnoDB row-lock behavior.

### Known legacy failure

ProductControllerTest was run separately. The first test still expects HTTP 200
and receives 302; its seven remaining tests then fail with an already-active
transaction. This existing public B2C failure was not changed. Legacy PHPUnit
doc-comment metadata deprecation warnings also remain.

## Remaining technical debt and Phase 8 recommendation

- Run the preserved opt-in MySQL concurrency release gate against a disposable
  InnoDB database before production release.
- Resolve the existing ProductControllerTest redirect/transaction issue in a
  separate B2C task, and modernize legacy PHPUnit metadata separately.
- A real browser/device visual acceptance pass remains recommended; this run
  verifies rendered feature responses, responsive markup and Blade compilation.
- Leading-wildcard identity search and filtered aggregate counts should be
  profiled with production-scale data before adding indexes or search tooling.
  Reseller selection currently uses a profile ID or the reseller index/edit
  links; a searchable selector is a possible later convenience.
- Phase 8 should focus on reservation lifecycle events after transaction commit
  and notifications with delivery idempotency, retries and failure visibility,
  while preserving the current domain locking and inventory semantics.

Phase 7 stops here. No notification delivery, WhatsApp API, payments, invoices,
B2B checkout, shipping-provider integration, public/mobile API, inventory edits
or reservation item edits are implemented.
