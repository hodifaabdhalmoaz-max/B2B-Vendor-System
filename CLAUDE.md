# Project Brain: Dunya-Alatfaal-Shop

This project is a premium e-commerce storefront for kids' products, designed to provide a playful, secure, and fast browsing experience.

## Stack
- **Core Framework**: Laravel 11 & PHP 8.2+
- **Database**: MySQL (Eloquent ORM) — monetary columns use `decimal(12,2)` precision
- **Authentication**: Cookie-based sessions (Web) + Laravel Sanctum tokens (API). JWT is **not** used; Sanctum is the standard for this project
- **Frontend**: Blade Templating + SASS/SCSS + Vanilla JS (bundled via Vite)
- **Caching**: Redis (recommended for production) / File cache (local dev), abstracted via `CacheService`
- **Sessions**: Database driver (local dev) / Redis (production) — set `SESSION_DRIVER=redis` in production `.env`
- **Commerce Tools**: `surfsidemedia/shoppingcart` (Shopping Cart) & Intervention Image (Image Processing)
- **Production Server**: Hostinger Shared Hosting (Production Environment)

## Commands
- **Local Dev Server**: `php artisan serve`
- **Asset Watcher**: `npm run dev`
- **Asset Compiler**: `npm run build`
- **Run Test Suite**: `php artisan test`
- **Lint & Format Code**: `./vendor/bin/pint` (run without arguments to format, or `--test` to check)
- **Static Analysis**: `./vendor/bin/phpstan analyse`
- **Clear Cache & Config**: `php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear`
- **Database Migrations**: `php artisan migrate` (or `php artisan migrate --force` in production)
- **Weekly Maintenance**: `php artisan maintenance:weekly` — optimizes tables, prunes old logs (`user_activities` >90d, `error_logs` >30d, `security_logs` >180d, `product_views` >90d), and cleans temp files
- **Performance Optimization**: `php artisan optimize:performance` — warms caches, stores config/routes, and optimizes autoloader
- **System Health Check**: `php artisan system:health-check` — verifies database connectivity and system status

## Conventions & Rules
- **Architecture**: Always follow the **Controller-Service-Repository** pattern.
  - Keep Controllers clean: they should only validate request inputs, invoke Services, and return Views/JSON.
  - Place core business operations (e.g. order creation, notifications) inside **Service** classes.
  - Interface with the database via **Repositories** to keep queries organized and reusable.
- **Authentication Strategy**:
  - Web routes use Laravel's built-in cookie/session authentication via `laravel/ui`.
  - API routes (`/api/v1/`) use **Laravel Sanctum** for token-based authentication. Do **not** introduce JWT packages.
  - Protected API routes use `auth:sanctum` middleware; public catalog routes remain unauthenticated.
- **Security & Rate Limiting**:
  - Restrict access to admin directories with the `AuthAdmin` middleware, and user dashboards with `auth`.
  - All public, cart, search, and checkout routes must carry a `smart.throttle:[profile]` middleware guard.
  - Never allow raw user-provided input in SQL query strings; always bind parameters.
- **Database & Models**:
  - All models must declare `$fillable` explicitly — never use `$guarded = []`.
  - All monetary columns must use `decimal(12,2)` precision to support high-value currencies.
  - Cast prices, dates, booleans, and JSON fields to native PHP types via model `casts()`.
  - `Model::preventLazyLoading()` is enabled in non-production environments to catch N+1 queries early.
  - Logging tables are automatically pruned by the weekly maintenance task to prevent database bloat.
  - See `.claude/rules/database.md` for the complete database ruleset.
- **Performance**:
  - Eager-load model relationships (e.g. `category`, `brand`) using `with()` to prevent **N+1 query loops** on pages rendering lists.
  - Cache heavy operations (home sliders, stats aggregates, filtering arrays) via `CacheService`.
- **Media Uploads**:
  - Standardize image processing: all uploads must route through `ImageService` to automatically convert files to `.webp` format and generate thumbnails.
- **Localization**:
  - Make all user-facing copy translatable by wrapping text in `__('...')` or `@lang('...')`.
  - Keep both English and Arabic translations synchronized inside `lang/en/` and `lang/ar/` folders.
## Recent Fixes (2026-06-04)

### Security & Performance Audit Fixes
- **C1 (Production Debug Mode)**: Changed `APP_DEBUG=true` to `APP_DEBUG=false` in `.env.production` to prevent details of system errors and credentials from leaking.
- **C3 (Null Dereference Protection)**: Updated all unsafe database updates/reads in `AdminController.php` (such as categories, brands, products, slides, coupons, colors, sizes) from `find($id)` to `findOrFail($id)` to return 404 instead of throwing 500 error crashes.
- **C4 & C5 (Cart & Wishlist Input Sanitization)**: Rewrote `add_to_cart` in `CartController.php` and `add_to_wishlist` in `WishlistController.php` to validate incoming parameters, fetching product name and price directly from the database instead of trusting user input (preventing price manipulation attacks).
- **C6 (Insecure Route Removal)**: Removed `/admin/test-revenue` test endpoint from `routes/web.php` which exposed analytics data.
- **H1 (Security Configuration Consolidation)**: Merged duplicate arrays (`password`, `2fa`, `headers`) in `config/security.php` to prevent newer config arrays from overwriting original settings.
- **H2 (Route Normalization)**: Corrected malformed coupon URL routing patterns (`/admin/coupon/{id}edit` and `/admin/coupon/{id}delete` to `/admin/coupon/{id}/edit` and `/admin/coupon/{id}/delete`).
- **H3 (Wishlist Database Cleanup)**: Fixed `empty_wishlist` in `WishlistController.php` to prune wishlist entries from the database when a user clears their wishlist.
- **M5 (Secure Upload File Naming)**: Replaced timestamp-based file uploads in `AdminController.php` (for brands, categories, products, slides) with `Str::uuid()` to prevent filename collision issues.
- **M8 (Carbon Mutability Bug)**: Replaced mutated Carbon dates in `getDateRange` of `AdminController.php` with `$now->copy()` to ensure accurate dashboard sales reporting intervals.

### Bug 1 — Admin Products Page: `LazyLoadingViolationException` on `category` & `brand`
- **File**: `app/Http/Controllers/AdminController.php` — `products()` method
- **Root Cause**: The query fetched products without eager loading `category` and `brand`, but the view `admin/products.blade.php` (lines 75–76) accessed `$product->category?->name` and `$product->brand?->name`, triggering lazy loading which is prohibited in dev via `Model::preventLazyLoading(!app()->isProduction())` in `AppServiceProvider`.
- **Fix**: Changed `Product::orderBy(...)->paginate(10)` to `Product::with(['category', 'brand'])->orderBy(...)->paginate(10)`.
- **Impact**: Eliminates the crash in dev AND the silent N+1 query performance hit in production.

### Bug 2 — Shop Page (Storefront): `LazyLoadingViolationException` on `products` relation
- **File 1**: `app/Services/SearchService.php` — `getSearchFilters()` method
- **File 2**: `resources/views/shop.blade.php` — category and brand sidebar filter (lines 749, 852)
- **Root Cause**: The shop sidebar displayed product counts per category and brand using `$category->products->count()` and `$brand->products->count()`. The `categories` and `brands` collections passed from `SearchService::getSearchFilters()` were queried without `withCount('products')`, causing the view to trigger lazy loading on the `products` relation.
- **Fix**:
  1. In `SearchService::getSearchFilters()` — added `->withCount('products')` to both Category and Brand queries so the count is computed by the DB in a single aggregated query.
  2. In `shop.blade.php` — replaced `$category->products->count()` with `$category->products_count` and `$brand->products->count()` with `$brand->products_count` to use the eagerly computed attribute instead of triggering a relation load.
- **Impact**: Eliminates crash in dev, removes N+1 queries in production, and improves page load performance for the shop page.

### Bug 3 — Wishlist Page: `Undefined variable $wishlistItems`
- **File**: `resources/views/wishlist.blade.php` and `resources/views/user/wishlist.blade.php`
- **Root Cause**: The view expected a variable named `$wishlistItems`, but `WishlistController::index()` passed `$items` (from `Cart::instance('wishlist')->content()`). There is also a separate user wishlist route (`/user/wishlist`) handled by `UserController::wishlist()` which correctly passes `$wishlistItems` from the DB model.
- **Status**: Documented. The `WishlistController::index()` uses the Cart session (variable name `$items`), while `UserController::wishlist()` uses the DB Wishlist model (variable name `$wishlistItems`). Views must match the variable name of their respective controller.

### General Rules Reinforced
- Always use `with(['relation'])` or `withCount('relation')` when a view renders relational data inside a loop.
- Never call `$model->relation->count()` in Blade templates unless the relation was eagerly loaded — use `$model->relation_count` from `withCount()` instead.
- Run `php artisan cache:clear` after changes to `SearchService` since filter results are cached for 1800 seconds.

## Session Fixes (2026-06-03)

### Fix 1 — `AuthController::logout()`: IDE Static Analysis Warnings

- **File**: `app/Http/Controllers/Api/AuthController.php` — `logout()` method
- **Root Cause 1**: `auth()->user()` returns `Authenticatable|null`. The `Authenticatable` interface does not declare `tokens()` (provided by Sanctum's `HasApiTokens` trait on `User`), causing IDEs to flag the call as undefined. Calling it on a potential `null` also risks a runtime `Call to a member function tokens() on null`.
- **Root Cause 2**: `auth()->id()` was flagged because IDEs resolve `auth()` to `Illuminate\Contracts\Auth\Factory`, which has no `id()` method.
- **Fix**:
  1. Moved user retrieval to the top of the method using `$request->user()` (the established pattern across the project) with a `/** @var \App\Models\User|null $user */` docblock.
  2. Replaced `auth()->id()` in the Audit Log call with `$user?->id` (null-safe operator).
  3. Wrapped `$user->tokens()->delete()` in `if ($user)` to guard against null.
- **Pattern to follow**: Always use `$request->user()` in controller methods to retrieve the authenticated user. Always guard with `if ($user)` or `?->` before chaining methods on potentially null results.

### Fix 2 — `.gitignore`: IDE Helper Files Not Excluded

- **File**: `.gitignore`
- **Root Cause**: The `barryvdh/laravel-ide-helper` package generates `_ide_helper.php`, `_ide_helper_models.php`, and `.phpstorm.meta.php` locally. These were not listed in `.gitignore`, causing them to appear as untracked files polluting the working tree.
- **Fix**: Added the three IDE helper file patterns to the `# IDE Files` section of `.gitignore`.
- **Rule**: IDE-generated helper files must never be committed — they are environment-specific and regenerated on demand via `php artisan ide-helper:generate`.

## Session Audit Implementation (2026-06-04 - Part 2)

### Security Hardening
- **API Cart Routes Security**: Wrapped all `/cart` API endpoints in `routes/api.php` with `auth:sanctum` to prevent unauthenticated/anonymous cart manipulation.
- **Address Selection Protection**: Added `findOrFail` validation to `UserController::addressSetDefault()` to verify that the address exists and belongs to the authenticated user before modifying defaults, preventing silent state manipulation.
- **Secure Profile Photo Uploads**: Replaced predictable `time()` prefix naming convention in `UserController::profileUpdate()` with `Str::uuid()`.
- **Deduplicated Configurations**: Removed the duplicate `'2fa'` array from `config/security.php` to ensure the canonical `'two_factor'` settings are always used.

### Performance Optimization
- **Dashboard Aggregate Query**: Rewrote `AdminController::index()` statistics retrieval to use a single `selectRaw` query on `orders`, reducing DB queries from 8 down to 1 when displaying admin metrics.
- **OPcache Protection**: Deleted all occurrences of `opcache_reset()` in `AdminController.php` upload routes and the `clearOpcache()` helper in `ImageService.php` to prevent site-wide cache reset overhead on every image upload.
- **Global View Composer Reduction**: Optimized `SecurityServiceProvider` view composer to share only `isSecureConnection`, removing redundant properties already natively available in Blade views.
- **Dead Code Pruning**: Removed unused, high-query dashboard rendering helpers `calculateRevenueData()` and `getChartData()` in `AdminController`.

### Architectural Refactoring & Safety
- **Service Dependency Injection**: Standardized image handling by injecting `ImageService` into `AdminController` via constructor DI, replacing duplicate helper methods (`Generate*Image`) with standard service method calls.
- **Order Soft Deletes**: Integrated `SoftDeletes` in the `Order` model and database migration to safeguard order records against permanent deletion.
- **Newsletter Subscription Persistence**: Implemented database persistence for newsletter subscriptions in `HomeController::newsletter_subscribe()` using the `NewsletterSubscriber` model instead of returning a stub response.
- **Repository Cleanliness**: Relocated 21 markdown documentation files to `/docs/` and deleted legacy test archives (`test-pint.zip` and `test_webp.php`) from the root.


