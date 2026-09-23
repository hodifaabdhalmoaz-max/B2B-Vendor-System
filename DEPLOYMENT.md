# دليل نشر متجر الأطفال الإلكتروني

دليل شامل لنشر متجر الأطفال الإلكتروني على الخادم الإنتاجي

**تم تطويره بواسطة: حذيفة الحذيفي**

## Prerequisites

- A web server (Apache or Nginx)
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- SSH access to your server
- A domain name with DNS configured to point to your server
- SSL certificate for your domain

## Step 1: Prepare Your Server

### Install Required Software

For Ubuntu/Debian:

```bash
# Update package lists
sudo apt update
sudo apt upgrade -y

# Install PHP and required extensions
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-fpm php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl

# Install MySQL
sudo apt install -y mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Nginx (or Apache)
sudo apt install -y nginx
```

## Step 2: Configure MySQL

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create a database and user
sudo mysql -u root -p
```

In MySQL prompt:

```sql
CREATE DATABASE kids_store_db;
CREATE USER 'kids_store_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON kids_store_db.* TO 'kids_store_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 3: Configure Web Server

### For Nginx

Create a new site configuration:

```bash
sudo nano /etc/nginx/sites-available/kids-store
```

Add the following configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    root /var/www/kids-store/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/kids-store /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## Step 4: Install SSL Certificate

Using Let's Encrypt:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## Step 5: Deploy the Application

```bash
# Create the directory
sudo mkdir -p /var/www/kids-store
sudo chown -R $USER:$USER /var/www/kids-store

# Clone the repository or upload your files
cd /var/www/kids-store
git clone https://your-repository-url.git .

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set proper permissions
sudo chown -R www-data:www-data /var/www/kids-store
sudo find /var/www/kids-store -type f -exec chmod 644 {} \;
sudo find /var/www/kids-store -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/kids-store/storage /var/www/kids-store/bootstrap/cache
```

## Step 6: Configure Environment

```bash
# Copy the production environment file
cp production.env.example .env

# Generate application key
php artisan key:generate

# Edit the .env file with your production settings
nano .env
```

## Step 7: Set Up the Database

```bash
# Run migrations
php artisan migrate

# Seed the database (if needed)
php artisan db:seed
```

## Step 8: Configure Caching

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Step 9: Set Up Queue Worker (Optional)

If you need to process queues:

```bash
# Install Supervisor
sudo apt install -y supervisor

# Create a configuration file
sudo nano /etc/supervisor/conf.d/kids-store-worker.conf
```

Add the following:

```ini
[program:kids-store-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/kids-store/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/kids-store/storage/logs/worker.log
stopwaitsecs=3600
```

Start the supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

## Step 10: Set Up Scheduled Tasks

Add Laravel's scheduler to crontab:

```bash
sudo crontab -e
```

Add the following line:

```
* * * * * cd /var/www/kids-store && php artisan schedule:run >> /dev/null 2>&1
```

## Maintenance and Updates

To update the application:

```bash
cd /var/www/kids-store
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

To put the site in maintenance mode during updates:

```bash
php artisan down
# Perform updates
php artisan up
```

## Troubleshooting

- Check the Laravel logs: `/var/www/kids-store/storage/logs/laravel.log`
- Check Nginx error logs: `/var/log/nginx/error.log`
- Check PHP-FPM logs: `/var/log/php8.2-fpm.log`

## Security Considerations

- Keep your server and software up to date
- Use strong passwords
- Configure a firewall (UFW)
- Set up fail2ban to prevent brute force attacks
- Regularly backup your database and files
