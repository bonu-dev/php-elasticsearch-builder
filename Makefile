.PHONY: start ws phpunit init-dataset

DC_FILE=docker-compose.yaml
DC=docker compose -f $(DC_FILE)

start:
	$(DC) up -d
ws: start
	$(DC) exec -it workspace bash
phpunit: start
	$(DC) exec -it workspace vendor/bin/phpunit
init-dataset: start
	$(DC) exec -it workspace php ./scripts/import_dataset.php
