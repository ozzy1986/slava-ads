#!/bin/bash
# First-time provisioning for slava-ads.ru on the existing VPS (Apache + PHP 8.5 + MySQL).
# Run on the VPS: sudo bash server-setup.sh
set -e

SITE_ROOT="/var/www/slava-ads.ru"
DB_NAME="slavaads_leads"
DB_USER="slavaads"

echo "=== Ensure PHP MySQL extension + curl ==="
apt-get update -y
apt-get install -y php-mysql php-curl libapache2-mod-php >/dev/null 2>&1 || true

echo "=== Create site root ==="
mkdir -p "$SITE_ROOT"
chown -R www-data:www-data "$SITE_ROOT"

echo "=== Create MySQL database and user ==="
read -r -p "Enter a password for MySQL user '$DB_USER': " DB_PASS
mysql <<SQL
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "=== Enable Apache site ==="
cp "$SITE_ROOT/deploy/slava-ads.ru.conf" /etc/apache2/sites-available/ 2>/dev/null || true
a2ensite slava-ads.ru.conf
a2enmod rewrite headers expires deflate
systemctl reload apache2

echo "=== Import DB schema (if present) ==="
if [ -f "$SITE_ROOT/api/schema.sql" ]; then
  mysql "$DB_NAME" < "$SITE_ROOT/api/schema.sql"
fi

echo "=== Done. Next steps ==="
echo "1. Create $SITE_ROOT/api/.env from .env.example with real values (DB_PASS=$DB_PASS)."
echo "2. When DNS points here: sudo certbot --apache -d slava-ads.ru -d www.slava-ads.ru"
echo "3. Canonical host www -> apex: in the certbot HTTP vhost set the redirect target to"
echo "   https://slava-ads.ru, and add the same www -> apex 301 rule to the *-le-ssl.conf vhost."
