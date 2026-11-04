# Hospital Queue on AWS - Evidence Pack

## Identity
| Item | Value |
|---|---|
| AWS account | 906963607134 |
| Region | ap-southeast-1 (Singapore) |
| Deployed commit (branch `aws-deploy`) | `3a4113708b54c1ee350cb5475cb0e934b36f9dd9` (also in `/var/log/hospital-queue-deployed-sha.txt` on the instance) |
| Integration commit | `ea952e42873ee4cef4ef5feb661be78e27e27c68` |
| EC2 instance | `i-0217de63e550485b1`, t3.micro, public IP 18.143.166.168 |
| AMI | `ami-0403d346e3210afd6` (Ubuntu 24.04 LTS, Canonical 099720109477, build 20261023) |
| Security group | `sg-0946176d660094291` (`hospital-queue-sg`): 80/tcp from 0.0.0.0/0; 22/tcp from 103.254.214.140/30 only |
| IAM role | `arn:aws:iam::906963607134:role/hospital-queue-ec2-role` |
| Instance profile | `hospital-queue-ec2-profile` |
| DynamoDB table | `hospital-queue-events` - `arn:aws:dynamodb:ap-southeast-1:906963607134:table/hospital-queue-events` (PAY_PER_REQUEST, TTL on `expires_at`) |

## How DynamoDB is used
`app/Services/QueueEventLog.php` appends one item per queue action (`patient_checked_in`, `doctor_assigned`,
`consultation_completed`) from `PatientController`. Reads: `/events` page and `php artisan hq:recent-events`
(DynamoDB `Query`, newest first). Items hold patient id, priority, service - no names or contacts. Writes never
break the app (errors are logged). Credentials come from the instance role through the SDK default chain.

## Inline IAM policy (exact)
```json
{"Version":"2012-10-17","Statement":[{"Sid":"QueueEventsReadWrite","Effect":"Allow","Action":["dynamodb:PutItem","dynamodb:Query"],"Resource":"arn:aws:dynamodb:ap-southeast-1:906963607134:table/hospital-queue-events"}]}
```

## CLI commands, in order (profile `hospital-queue`, `AWS_REGION=ap-southeast-1`)
```
aws sts get-caller-identity
aws dynamodb create-table --table-name hospital-queue-events --attribute-definitions AttributeName=queue_date,AttributeType=S AttributeName=event_id,AttributeType=S --key-schema AttributeName=queue_date,KeyType=HASH AttributeName=event_id,KeyType=RANGE --billing-mode PAY_PER_REQUEST --tags Key=Project,Value=hospital-queue
aws dynamodb wait table-exists --table-name hospital-queue-events
aws dynamodb update-time-to-live --table-name hospital-queue-events --time-to-live-specification Enabled=true,AttributeName=expires_at
aws iam create-role --role-name hospital-queue-ec2-role --assume-role-policy-document file://deploy/iam/trust-policy.json --tags Key=Project,Value=hospital-queue
aws iam put-role-policy --role-name hospital-queue-ec2-role --policy-name hospital-queue-dynamodb --policy-document file://deploy/iam/dynamodb-policy.json
aws iam create-instance-profile --instance-profile-name hospital-queue-ec2-profile
aws iam add-role-to-instance-profile --instance-profile-name hospital-queue-ec2-profile --role-name hospital-queue-ec2-role
aws ec2 create-key-pair --key-name hospital-queue-key --key-type ed25519 ...   (.pem stored outside the repo)
aws ec2 create-security-group --group-name hospital-queue-sg --vpc-id vpc-05bf3c0d21174ca5d ...
aws ec2 authorize-security-group-ingress --group-id sg-0946176d660094291 --protocol tcp --port 22 --cidr 103.254.214.140/30
aws ec2 authorize-security-group-ingress --group-id sg-0946176d660094291 --protocol tcp --port 80 --cidr 0.0.0.0/0
aws ec2 run-instances --image-id ami-0403d346e3210afd6 --instance-type t3.micro --key-name hospital-queue-key --security-group-ids sg-0946176d660094291 --subnet-id subnet-0f0a5f4cd28a6d4b7 --iam-instance-profile Name=hospital-queue-ec2-profile --user-data file://deploy/bootstrap.sh --metadata-options HttpTokens=required,HttpEndpoint=enabled --block-device-mappings DeviceName=/dev/sda1,Ebs={VolumeSize=12,VolumeType=gp3} --tag-specifications ...Name=hospital-queue-app,Project=hospital-queue
aws ec2 wait instance-running --instance-ids i-0217de63e550485b1
```
Two earlier instances (`i-02498bdc54f7ec3c4`, `i-0d7d1b4f822ebb305`) were terminated after bootstrap defects (see below).

## Verification (raw output: `verification-output.txt`)
1. On the instance, `aws sts get-caller-identity` -> `arn:aws:sts::906963607134:assumed-role/hospital-queue-ec2-role/i-0217de63e550485b1` (role, not a user).
2. `curl -I http://localhost` -> `302 Found` (to /login).
3. Public `/login` returns `<title>Hospital Queue Management System</title>`, not the nginx page.
4. `migrate:status`: all 6 migrations `Ran`.
5. Logged-in `POST /patients/add` -> success; `aws dynamodb query` returned the `patient_checked_in` item; `hq:recent-events` and `/events` show it.
6. Server `.env` has `AWS_ACCESS_KEY_ID=` and `AWS_SECRET_ACCESS_KEY=` blank.
7. Port 22 is limited to `103.254.214.140/30`; 80 is public.
8. Demo login created for verification (`demo@hospital-queue.local`); the password is stored only in `/root/.demo_pw` on the instance.

## Decisions and deviations
- **SSH scope:** the home ISP rotates its egress between 103.254.214.140-.143, so a single /32 timed out. SSH is allowed from that /30 (4 addresses) instead.
- **Database:** the app uses MySQL-only SQL (`TIMESTAMPDIFF`), which fails on SQLite (`/registration` returned HTTP 500). The instance runs local MariaDB; no app code was changed.
- **Bootstrap bug 1:** cloud-init leaves `$HOME` unset, so `composer install` aborted; fixed in `bootstrap.sh`.
- **Sessions/cache** stay on the database driver; DynamoDB holds the queue event log only.
- The AWS CLI on the instance (snap) was installed by hand for verification, not by `bootstrap.sh`.
- Running as the account root user (the CLI login for this project returned root); the instance itself has no keys.

## Screenshots a human still needs to take
- EC2 console: instance `hospital-queue-app` (Details, Security, Networking tabs showing the IAM role and security group).
- DynamoDB console: table `hospital-queue-events` (Overview, Items showing the event, Additional settings showing TTL and on-demand mode).
- IAM console: role `hospital-queue-ec2-role` (Trust relationships, inline policy `hospital-queue-dynamodb`).
- Security group `hospital-queue-sg` inbound rules.
- Browser: `http://18.143.166.168/events` after registering a patient.
