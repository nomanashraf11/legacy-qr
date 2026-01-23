# Infrastructure as Code (IaC)

This directory contains Infrastructure as Code templates for deploying the Living Legacy QR application to AWS.

## 📁 Directory Structure

```
infrastructure/
├── cloudformation/
│   └── cloudformation-template.yaml    # CloudFormation template
├── terraform/
│   ├── main.tf                         # Main Terraform configuration
│   ├── user_data.sh                    # EC2 user data script
│   └── terraform.tfvars.example         # Variables template
├── .gitignore                          # Git ignore rules
└── README.md                           # This file
```

## 🚀 Quick Start

### Option 1: CloudFormation (AWS Native)

**Prerequisites:**
- AWS CLI configured
- EC2 Key Pair created

**Deploy:**
```bash
cd infrastructure/cloudformation
aws cloudformation create-stack \
  --stack-name living-legacy-qr-staging \
  --template-body file://cloudformation-template.yaml \
  --parameters ParameterKey=KeyPairName,ParameterValue=your-key-name \
               ParameterKey=DBPassword,ParameterValue=YourPassword123!
```

### Option 2: Terraform (Recommended)

**Prerequisites:**
- Terraform installed
- AWS CLI configured

**Deploy:**
```bash
cd infrastructure/terraform
cp terraform.tfvars.example terraform.tfvars
# Edit terraform.tfvars with your values
terraform init
terraform plan
terraform apply
```

## 📚 Documentation

- **IAC_SETUP_GUIDE.md** - Complete setup guide with both options
- **MIGRATION_PLAN.md** - Full migration plan from Hostinger to AWS

## 🔧 What Gets Created

- **VPC** - Virtual Private Cloud with public/private subnets
- **EC2 Instance** - Web server (t4g.nano or t3.nano)
- **RDS MySQL** - Database instance (db.t3.micro)
- **S3 Bucket** - For image storage
- **Security Groups** - Firewall rules
- **IAM Roles** - For EC2 to access S3
- **Elastic IP** - Static IP address

## 💰 Estimated Cost

~$22-26/month for staging environment

## 🔐 Security Notes

1. **Never commit** `terraform.tfvars` or CloudFormation parameters with secrets
2. Use strong database passwords
3. Restrict `allowed_cidr` to your IP instead of `0.0.0.0/0`
4. Keep EC2 key pairs secure

## 📖 More Information

See `IAC_SETUP_GUIDE.md` for detailed instructions.
