#!/bin/bash
# User Data Script for EC2 Instance
# This script runs when the EC2 instance first starts

set -e

# Update system
yum update -y

# Install PHP 8.1 and extensions
amazon-linux-extras enable php8.1
yum install -y php php-fpm php-mysqlnd php-xml php-mbstring php-gd php-curl php-zip

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install Nginx
yum install -y nginx
systemctl start nginx
systemctl enable nginx

# Install MySQL client
yum install -y mysql

# Install Git
yum install -y git

# Install AWS CLI
yum install -y aws-cli

# Create application directory
mkdir -p /var/www/living-legacy-qr
chown -R nginx:nginx /var/www

# Create log directory
mkdir -p /var/log/laravel
chown -R nginx:nginx /var/log/laravel

# Configure PHP-FPM
sed -i 's/user = apache/user = nginx/' /etc/php-fpm.d/www.conf
sed -i 's/group = apache/group = nginx/' /etc/php-fpm.d/www.conf
systemctl start php-fpm
systemctl enable php-fpm

# Configure Nginx for Laravel
cat > /etc/nginx/conf.d/living-legacy-qr.conf << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/living-legacy-qr/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# React SPA fallback - use this server block when hosting the React app
# (e.g. qr.livinglegacyqr.com or legacy.livinglegacyqr.com) on the same server.
# Ensures /uuid/legacy and other SPA routes serve index.html instead of 404
# on both mobile and desktop (fixes mobile 404 issue).
#
# server {
#     listen 80;
#     server_name qr.livinglegacyqr.com legacy.livinglegacyqr.com;
#     root /var/www/living-legacy-qr/public;
#     index index.html;
#     location / {
#         try_files $uri $uri/ /index.html;
#     }
#     location /assets {
#         try_files $uri =404;
#     }
# }
EOF

# Test Nginx configuration
nginx -t

# Reload Nginx
systemctl reload nginx

# Log completion
echo "User data script completed at $(date)" >> /var/log/user-data.log
