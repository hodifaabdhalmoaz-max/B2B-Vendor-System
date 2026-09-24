# Phase 6 — Authenticated reseller portal

Implemented on the Phase 5 baseline `3a8223f0e7bc5953d76c1d1510ef2fa083d2b371`.
Scope ends at the reseller portal. No admin reservation management, B2B API,
payments, checkout conversion, shipping, invoices, notifications, or promotion
engine were added. InventoryService and ReservationService are unchanged.

## Files created

- `app/Http/Controllers/Reseller/CatalogController.php`
- `app/Http/Controllers/Reseller/ReservationCartController.php`
- `app/Http/Controllers/Reseller/ReservationController.php`
- `app/Http/Controllers/Reseller/WishlistController.php`
- `app/Http/Middleware/ResellerTranslations.php`
- `app/Http/Requests/Reseller/StoreReservationRequest.php`
- `app/Repositories/ResellerPortalRepository.php`
- `app/Services/ResellerCatalogService.php`
- `app/Services/ResellerPortalService.php`
- `app/Services/ResellerProductMediaService.php`
- `app/Services/ResellerReservationCartService.php`
- `resources/views/reseller/layout.blade.php`
- `resources/views/reseller/catalog.blade.php`
- `resources/views/reseller/product.blade.php`
- `resources/views/reseller/cart.blade.php`
- `resources/views/reseller/reservations.blade.php`
- `resources/views/reseller/reservation.blade.php`
- `resources/views/reseller/wishlist.blade.php`
- `resources/views/reseller/partials/product-card.blade.php`
- `resources/views/reseller/partials/wishlist-button.blade.php`
- `resources/views/reseller/partials/reservation-card.blade.php`
- `resources/views/reseller/partials/status.blade.php`
- `public/assets/css/reseller.css`
- `public/assets/js/reseller.js`
- `tests/Feature/B2BPhaseSixPortalTest.php`
- `docs/B2B_PHASE6.md`

## Files modified

- `app/Http/Controllers/Reseller/DashboardController.php`
- `resources/views/reseller/index.blade.php`
- `routes/web.php`
- `lang/ar.json`
- `lang/en.json`
- `tests/Feature/B2BPhaseFiveMySqlConcurrencyTest.php`

No migrations. The existing wishlist unique constraint on `(user_id, product_id)`
already provides the required protection; no deduplication or data deletion is needed.

## Routes and access

All names below start with `reseller.` and URLs with `/reseller`.
Portal routes retain `auth`, `reseller`, `force.password.change`, and
`smart.throttle:user_dashboard`. Search also uses `smart.throttle:search`.
The existing password-change endpoints remain outside the forced-change guard.
Normal users, admins, inactive users, missing profiles, and inactive profiles cannot
enter the portal. ReservationService still checks reservation eligibility at commit time.

| Method | Suffix | Route name |
|---|---|---|
| GET | `/` | `index` |
| GET | `/catalog` | `catalog` |
| GET | `/search` | `search` |
| GET | `/products/{product}` | `products.show` |
| GET | `/products/{product}/images/{image}` | `products.images.download` |
| GET | `/cart` | `cart.index` |
| POST | `/cart` | `cart.store` |
| PATCH | `/cart/{variant}` | `cart.update` |
| DELETE | `/cart/{variant}` | `cart.destroy` |
| DELETE | `/cart` | `cart.clear` |
| POST | `/reservations` | `reservations.store` |
| GET | `/reservations` | `reservations.index` |
| GET | `/reservations/{reservation}` | `reservations.show` |
| POST | `/reservations/{reservation}/cancel` | `reservations.cancel` |
| GET | `/wishlist` | `wishlist.index` |
| POST | `/wishlist/{product}` | `wishlist.store` |
| DELETE | `/wishlist/{product}` | `wishlist.destroy` |

## Architecture and catalog

Focused controllers validate inputs, call services, and return responses.
ResellerPortalRepository owns queries and wishlist persistence. CatalogService
prepares products and DecimalMoney price ranges. PortalService coordinates dashboard,
history, wishlist, domain submission, and the stricter reseller cancellation policy.
The cart and product-media concerns have separate services.

Catalog queries require at least one active ProductVariant. Products with active but
sold-out variants remain visible, with an explicit sold-out label. Products without
active variants are excluded. Search groups bound conditions for product name,
base SKU, active variant SKU, and the primary category name. Filters cover primary
category, available stock, and offers (`is_offer` or a positive sale price below the
regular price). Catalog and wishlist use 12-product pages; history uses 12 reservations.

Catalog eager loads category and active variants with InventoryItem, Color, and Size.
Each variant is given its already-loaded Product relation before effective pricing.
A correlated wishlist subquery gives per-card saved state without loading every
wishlist ID. Detail additionally eager loads color images and their colors.
The feature suite verifies that a 12-product catalog uses at most nine queries.

Availability comes exclusively from `InventoryItem.available_quantity`.
The in-stock SQL predicate is `stock_on_hand > reserved_quantity` on an active
variant. Legacy product/color/size quantities and stock_status are not consulted.
Price ranges and cart totals use integer cents through DecimalMoney, based on the
existing ProductVariant effective price. No floating-point totals are introduced.

Dashboard sections show six recent products, six offers, six most-requested products,
five owned recent reservations, and grouped owned status counts. Demand ranking sums
ReservationItem quantities through ProductVariant/Product, using **pending_review,
confirmed, preparing, shipped, and completed** only. Cancelled and expired demand is
excluded. No B2C Order data is involved. Availability is read fresh rather than cached.

## Cart, exact variants, and submission

Cart state is `b2b_reservation_cart.{reseller_profile_id}`. Its `items` member maps
exact ProductVariant IDs to integer requested quantities. The sibling
`idempotency_key` is form metadata. Names, SKUs, prices, color, size, and availability
are never trusted session attributes. Cart display bulk reloads live relationships.
Each exact variant has its own labeled quantity/add form; it submits its actual ID.

Add increments an existing line; update replaces its quantity. Positive integer
quantities, active variants, present inventory, and current availability are checked.
The cart permits up to 100 distinct variants. Editing never holds or releases stock.
Both resellers can add the final unit; the domain resolves competition at submission.
Stale, inactive, deleted, or understocked lines remain visible with their original
requested quantities and a warning. Current-price totals are explicitly a preview.

Submission sequence:

1. Preparing the cart form creates a UUID once in the profile-scoped session.
   GET refreshes and failed submissions retain it. Explicit cart edits reset it
   for the next form intent. Validation errors preserve valid caller keys and notes;
   malformed array fields cannot break the redisplayed form.
2. The form posts the key, exact variant IDs, quantities, and optional notes.
   StoreReservationRequest rejects protected top-level fields and unexpected line
   keys, including prices. It normalizes validated numeric strings to strict integers.
3. PortalService calls ReservationService::create with the authenticated profile.
   The domain atomically checks eligibility/stock and produces immutable snapshots.
4. Only after success, clear that profile's cart and redirect to the reservation detail.
   Any failure preserves the cart; the next render reloads current availability.

The POST carries its own line intent, so an identical key/body can replay after the
cart is cleared. It returns the original reservation without additional holds or
items. Conflicting payloads or another profile's key are rejected by the domain.
No legacy CartService, Shoppingcart, OrderService, coupons, taxes, or checkout state
is involved. Controllers/services do not directly write inventory balances, ledger
movements, or reservation status.

## History, ownership, and cancellation

Every history/detail lookup includes the authenticated reseller_profile_id.
Foreign detail/cancel URLs return 404. Lists eager load items for totals and counts.
Details render product_name_snapshot, sku_snapshot, variant_snapshot color/size,
quantity, unit_price, and line_total. Product renames and price changes do not rewrite
history. Pending reservations show their server expiration deadline and timezone.
There is no client-side expiration mutation or stock release.

Self-cancellation is allowed only for pending_review. PortalService starts a
transaction, locks the owned reservation, checks pending status, then calls
ReservationService::cancel while retaining that lock. This prevents a concurrent
confirmation from bypassing the reseller policy. Repeated cancellation is rejected
without a second release. All other statuses hide and reject the action. Broader
admin/system transitions in ReservationService are unchanged.

## Wishlist, media, localization, and mobile UX

Wishlist actions use the existing product-level model with authenticated user_id.
firstOrCreate plus the existing unique index makes repeated/concurrent adds safe.
Removal is scoped to that user's product entry. No stock or B2C wishlist cart changes
occur. Saved products without an active variant are hidden until eligible again;
their database wishlist entries are retained.

Main/gallery images and existing colorImages reuse their stored files. Downloads
accept a product ID and numeric image index, never a path. The server constructs an
allowlist, limits file extensions/names, and checks the resolved real path remains
inside public/uploads/products, including for symlinks. Missing/invalid images are 404.
Copy description converts marketing HTML to readable plain text, removes script/style
content, uses Clipboard API, and provides a selectable textarea fallback and feedback.

The isolated layout reuses bundled Bootstrap CSS, with cream/coral/teal styling,
44px minimum controls, responsive cards instead of wide tables, lazy images,
persistent cart access, visible focus indicators, and RTL document direction.
There is no new frontend framework. Expiration uses a static server deadline rather
than a potentially misleading client countdown.

Arabic/English JSON dictionaries have 166 matching keys. This legacy installation
uses resources/lang by default. ResellerTranslations supplies a request-scoped
translator with an additional root JSON path, then restores the original translator.
Existing public/admin translations and PHP dictionaries retain their prior behavior.
Locale rendering is covered explicitly in both languages.

## Verification

- Phase 6: **20 feature tests pass**, including access/middleware, inventory-source
  isolation, sold-out handling, cart CRUD/no holds/profile isolation, atomic success
  and rollback, stable keys, replay after clearing, tampered fields, eligibility,
  foreign-key replay, IDOR, historical snapshots, cancellation, wishlist, search,
  filters, price ranges, pagination, demand ranking, bounded query count, media path
  restrictions, malformed old input, and Arabic/English rendering.
- Combined required regression run: **130 passed, 2 skipped, 1,152 assertions**.
  Includes Phase 1–5, AuthenticationHardeningTest, Phase 6, ProductVariantSelectionTest,
  ProductServiceTest, Unit/ProductTest, Api/ProductApiTest, and the opt-in Phase 5 race test.
- ProductControllerTest was also run separately: its known first assertion expects
  200 but receives 302; the seven following tests report an already-active transaction.
  This legacy B2C failure is retained as explicitly requested. No standalone wishlist
  test file exists; Phase 6 provides reseller wishlist coverage.
- `php artisan migrate:fresh --env=testing --force` passed with explicitly overridden
  SQLite `:memory:` connection, array cache, and array sessions. No development or
  production database was reset.
- Changed PHP files pass Pint; Blade compilation and `git diff --check` pass.
  Translation JSON parses with identical key sets and existing translations retained.
- Source review confirms portal inventory changes flow only through ReservationService.

### MySQL concurrency release gate

B2BPhaseFiveMySqlConcurrencyTest remains opt-in and destructive only with both
`B2B_MYSQL_CONCURRENCY_DATABASE` (ending in `_test`/`_testing`) and
`B2B_MYSQL_CONCURRENCY_ALLOW_FRESH=1`. Reservation-vs-reservation now uses **two
different active reseller profiles/users**, avoiding false confidence from a shared
profile lock. Same-key replay intentionally still uses one profile. Inventory and
confirm-vs-expire races remain intact.

No disposable MySQL database/opt-in was configured, so the Phase 5 test was honestly
skipped. The second skip is the pre-existing Phase 4 SQLite concurrency placeholder.
SQLite functional checks do not establish InnoDB race correctness.

## Remaining debt and Phase 7 recommendations

- Run the preserved MySQL release gate against a disposable InnoDB database before
  production release. This environment has not verified real concurrent row locks.
- Resolve the existing ProductControllerTest redirect/transaction problem separately.
  Legacy test doc-comment deprecation warnings also remain.
- Catalog eager loading is page-bounded, but each product's active variant list and
  aggregate demand query should be profiled with production-scale data before adding
  targeted caching/indexes. Session cart intent is capped at 100 distinct lines.
- A browser/device visual acceptance pass is still recommended; verification here
  includes rendered feature responses and Blade compilation, not device screenshots.
- Phase 7 can add authorized admin reservation management using the existing domain
  transitions, with policies and real MySQL cancellation/confirmation race coverage.
  Keep admin-only data out of the reseller views and keep inventory mutations inside
  the established services. No Phase 7 functionality is included in this change.
