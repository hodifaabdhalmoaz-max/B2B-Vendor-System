---
paths:
  - "routes/api.php"
  - "app/Http/Controllers/Api/**/*.php"
  - "app/Http/Resources/**/*.php"
---

# API Rules

- Keep API routes in `routes/api.php` under `/api/` prefix with `ApiRateLimit` middleware.
- Authenticate API users exclusively via **Laravel Sanctum** (`auth:sanctum` middleware). Do **not** install or use JWT packages (`tymon/jwt-auth`, etc.).
- Revoke previous tokens on login (`$user->tokens()->delete()`) before issuing a new one to prevent token accumulation.
- Validate requests using dedicated FormRequest classes; return `422` with JSON validation details.
- Return standardized JSON: `{ "success": bool, "data": {}, "message": "..." }` with correct HTTP status codes.
- Never return Eloquent models directly — use Laravel API Resources (`JsonResource` / `ResourceCollection`) with eager-loaded relations.
- Hide internal column names and sensitive data in the resource's `toArray()` method.

