#!/bin/bash
# -----------------------------------------------------------------------------
# Script utilitário: executa comandos dentro do container Laravel como www-data
#
# USO:
#   ./docker/exec-as-www-data.sh php artisan cache:clear
#   ./docker/exec-as-www-data.sh php artisan migrate
#   ./docker/exec-as-www-data.sh bash
# -----------------------------------------------------------------------------

CONTAINER_NAME="dados-app"

if [ $# -eq 0 ]; then
    echo "Erro: nenhum comando fornecido."
    echo "Uso: $0 <comando> [args...]"
    echo "Exemplo: $0 php artisan cache:clear"
    exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "Erro: container '${CONTAINER_NAME}' não está rodando."
    echo "Inicie os containers com: docker compose up -d"
    exit 1
fi

docker exec -u www-data "${CONTAINER_NAME}" "$@"
