#!/bin/sh
set -eu

# Docker named volumes are mounted after the image was built. They may contain
# root-owned compiled Blade files from past "docker compose exec app php artisan"
# calls. Apache renders templates as www-data and must be able to replace them.
if [ "$(id -u)" -eq 0 ]; then
    for directory in \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/framework/cache \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/logs \
        /var/www/html/bootstrap/cache
    do
        mkdir -p "$directory"
        chown -R www-data:www-data "$directory"
        chmod -R u+rwX,g+rwX "$directory"
    done
fi

exec /usr/local/bin/docker-php-entrypoint "$@"
