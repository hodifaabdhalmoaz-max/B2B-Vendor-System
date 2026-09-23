---
name: deploy
argument-hint: [branch-name]
---

Deploy Dunya-Alatfaal-Shop to Hostinger (branch defaults to `main` if `$ARGUMENTS` is empty):

1. Run `npm run build` to compile front-end assets locally.
2. Run `php artisan test` and `./vendor/bin/pint` to verify tests and code style.
3. Commit compiled assets: `git add public/build/ && git commit -m "build: compile assets" && git push origin ${ARGUMENTS:-main}`.
4. On server: `git pull`, `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`.
5. Cache production config: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
6. Reset OPCache via web/artisan to reflect changes instantly.
7. Verify deployment by hitting the health check endpoint or home page.
