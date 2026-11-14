dc_file := "docker-compose.yaml"
dc := "docker compose -f " + dc_file

start:
    {{ dc }} up -d

ws: start
    {{ dc }} exec -it workspace bash

phpunit: start
    {{ dc }} exec -it workspace vendor/bin/phpunit

init-dataset: start
    {{ dc }} exec -it workspace php ./scripts/import_dataset.php
