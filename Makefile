.PHONY: start stop build

start:
	docker compose up --wait
stop:
	docker compose down --remove-orphans

build:
	docker compose build --pull --no-cache
