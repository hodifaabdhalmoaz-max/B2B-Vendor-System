---
name: fix-issue
argument-hint: [issue-number]
---

Fix GitHub issue #$ARGUMENTS:

1. Run `gh issue view $ARGUMENTS` to read and understand the issue.
2. Search relevant files in `app/Http/Controllers/`, `app/Services/`, `app/Models/`, `app/Http/Middleware/`, `resources/views/`, `database/`.
3. Implement the minimal fix: keep logic in Services, use `ImageService` for images, use `OrderService`/`OrderRepository` for orders, clear caches after fix.
4. Write a regression test in `tests/Feature/` or `tests/Unit/` that fails before and passes after the fix.
5. Run `php artisan test` to verify all tests pass.
6. Commit: `git add -p && git commit -m "fix: description (closes #$ARGUMENTS)"`.
