#!/bin/bash
# Automatically runs after saving a file to auto-format it.
# Receives the file path as the first argument: $1

TARGET_FILE="$1"

if [ -z "$TARGET_FILE" ]; then
    echo "Usage: lint-on-save.sh [file-path]"
    exit 1
fi

# Only run if the file actually exists
if [ ! -f "$TARGET_FILE" ]; then
    exit 0
fi

# Determine formatter based on file extension
case "$TARGET_FILE" in
    *.php)
        echo "Auto-formatting PHP file with Laravel Pint: $TARGET_FILE"
        ./vendor/bin/pint "$TARGET_FILE"
        ;;
    *.json|*.js|*.css)
        if [ -f "./node_modules/.bin/prettier" ]; then
            echo "Auto-formatting with Prettier: $TARGET_FILE"
            ./node_modules/.bin/prettier --write "$TARGET_FILE"
        fi
        ;;
    *)
        # Do nothing for other file types
        exit 0
        ;;
esac

exit 0
