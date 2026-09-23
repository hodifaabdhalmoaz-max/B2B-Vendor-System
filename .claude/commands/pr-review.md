---
name: pr-review
argument-hint: [pr-number]
---

Review GitHub PR #$ARGUMENTS:

1. Run `gh pr checkout $ARGUMENTS` and `gh pr view $ARGUMENTS` to read the PR context.
2. Audit changed files: security (middleware, XSS, `@csrf`), performance (eager-loading, caching), code quality (Services over Controllers, Repositories), localization (`__()` / `@lang()`).
3. Run `php artisan test` on the PR branch to verify no regressions.
4. Post review via `gh pr review $ARGUMENTS`: group findings under Security/Performance/Code Quality, tag as CRITICAL/WARNING/SUGGESTION, approve or request changes.
