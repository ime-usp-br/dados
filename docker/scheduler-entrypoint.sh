#!/bin/sh
set -e

# -----------------------------------------------------------------------------
# Scheduler Entrypoint
# Aguarda o app terminar de instalar dependências e o MySQL ficar pronto
# antes de iniciar o schedule:work (roda o cron do Laravel em loop).
# -----------------------------------------------------------------------------

LARAVEL_USER="www-data"

echo "[scheduler] Aguardando vendor/autoload.php..."
until [ -f "vendor/autoload.php" ]; do
    sleep 2
done
echo "[scheduler] vendor encontrado."

echo "[scheduler] Aguardando MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306}', '${DB_USERNAME:-dados}', '${DB_PASSWORD:-dados}');" 2>/dev/null; do
    sleep 2
done
echo "[scheduler] MySQL pronto. Iniciando schedule:work..."

exec gosu "${LARAVEL_USER}" php artisan schedule:work
