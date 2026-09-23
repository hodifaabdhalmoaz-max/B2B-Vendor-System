#!/bin/bash

# Production Deployment Script for Kids Store E-commerce
# Author: Hudhaifa Al-Hudhaifi
# Version: 1.0

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="Kids Store E-commerce"
PROJECT_DIR="/var/www/kids-store"
BACKUP_DIR="/var/backups/kids-store"
LOG_FILE="/var/log/kids-store-deploy.log"
PHP_VERSION="8.2"
NODE_VERSION="18"

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" >> $LOG_FILE
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
    echo "[ERROR] $1" >> $LOG_FILE
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
    echo "[WARNING] $1" >> $LOG_FILE
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
    echo "[INFO] $1" >> $LOG_FILE
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        error "This script should not be run as root for security reasons"
    fi
}

# Check system requirements
check_requirements() {
    log "Checking system requirements..."
    
    # Check PHP version
    if ! command -v php &> /dev/null; then
        error "PHP is not installed"
    fi
    
    PHP_CURRENT=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    if [[ "$PHP_CURRENT" < "$PHP_VERSION" ]]; then
        error "PHP $PHP_VERSION or higher is required. Current: $PHP_CURRENT"
    fi
    
    # Check Composer
    if ! command -v composer &> /dev/null; then
        error "Composer is not installed"
    fi
    
    # Check Node.js
    if ! command -v node &> /dev/null; then
        error "Node.js is not installed"
    fi
    
    # Check required PHP extensions
    REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "tokenizer" "xml" "ctype" "json" "bcmath" "fileinfo" "gd" "zip" "redis")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            error "PHP extension '$ext' is not installed"
        fi
    done
    
    log "✅ All requirements met"
}

# Create backup before deployment
create_backup() {
    log "Creating backup before deployment..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/pre_deploy_$TIMESTAMP"
    
    mkdir -p "$BACKUP_PATH"
    
    # Backup database
    if [[ -f "$PROJECT_DIR/.env" ]]; then
        DB_NAME=$(grep "^DB_DATABASE=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
        DB_USER=$(grep "^DB_USERNAME=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
        DB_PASS=$(grep "^DB_PASSWORD=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
        
        if [[ -n "$DB_NAME" && -n "$DB_USER" ]]; then
            mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_PATH/database.sql"
            log "✅ Database backup created"
        fi
    fi
    
    # Backup files
    if [[ -d "$PROJECT_DIR" ]]; then
        tar -czf "$BACKUP_PATH/files.tar.gz" -C "$PROJECT_DIR" . --exclude=node_modules --exclude=vendor --exclude=.git
        log "✅ Files backup created"
    fi
    
    log "✅ Backup completed: $BACKUP_PATH"
}

# Pull latest code
update_code() {
    log "Updating code from repository..."
    
    cd "$PROJECT_DIR"
    
    # Stash any local changes
    git stash
    
    # Pull latest changes
    git pull origin main
    
    log "✅ Code updated"
}

# Install/update dependencies
install_dependencies() {
    log "Installing/updating dependencies..."
    
    cd "$PROJECT_DIR"
    
    # Install PHP dependencies
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Install Node.js dependencies
    npm ci --production
    
    log "✅ Dependencies installed"
}

# Build assets
build_assets() {
    log "Building production assets..."
    
    cd "$PROJECT_DIR"
    
    # Build assets
    npm run build
    
    log "✅ Assets built"
}

# Configure environment
configure_environment() {
    log "Configuring production environment..."
    
    cd "$PROJECT_DIR"
    
    # Ensure .env exists
    if [[ ! -f ".env" ]]; then
        if [[ -f ".env.production.example" ]]; then
            cp .env.production.example .env
            warning "Created .env from .env.production.example - please update with your values"
        else
            error ".env file not found and no template available"
        fi
    fi
    
    # Generate app key if not set
    if ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate --force
        log "✅ Application key generated"
    fi
    
    # Set production environment
    sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
    sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
    
    log "✅ Environment configured"
}

# Run database migrations
run_migrations() {
    log "Running database migrations..."
    
    cd "$PROJECT_DIR"
    
    # Run migrations
    php artisan migrate --force
    
    log "✅ Migrations completed"
}

# Optimize application
optimize_application() {
    log "Optimizing application for production..."
    
    cd "$PROJECT_DIR"
    
    # Clear all caches
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Optimize for production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    
    # Optimize Composer autoloader
    composer dump-autoload --optimize
    
    log "✅ Application optimized"
}

# Set proper permissions
set_permissions() {
    log "Setting proper file permissions..."
    
    cd "$PROJECT_DIR"
    
    # Set ownership
    sudo chown -R www-data:www-data .
    
    # Set directory permissions
    find . -type d -exec chmod 755 {} \;
    
    # Set file permissions
    find . -type f -exec chmod 644 {} \;
    
    # Set executable permissions for artisan
    chmod +x artisan
    
    # Set writable permissions for storage and cache
    chmod -R 775 storage bootstrap/cache
    
    log "✅ Permissions set"
}

# Restart services
restart_services() {
    log "Restarting services..."
    
    # Restart PHP-FPM
    sudo systemctl restart php${PHP_VERSION}-fpm
    
    # Restart Nginx
    sudo systemctl restart nginx
    
    # Restart Redis (if used)
    if systemctl is-active --quiet redis; then
        sudo systemctl restart redis
    fi
    
    # Restart queue workers
    if systemctl is-active --quiet kids-store-worker; then
        sudo systemctl restart kids-store-worker
    fi
    
    log "✅ Services restarted"
}

# Run health check
health_check() {
    log "Running post-deployment health check..."
    
    cd "$PROJECT_DIR"
    
    # Run system health check
    php artisan system:health-check --detailed
    
    # Test application response
    APP_URL=$(grep "^APP_URL=" .env | cut -d'=' -f2)
    if [[ -n "$APP_URL" ]]; then
        HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL")
        if [[ "$HTTP_STATUS" == "200" ]]; then
            log "✅ Application is responding correctly"
        else
            warning "Application returned HTTP status: $HTTP_STATUS"
        fi
    fi
    
    log "✅ Health check completed"
}

# Main deployment function
deploy() {
    log "🚀 Starting deployment of $PROJECT_NAME"
    log "Deployment started at: $(date)"
    
    check_root
    check_requirements
    create_backup
    update_code
    install_dependencies
    build_assets
    configure_environment
    run_migrations
    optimize_application
    set_permissions
    restart_services
    health_check
    
    log "🎉 Deployment completed successfully!"
    log "Deployment finished at: $(date)"
    
    # Send notification (if configured)
    if command -v mail &> /dev/null; then
        echo "Deployment of $PROJECT_NAME completed successfully at $(date)" | mail -s "Deployment Success" admin@yourdomain.com
    fi
}

# Rollback function
rollback() {
    log "🔄 Starting rollback process..."
    
    # Find latest backup
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/pre_deploy_* | head -n1)
    
    if [[ -z "$LATEST_BACKUP" ]]; then
        error "No backup found for rollback"
    fi
    
    log "Rolling back to: $LATEST_BACKUP"
    
    # Restore files
    cd "$PROJECT_DIR"
    tar -xzf "$LATEST_BACKUP/files.tar.gz"
    
    # Restore database
    if [[ -f "$LATEST_BACKUP/database.sql" ]]; then
        DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)
        DB_USER=$(grep "^DB_USERNAME=" .env | cut -d'=' -f2)
        DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2)
        
        mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$LATEST_BACKUP/database.sql"
    fi
    
    restart_services
    
    log "✅ Rollback completed"
}

# Script execution
case "${1:-deploy}" in
    "deploy")
        deploy
        ;;
    "rollback")
        rollback
        ;;
    "health-check")
        health_check
        ;;
    *)
        echo "Usage: $0 {deploy|rollback|health-check}"
        exit 1
        ;;
esac
