# Hostinger to AWS Migration Plan

## Overview
This document outlines the complete migration plan for moving the Living Legacy QR application from Hostinger hosting to AWS infrastructure (EC2 + RDS + S3).

---

## 🎯 Migration Goals

1. **Move application to AWS EC2** (t4g.nano instance)
2. **Migrate database to AWS RDS** (MySQL)
3. **Migrate all images/files to AWS S3**
4. **Zero downtime migration** (or minimal downtime)
5. **Maintain data integrity** throughout the process

---

## 📋 Pre-Migration Checklist

### 1. AWS Account Setup
- [ ] Create AWS account
- [ ] Set up IAM user with appropriate permissions
- [ ] Configure AWS CLI locally
- [ ] Set up billing alerts

### 2. AWS Resources to Create
- [ ] **EC2 Instance**: t4g.nano (or t3.nano) - ~$3.50/month
- [ ] **RDS Instance**: db.t3.micro (MySQL 8.0) - ~$15/month
- [ ] **S3 Bucket**: For image storage - ~$2-5/month
- [ ] **Security Groups**: Configure firewall rules
- [ ] **Elastic IP**: For static IP address (optional)

### 3. Backup Current Production
- [ ] Full database backup from Hostinger
- [ ] Backup all files/images from Hostinger
- [ ] Export environment variables
- [ ] Document current server configuration

---

## 🔧 Step-by-Step Migration Process

### Phase 1: AWS Infrastructure Setup (Day 1)

#### 1.1 Create S3 Bucket
```bash
# Create bucket via AWS Console or CLI
aws s3 mb s3://living-legacy-qr-staging --region us-east-1

# Configure bucket policy for public read access
# (See S3_BUCKET_POLICY.json below)

# Enable CORS if needed
# (See S3_CORS_CONFIG.json below)
```

**S3 Bucket Policy** (`S3_BUCKET_POLICY.json`):
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::living-legacy-qr-staging/*"
        }
    ]
}
```

**S3 CORS Configuration** (`S3_CORS_CONFIG.json`):
```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": []
    }
]
```

#### 1.2 Create RDS MySQL Instance
```bash
# Via AWS Console:
# - Engine: MySQL 8.0
# - Instance class: db.t3.micro
# - Storage: 20GB (auto-scaling enabled)
# - Public access: No (only from EC2)
# - Backup retention: 7 days
# - Multi-AZ: No (for staging)
```

**Security Group Rules:**
- Inbound: MySQL (3306) from EC2 security group only

#### 1.3 Create EC2 Instance
```bash
# Via AWS Console:
# - Instance type: t4g.nano (ARM) or t3.nano (x86)
# - AMI: Amazon Linux 2023 or Ubuntu 22.04 LTS
# - Storage: 8GB (minimum)
# - Security Group: Allow HTTP (80), HTTPS (443), SSH (22)
```

**Security Group Rules:**
- Inbound: HTTP (80) from 0.0.0.0/0
- Inbound: HTTPS (443) from 0.0.0.0/0
- Inbound: SSH (22) from your IP only

---

### Phase 2: Application Setup on EC2 (Day 1-2)

#### 2.1 Connect to EC2 Instance
```bash
ssh -i your-key.pem ec2-user@your-ec2-ip
```

#### 2.2 Install Required Software
```bash
# Update system
sudo yum update -y  # Amazon Linux
# OR
sudo apt update && sudo apt upgrade -y  # Ubuntu

# Install PHP 8.1+ and extensions
sudo yum install php81 php81-php-fpm php81-php-mysqlnd php81-php-xml php81-php-mbstring php81-php-gd php81-php-curl php81-php-zip -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx (or Apache)
sudo yum install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx

# Install MySQL client (for testing RDS connection)
sudo yum install mysql -y
```

#### 2.3 Clone and Setup Application
```bash
# Clone repository
cd /var/www
sudo git clone your-repo-url living-legacy-qr
cd living-legacy-qr

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
sudo chown -R nginx:nginx /var/www/living-legacy-qr
sudo chmod -R 755 /var/www/living-legacy-qr
sudo chmod -R 775 /var/www/living-legacy-qr/storage
sudo chmod -R 775 /var/www/living-legacy-qr/bootstrap/cache
```

#### 2.4 Configure Environment
```bash
# Copy .env file
cp .env.example .env

# Edit .env with production values
sudo nano .env
```

**.env Configuration:**
```env
APP_NAME="Living Legacy QR"
APP_ENV=staging
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://staging.yourdomain.com

# Database (RDS)
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.region.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Filesystem (S3)
FILESYSTEM_DISK=s3

# AWS S3 Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=living-legacy-qr-staging
AWS_URL=https://living-legacy-qr-staging.s3.amazonaws.com

# Other configurations...
```

#### 2.5 Generate Application Key and Cache Config
```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 2.6 Configure Nginx
```nginx
# /etc/nginx/conf.d/living-legacy-qr.conf
server {
    listen 80;
    server_name staging.yourdomain.com;
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
```

```bash
# Test and reload Nginx
sudo nginx -t
sudo systemctl reload nginx
```

---

### Phase 3: Database Migration (Day 2)

#### 3.1 Export Database from Hostinger
```bash
# On Hostinger (via SSH or phpMyAdmin)
mysqldump -u username -p database_name > backup.sql

# Or via phpMyAdmin: Export → Custom → Select all tables → Go
```

#### 3.2 Import Database to RDS
```bash
# On your local machine or EC2
mysql -h your-rds-endpoint.region.rds.amazonaws.com \
      -u your_db_username \
      -p \
      your_database_name < backup.sql
```

#### 3.3 Run Migrations (if needed)
```bash
cd /var/www/living-legacy-qr
php artisan migrate --force
```

#### 3.4 Verify Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
# Should return PDO object
```

---

### Phase 4: Image Migration to S3 (Day 2-3)

#### 4.1 Option A: Using Migration Script (Recommended)
```bash
# On EC2 or local machine with access to Hostinger files
cd /var/www/living-legacy-qr

# Dry run first
php artisan migrate:images-to-s3 --dry-run

# Actual migration
php artisan migrate:images-to-s3

# Migrate specific types only
php artisan migrate:images-to-s3 --skip-reviews --skip-tributes
```

#### 4.2 Option B: Using AWS CLI (For Large Files)
```bash
# Install AWS CLI
sudo yum install aws-cli -y

# Configure credentials
aws configure

# Sync images to S3
aws s3 sync /path/to/public/images/profile/photos s3://living-legacy-qr-staging/images/profile/photos
aws s3 sync /path/to/public/images/profile/profile_pictures s3://living-legacy-qr-staging/images/profile/profile_pictures
aws s3 sync /path/to/public/images/profile/cover_pictures s3://living-legacy-qr-staging/images/profile/cover_pictures
aws s3 sync /path/to/public/images/reviews s3://living-legacy-qr-staging/images/reviews
aws s3 sync /path/to/public/images/profile/tributes s3://living-legacy-qr-staging/images/profile/tributes
```

#### 4.3 Option C: Using Third-Party Tools

**Recommended Tools:**

1. **CloudBerry (MSP360)**
   - GUI-based tool
   - Supports scheduled sync
   - Free for basic use
   - Download: https://www.msp360.com/

2. **Rclone**
   - Command-line tool
   - Open source
   - Supports many cloud providers
   - Installation:
     ```bash
     curl https://rclone.org/install.sh | sudo bash
     rclone config  # Configure S3
     rclone copy /local/path s3:bucket-name/path
     ```

3. **S3 Browser**
   - Windows GUI tool
   - Easy to use
   - Free version available
   - Download: https://s3browser.com/

4. **Cyberduck**
   - Cross-platform GUI
   - Free and open source
   - Supports S3
   - Download: https://cyberduck.io/

**Using Rclone (Recommended for CLI):**
```bash
# Install Rclone
curl https://rclone.org/install.sh | sudo bash

# Configure
rclone config
# Select "s3" → Enter AWS credentials → Choose region

# Sync images
rclone copy /var/www/living-legacy-qr/public/images/profile/photos \
            s3:living-legacy-qr-staging/images/profile/photos \
            --progress --transfers 10

# Verify sync
rclone check /var/www/living-legacy-qr/public/images/profile/photos \
             s3:living-legacy-qr-staging/images/profile/photos
```

---

### Phase 5: DNS and SSL Setup (Day 3)

#### 5.1 Update DNS Records
```
Type: A
Name: staging (or @)
Value: Your EC2 Elastic IP or Public IP
TTL: 300
```

#### 5.2 Setup SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo yum install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d staging.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

---

### Phase 6: Testing and Verification (Day 3-4)

#### 6.1 Functional Testing Checklist
- [ ] User registration/login works
- [ ] Profile creation works
- [ ] Photo upload works (uploads to S3)
- [ ] Photo display works (loads from S3)
- [ ] Profile picture upload works
- [ ] Cover picture upload works
- [ ] Photo deletion works (deletes from S3)
- [ ] QR code generation works
- [ ] API endpoints respond correctly
- [ ] Frontend loads correctly
- [ ] All images display correctly

#### 6.2 Performance Testing
- [ ] Page load times acceptable
- [ ] Image loading speed acceptable
- [ ] Database query performance acceptable
- [ ] API response times acceptable

#### 6.3 Security Testing
- [ ] HTTPS works correctly
- [ ] Security headers configured
- [ ] Database not publicly accessible
- [ ] S3 bucket permissions correct

---

### Phase 7: Production Cutover (Day 4-5)

#### 7.1 Final Backup
```bash
# Backup database one more time
mysqldump -h hostinger-db -u user -p database > final_backup.sql

# Backup any new images uploaded since last backup
```

#### 7.2 Update Production DNS
```
# Change DNS A record to point to AWS EC2
# TTL should be low (300 seconds) for quick rollback if needed
```

#### 7.3 Monitor Application
- Monitor error logs: `tail -f storage/logs/laravel.log`
- Monitor server resources: `htop` or AWS CloudWatch
- Monitor S3 usage in AWS Console
- Monitor RDS performance in AWS Console

#### 7.4 Rollback Plan (If Needed)
1. Revert DNS to Hostinger IP
2. Restore database from backup if needed
3. Investigate issues
4. Fix and retry migration

---

## 🔄 Post-Migration Tasks

### 1. Cleanup
- [ ] Remove old files from Hostinger (after verification period)
- [ ] Archive Hostinger backups
- [ ] Update documentation

### 2. Monitoring Setup
- [ ] Set up CloudWatch alarms for EC2
- [ ] Set up CloudWatch alarms for RDS
- [ ] Set up CloudWatch alarms for S3
- [ ] Configure email/SMS notifications

### 3. Cost Optimization
- [ ] Review AWS costs weekly
- [ ] Set up cost budgets and alerts
- [ ] Optimize S3 storage class (Standard → Standard-IA for old files)
- [ ] Consider Reserved Instances for RDS if keeping long-term

### 4. Documentation
- [ ] Document new infrastructure
- [ ] Update deployment procedures
- [ ] Create runbooks for common issues
- [ ] Document backup/restore procedures

---

## 📊 Estimated Costs (Monthly)

| Service | Instance/Size | Monthly Cost |
|---------|--------------|--------------|
| EC2 t4g.nano | 0.5 vCPU, 0.5GB RAM | ~$3.50 |
| RDS db.t3.micro | 2 vCPU, 1GB RAM | ~$15.00 |
| S3 Storage | 100GB | ~$2.30 |
| S3 Requests | 100K GET requests | ~$0.40 |
| Data Transfer | 10GB out | ~$0.90 |
| **Total** | | **~$22-26/month** |

*Note: Costs may vary based on usage and region*

---

## 🚨 Troubleshooting

### Common Issues

1. **S3 Upload Fails**
   - Check IAM permissions
   - Verify AWS credentials in .env
   - Check bucket policy

2. **Database Connection Fails**
   - Verify security group allows EC2 → RDS
   - Check RDS endpoint and credentials
   - Verify database exists

3. **Images Not Loading**
   - Check S3 bucket policy (public read)
   - Verify CORS configuration
   - Check image URLs in database

4. **High Costs**
   - Review CloudWatch metrics
   - Check for unnecessary data transfer
   - Optimize S3 storage class

---

## 📞 Support Resources

- **AWS Documentation**: https://docs.aws.amazon.com/
- **Laravel Documentation**: https://laravel.com/docs
- **AWS Support**: https://aws.amazon.com/support/

---

## ✅ Migration Sign-Off

- [ ] All phases completed
- [ ] Testing passed
- [ ] Monitoring configured
- [ ] Documentation updated
- [ ] Team trained on new infrastructure

**Migration Date**: _______________
**Migrated By**: _______________
**Verified By**: _______________

---

*Last Updated: [Current Date]*
