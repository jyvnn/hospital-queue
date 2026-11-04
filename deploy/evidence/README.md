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


## Final Evidence (C1â€“C4, D1â€“D3)

All files below were captured on 2026-11-04 by Claude Code (an AI agent) for John Cedrick Siega. Everything in this phase was read-only against AWS and the instance. Text files only; terminal screenshots are optional and a human can add them later. Each C/D text file starts with a 5-line header (what it proves, command, ISO timestamp, host, operator).

### Files in deploy/evidence/
| File | What it proves | Command or action that produced it |
|---|---|---|
| C1-caller-identity.txt | Instance uses an assumed role, not a user | `ssh ... ubuntu@18.143.166.168 'aws sts get-caller-identity'` |
| C2-no-keys.txt | No AWS keys on the server `.env` (both values blank) | `ssh ... "grep -iE 'aws_access_key_id\|aws_secret_access_key' /var/www/hospital-queue/.env"` |
| C3-dynamodb-scan.txt | The table holds real app-written items; real key names are `queue_date` + `event_id` | `aws dynamodb scan --table-name hospital-queue-events --max-items 5 --profile hospital-queue` (from the laptop) |
| C3-dynamodb-query.txt | Query by partition key returns events newest first; includes the click-to-event trace note | `aws dynamodb query ... --key-condition-expression "queue_date = :d" ... --no-scan-index-forward --limit 10` |
| C3-dynamodb-query-FAILED-first-attempt.txt | The task's literal flag `--scan-index-forward false` is rejected by AWS CLI v2 | Same query with the template flag; kept for honesty |
| C4-migrate-status.txt | All 6 migrations `Ran`, none pending | `ssh ... 'cd /var/www/hospital-queue && php artisan migrate:status'` |
| D1-architecture.mmd | Mermaid source of the architecture diagram | Hand-written from the deployed resources |
| D1-architecture.png | Rendered diagram | `npx -y @mermaid-js/mermaid-cli -i D1-architecture.mmd -o D1-architecture.png --size 1600` |
| D1-export-error.txt | First export attempt (`-w 1600`) failed; this CLI version uses `--size` | Original command from the task |
| D1-drawio-note.txt | How to edit in draw.io and what to double-check | Instructions |
| D2-source.txt | Repo URL, branch, HEAD SHA, deployed SHA | `git remote get-url origin`, `git branch --show-current`, `git rev-parse HEAD`, `git log -1`, SSH read of the deployed-SHA file |
| D2-warning.txt | HEAD differs from the deployed commit (docs-only changes) | `git diff --name-only <deployed> HEAD` |
| dynamodb-describe-table.json, dynamodb-describe-ttl.json | Table schema, on-demand billing, TTL | `aws dynamodb describe-table` / `describe-time-to-live` |
| iam-get-role.json, iam-get-role-policy.json, iam-get-instance-profile.json | Role trust policy, exact inline policy, instance profile | `aws iam get-role` / `get-role-policy` / `get-instance-profile` |
| security-group.json | Security group rules | `aws ec2 describe-security-groups` |
| ec2-describe-instance.json | Instance type, AMI, profile, tags | `aws ec2 describe-instances` |
| verification-output.txt, verification-dynamodb-write.txt | Phase 5 on-instance checks and the first user-action write | Scripts run over SSH during deployment |

### What a panelist should ask, and where the answer is
| Question | Answer |
|---|---|
| Is the instance really using a role? | C1 |
| Are there any credentials on disk? | C2 |
| Is DynamoDB actually being used? | C3 (scan + query), plus the DynamoDB console Items screenshot and the live `/events` page (see screenshot list) |
| Did the schema apply? | C4 |
| What does the architecture look like? | D1-architecture.mmd, D1-architecture.png |
| Where is the code? | D2-source.txt |

### Screenshots still needed from a human
The earlier evidence plan labels A1â€“A8 and B1â€“B5 were not available to me, so I cannot confirm which label is which. These are the console and running-app shots the evidence needs; map them to your A/B numbers:
- EC2 console: instance `hospital-queue-app` Details tab (type t3.micro, state running, public IP 18.143.166.168).
- EC2 console: Security tab showing IAM role `hospital-queue-ec2-role` and security group `hospital-queue-sg` with its inbound rules.
- IAM console: role `hospital-queue-ec2-role` Trust relationships (ec2.amazonaws.com).
- IAM console: inline policy `hospital-queue-dynamodb` showing only `dynamodb:PutItem` and `dynamodb:Query` on the table ARN.
- DynamoDB console: table `hospital-queue-events` overview (on-demand, key schema, ARN).
- DynamoDB console: Explore items showing the app-written events.
- DynamoDB console: Additional settings showing TTL enabled on `expires_at`.
- App: `http://18.143.166.168/events` after registering a patient.
- App: the Registration page and the queue/home page.
- App: login page (shows the Laravel app is serving, not the nginx default).
- Optional: terminal screenshots of C1, C2, C4.

### Known gaps and honest limitations
- **Only one DynamoDB table exists.** The task's diagram text mentioned `hospital-queue-runtime`; it was never created, so D1 draws only `hospital-queue-events`. Sessions and cache use the database driver (local MariaDB). DynamoDB holds the queue event log only.
- **SSH is not limited to a single IP.** Port 22 allows `103.254.214.140/30` (4 addresses) because the home ISP rotates the egress address and a single /32 timed out. The D1 label says so.
- **Account ID is not redacted** (no instruction to redact). The repo is public and contains it in the evidence files.
- **C3 scan ran from the laptop, not the instance,** using the AWS CLI profile logged in as the account root user. The instance role cannot Scan (only PutItem and Query), which is intended.
- **C3 deviates from the task template in two documented ways:** the key is `queue_date`, not `pk`; and AWS CLI v2 needs `--no-scan-index-forward`. The failed first attempt is kept.
- **Duplicate events:** the C3 query shows each `doctor_assigned` and `consultation_completed` event twice, about 0.2 seconds apart, for patients 3 and 4 (`patient_checked_in` appears once). That pattern suggests the UI sends those two requests twice, which could also double-count the doctor's patient counter; I have not investigated or tested this. Panelists may ask.
- **Query covers one day.** The table is partitioned by date, so the query shows 2026-11-04 only; other days need their own partition key.
- **Deployed commit differs from HEAD** (D2-warning.txt): docs-only changes after `3a41137`. Compared by git, not by diffing files on the instance.
- **The AWS CLI on the instance was installed by hand** (snap) for verification; it is not in `bootstrap.sh`, so a rebuilt instance would not have it.
- **D1 is Mermaid + PNG only,** not Visio or a finished draw.io file. The first PNG export attempt failed on a wrong flag (D1-export-error.txt).
- **App database is local MariaDB on the instance** (the app uses MySQL-only SQL); data is lost if the instance is terminated.
- **Demo login** `demo@hospital-queue.local` exists on the server; delete it after the defense.