---
paths:
  - "resources/views/**/*.blade.php"
  - "resources/css/**/*.css"
  - "resources/sass/**/*.scss"
  - "resources/js/**/*.js"
  - "public/css/**/*.css"
  - "public/js/**/*.js"
---

# Frontend Rules

- Inherit from `layouts.app`; maintain single `<h1>` per page with proper semantic heading hierarchy.
- Code RTL-first layout as default; avoid inline CSS — use Sass/CSS components or responsive utilities.
- All state-changing forms must include `@csrf`; use `@method('PUT'/'PATCH'/'DELETE')` for non-POST actions.
- Use `@vite([...])` for asset compilation; load static media with `asset()` helper.
- Display images via responsive tags using `.webp` assets; fall back to placeholder if image field is null.
- Never hardcode text in views — use `__('key')` or `@lang('key')`; update both `lang/ar/` and `lang/en/`.
