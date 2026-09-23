---
paths:
  - "app/Http/Controllers/*.php"
  - "app/Http/Controllers/Admin/**/*.php"
  - "app/Services/**/*.php"
  - "app/Repositories/**/*.php"
  - "app/Http/Middleware/**/*.php"
  - "app/Providers/**/*.php"
---

# Backend Rules

- Follow Controller-Service-Repository pattern: controllers handle validation/delegation only, Services hold business logic, Repositories abstract queries.
- Eager-load relations (`category`, `brand`, `colors`, `sizes`) with `with()` to prevent N+1 queries. `Model::preventLazyLoading()` is enabled in non-production via `AppServiceProvider`.
- Admin routes use `['auth', AuthAdmin::class]`; user routes use `['auth']`; scope queries with `Auth::id()` to prevent IDOR.
- Wrap routes in `smart.throttle` with profiles: `public` (120-240/min), `search` (40/min), `cart` (60/min), `checkout` (30/min).
- Use `CacheService` for high-traffic data (search filters, categories, featured items); clear related cache keys on model updates.
- Handle all image uploads through `ImageService` for automatic webp conversion, cropping, and thumbnail generation.
- Run `php artisan maintenance:weekly` on schedule (via Cron) to prune old logs, optimize tables, and clean temporary files.

