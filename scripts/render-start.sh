#!/bin/sh
# Arranque en Render: Nginx escucha $PORT (por defecto 10000).
set -eu
cd /var/www

PORT="${PORT:-10000}"

cat >/etc/nginx/sites-available/default <<EOF
server {
    listen ${PORT};
    listen [::]:${PORT};
    server_name _;
    root /var/www/public;
    index index.php index.html;

    client_max_body_size 20M;
    sendfile off;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_read_timeout 120;
    }
}
EOF
ln -sfn /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
rm -f /etc/nginx/sites-enabled/default.bak

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

php artisan storage:link >/dev/null 2>&1 || true
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true

if [ "${IMPORT_SQL:-0}" = "1" ] && [ -f /var/www/database/sql/estilo_dorado.sql ]; then
  echo "Importando database/sql/estilo_dorado.sql ..."
  sed -e '/^CREATE DATABASE/d' -e '/^USE /d' /var/www/database/sql/estilo_dorado.sql \
    | mysql \
      --host="${DB_HOST}" \
      --port="${DB_PORT:-3306}" \
      --user="${DB_USERNAME}" \
      --password="${DB_PASSWORD}" \
      "${DB_DATABASE}" \
    && echo "Import OK. Quita IMPORT_SQL=1 en Render para no repetirlo."
fi

php-fpm -D
exec nginx -g 'daemon off;'
