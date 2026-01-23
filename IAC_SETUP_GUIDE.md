# Infrastructure as Code Setup Guide

This guide explains how to deploy the Living Legacy QR infrastructure using Infrastructure as Code (IaC).

## Prerequisites

1. **AWS Account** with appropriate permissions
2. **AWS CLI** installed and configured
3. **Terraform** (optional, for Terraform deployment)
4. **EC2 Key Pair** created in AWS Console

---

## Option 1: CloudFormation Deployment

### Step 1: Create EC2 Key Pair

1. Go to AWS Console → EC2 → Key Pairs
2. Click "Create key pair"
3. Name: `living-legacy-qr-key`
4. Type: RSA
5. Format: `.pem` (for Linux/Mac) or `.ppk` (for Windows)
6. Download and save securely
7. Set permissions: `chmod 400 living-legacy-qr-key.pem`

### Step 2: Update CloudFormation Template

Edit `cloudformation-template.yaml`:

1. **Update AMI ID** (line ~150):
   ```yaml
   ImageId: ami-0c55b159cbfafe1f0  # Update for your region
   ```
   
   Find your region's Amazon Linux 2023 AMI:
   ```bash
   aws ec2 describe-images \
     --owners amazon \
     --filters "Name=name,Values=al2023-ami-*-x86_64" \
     --query 'Images[*].[ImageId,Name]' \
     --output table
   ```

### Step 3: Deploy via AWS Console

1. Go to AWS Console → CloudFormation
2. Click "Create stack" → "With new resources"
3. Upload `cloudformation-template.yaml`
4. Fill in parameters:
   - **Environment**: `staging`
   - **InstanceType**: `t4g.nano`
   - **DBInstanceClass**: `db.t3.micro`
   - **DBName**: `livinglegacyqr`
   - **DBUsername**: `admin` (or your choice)
   - **DBPassword**: `YourSecurePassword123!`
   - **KeyPairName**: `living-legacy-qr-key` (or your key name)
   - **AllowedCIDR**: `0.0.0.0/0` (or your IP for security)
   - **S3BucketName**: `living-legacy-qr-staging`
5. Click "Next" → Review → Check "I acknowledge..." → "Create stack"
6. Wait 10-15 minutes for stack creation

### Step 4: Deploy via AWS CLI

```bash
# Create stack
aws cloudformation create-stack \
  --stack-name living-legacy-qr-staging \
  --template-body file://cloudformation-template.yaml \
  --parameters \
    ParameterKey=Environment,ParameterValue=staging \
    ParameterKey=InstanceType,ParameterValue=t4g.nano \
    ParameterKey=DBInstanceClass,ParameterValue=db.t3.micro \
    ParameterKey=DBName,ParameterValue=livinglegacyqr \
    ParameterKey=DBUsername,ParameterValue=admin \
    ParameterKey=DBPassword,ParameterValue='YourSecurePassword123!' \
    ParameterKey=KeyPairName,ParameterValue=living-legacy-qr-key \
    ParameterKey=AllowedCIDR,ParameterValue=0.0.0.0/0 \
    ParameterKey=S3BucketName,ParameterValue=living-legacy-qr-staging \
  --region us-east-1

# Check stack status
aws cloudformation describe-stacks \
  --stack-name living-legacy-qr-staging \
  --query 'Stacks[0].StackStatus'

# Get outputs
aws cloudformation describe-stacks \
  --stack-name living-legacy-qr-staging \
  --query 'Stacks[0].Outputs'
```

### Step 5: Get Output Values

After stack creation, get the outputs:

```bash
aws cloudformation describe-stacks \
  --stack-name living-legacy-qr-staging \
  --query 'Stacks[0].Outputs[*].[OutputKey,OutputValue]' \
  --output table
```

Save these values:
- `WebServerPublicIP` - Your EC2 public IP
- `DatabaseEndpoint` - RDS endpoint
- `S3BucketName` - S3 bucket name

---

## Option 2: Terraform Deployment

### Step 1: Install Terraform

**macOS:**
```bash
brew install terraform
```

**Linux:**
```bash
wget https://releases.hashicorp.com/terraform/1.6.0/terraform_1.6.0_linux_amd64.zip
unzip terraform_1.6.0_linux_amd64.zip
sudo mv terraform /usr/local/bin/
```

**Windows:**
Download from https://www.terraform.io/downloads

### Step 2: Configure AWS Credentials

```bash
aws configure
# Enter your Access Key ID
# Enter your Secret Access Key
# Enter region: us-east-1
# Enter output format: json
```

### Step 3: Create terraform.tfvars

Create `terraform.tfvars` file:

```hcl
aws_region      = "us-east-1"
environment     = "staging"
instance_type   = "t4g.nano"
db_instance_class = "db.t3.micro"
db_name         = "livinglegacyqr"
db_username     = "admin"
db_password     = "YourSecurePassword123!"
key_pair_name   = "living-legacy-qr-key"
allowed_cidr     = "0.0.0.0/0"
s3_bucket_name   = "living-legacy-qr-staging"
```

**⚠️ Important:** Add `terraform.tfvars` to `.gitignore` to avoid committing secrets!

### Step 4: Initialize Terraform

```bash
cd terraform/
terraform init
```

### Step 5: Plan Deployment

```bash
terraform plan
```

Review the plan to see what will be created.

### Step 6: Deploy Infrastructure

```bash
terraform apply
```

Type `yes` when prompted. This will take 10-15 minutes.

### Step 7: Get Output Values

```bash
terraform output
```

Save these values for your `.env` file.

### Step 8: Destroy Infrastructure (when needed)

```bash
terraform destroy
```

---

## Post-Deployment Steps

### 1. Connect to EC2 Instance

```bash
# Get the public IP from outputs
ssh -i living-legacy-qr-key.pem ec2-user@<PUBLIC_IP>
```

### 2. Clone and Setup Application

```bash
# On EC2 instance
cd /var/www/living-legacy-qr
sudo git clone <your-repo-url> .
sudo chown -R nginx:nginx /var/www/living-legacy-qr
cd /var/www/living-legacy-qr

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Configure Environment

```bash
sudo nano .env
```

Use the output values from CloudFormation/Terraform:

```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=http://<PUBLIC_IP>

# Database (from RDS endpoint)
DB_CONNECTION=mysql
DB_HOST=<DatabaseEndpoint>
DB_PORT=3306
DB_DATABASE=livinglegacyqr
DB_USERNAME=admin
DB_PASSWORD=YourSecurePassword123!

# Filesystem (S3)
FILESYSTEM_DISK=s3

# AWS S3 Configuration
AWS_ACCESS_KEY_ID=<your-access-key>
AWS_SECRET_ACCESS_KEY=<your-secret-key>
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=<S3BucketName>
AWS_URL=https://<S3BucketName>.s3.amazonaws.com
```

### 4. Generate Application Key

```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Run Migrations

```bash
php artisan migrate --force
```

### 6. Test Application

Visit `http://<PUBLIC_IP>` in your browser.

---

## Updating Infrastructure

### CloudFormation

```bash
# Update stack
aws cloudformation update-stack \
  --stack-name living-legacy-qr-staging \
  --template-body file://cloudformation-template.yaml \
  --parameters <same-parameters-as-before>
```

### Terraform

```bash
# Make changes to .tf files
terraform plan
terraform apply
```

---

## Cost Optimization Tips

1. **Use t4g.nano** for staging (cheaper ARM-based instance)
2. **Stop EC2** when not in use: `aws ec2 stop-instances --instance-ids <id>`
3. **Use RDS snapshots** before stopping
4. **Set up CloudWatch alarms** for cost monitoring
5. **Use S3 Intelligent-Tiering** for old images

---

## Troubleshooting

### CloudFormation Stack Fails

```bash
# Check stack events
aws cloudformation describe-stack-events \
  --stack-name living-legacy-qr-staging \
  --query 'StackEvents[*].[Timestamp,ResourceStatus,ResourceStatusReason]' \
  --output table
```

### Terraform Apply Fails

```bash
# Check state
terraform show

# Refresh state
terraform refresh

# Import existing resources (if needed)
terraform import aws_instance.web_server <instance-id>
```

### Can't Connect to EC2

1. Check security group allows SSH from your IP
2. Verify key pair permissions: `chmod 400 key.pem`
3. Check EC2 instance status in console

### Database Connection Fails

1. Verify security group allows EC2 → RDS (port 3306)
2. Check RDS endpoint is correct
3. Verify database credentials

---

## Security Best Practices

1. **Change AllowedCIDR** to your IP instead of `0.0.0.0/0`
2. **Use strong database passwords**
3. **Enable MFA** on AWS account
4. **Use IAM roles** instead of access keys when possible
5. **Enable CloudTrail** for audit logging
6. **Regular security updates** on EC2

---

## Next Steps

1. Set up SSL certificate (Let's Encrypt)
2. Configure domain name and DNS
3. Set up monitoring (CloudWatch)
4. Configure backups
5. Set up CI/CD pipeline

---

*For questions or issues, refer to the main MIGRATION_PLAN.md document.*
