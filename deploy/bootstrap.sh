#!/bin/bash
# EC2 user-data for the hospital-queue app (Ubuntu 24.04, t3.micro).
# Everything is logged to /var/log/hospital-queue-bootstrap.log.
exec > >(tee -a /var/log/hospital-queue-bootstrap.log) 2>&1
set -euxo pipefail
export DEBIAN_FRONTEND=noninteractive
export COMPOSER_ALLOW_SUPERUSER=1

REPO="https://github.com/johnsiega/hospital-queue-AWS.git"
BRANCH="aws-deploy"
WEBROOT="/var/www/hospital-queue"
REGION="ap-southeast-1"
EVENTS_TABLE="hospital-queue-events"

# 1. Swap first: composer on 1 GB RAM can be OOM-killed without it.
if ! swapon --show | grep -q /swapfile; then
  fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
  echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

# 2. Runtime packages (Ubuntu 24.04 ships PHP 8.3).
apt-get update -y
apt-get install -y nginx git unzip curl composer \
  php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
  php8.3-sqlite3 php8.3-bcmath php8.3-intl

# 3. Fetch the aws-deploy branch (public/build is committed, so no Node/Vite here).
rm -rf "$WEBROOT"
git clone --branch "$BRANCH" --depth 1 "$REPO" "$WEBROOT"
git -C "$WEBROOT" rev-parse HEAD > /var/log/hospital-queue-deployed-sha.txt
cd "$WEBROOT"
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Production .env. AWS keys stay blank: the instance profile supplies credentials.
TOKEN=$(curl -s -X PUT http://169.254.169.254/latest/api/token -H 'X-aws-ec2-metadata-token-ttl-seconds: 300')
PUBLIC_IP=$(curl -s -H "X-aws-ec2-metadata-token: $TOKEN" http://169.254.169.254/latest/meta-data/public-ipv4)
cp .env.example .env
setenv() { # key value
  if grep -q "^$1=" .env; then sed -i "s|^$1=.*|$1=$2|" .env; else echo "$1=$2" >> .env; fi
}
setenv APP_NAME '"Hospital Queue"'
setenv APP_ENV production
setenv APP_DEBUG false
setenv APP_URL "http://$PUBLIC_IP"
setenv LOG_LEVEL warning
setenv DB_CONNECTION sqlite
setenv SESSION_DRIVER database
setenv CACHE_STORE database
setenv QUEUE_CONNECTION sync
setenv AWS_ACCESS_KEY_ID ''
setenv AWS_SECRET_ACCESS_KEY ''
setenv AWS_DEFAULT_REGION "$REGION"
setenv DYNAMODB_EVENTS_TABLE "$EVENTS_TABLE"

mkdir -p database && touch database/database.sqlite
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --class=DoctorSeeder --force   # doctors only; no default user/password
php artisan config:cache && php artisan route:cache && php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache database
chmod -R ug+rwX storage bootstrap/cache database

# 5. nginx -> PHP-FPM serving public/
cat > /etc/nginx/sites-available/hospital-queue <<'NGINX'
server {
    listen 80 default_server;
    server_name _;
    root /var/www/hospital-queue/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known) { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/hospital-queue /etc/nginx/sites-enabled/hospital-queue
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now php8.3-fpm
systemctl restart nginx
echo "BOOTSTRAP COMPLETE"
