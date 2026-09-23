#!/bin/bash
# Before EVERY commit: Pint style check → PHPStan analysis → PHPUnit tests
# If any check fails, the commit is BLOCKED (exit code 2).

echo "Running pre-commit hooks for Dunya-Alatfaal-Shop..."

# 1. Format Check (Laravel Pint)
# Check only modified PHP files for code style compliance
STAGED_PHP_FILES=$(git diff --cached --name-only --diff-filter=ACM | grep -E "\.php$")

if [ -n "$STAGED_PHP_FILES" ]; then
    echo "Checking PHP code style with Pint..."
    # Running Pint in test mode so it doesn't modify files during pre-commit check (fails if styling issues exist)
    ./vendor/bin/pint --test || {
        echo "❌ Code style check failed! Please run './vendor/bin/pint' to auto-format your code."
        exit 2
    }
fi

# 2. Static Analysis (PHPStan)
if [ -f "./vendor/bin/phpstan" ]; then
    echo "Running PHPStan static analysis..."
    ./vendor/bin/phpstan analyse --memory-limit=1G || {
        echo "❌ PHPStan static analysis failed! Fix static analysis errors before committing."
        exit 2
    }
fi

# 3. Test Suite (PHPUnit)
echo "Running test suite..."
php artisan test --compact || {
    echo "❌ Tests failed! Fix regressions before committing."
    exit 2
}

echo "✅ All checks passed successfully! Commit allowed."
exit 0
