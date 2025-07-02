YELLOW := \033[1;33m
GREEN := \033[1;32m
RED := \033[1;31m
RESET := \033[0m

DC := docker compose

.DEFAULT_GOAL := help

help: ## Show this help message
	@echo '${YELLOW}Usage:${RESET}'
	@echo '  make ${GREEN}<target>${RESET}'
	@echo ''
	@echo '${YELLOW}Available targets:${RESET}'
	@awk 'BEGIN {FS = ":.*##"; printf ""} /^[a-zA-Z_-]+:.*?##/ { printf "  ${GREEN}%-20s${RESET} %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

up: ## Build and start all containers (including frontend)
	@echo "${YELLOW}Building and starting all containers...${RESET}"
	$(DC) up -d --build
	@echo "${GREEN}All services running!${RESET}"
	@echo "${GREEN}Frontend: http://localhost:3000${RESET}"
	@echo "${GREEN}API: http://localhost:8080${RESET}"
	@echo "${GREEN}Full App: http://localhost:8080 (via Nginx proxy)${RESET}"

down: ## Stop all containers
	@echo "${YELLOW}Stopping containers...${RESET}"
	$(DC) down
	@echo "${GREEN}Containers stopped!${RESET}"

install-backend: ## Install Symfony dependencies
	@echo "${YELLOW}Installing Symfony dependencies...${RESET}"
	$(DC) exec app composer install --no-interaction --optimize-autoloader
	@echo "${GREEN}Backend dependencies installed!${RESET}"

frontend-install: ## Install frontend dependencies
	@echo "${YELLOW}Installing frontend dependencies...${RESET}"
	$(DC) exec frontend pnpm install
	@echo "${GREEN}Frontend dependencies installed!${RESET}"

frontend-logs: ## Show frontend logs
	$(DC) logs -f frontend

install: ## Install both backend and frontend
	@echo "${YELLOW}Installing backend and frontend...${RESET}"
	make install-backend
	make frontend-install
	@echo "${GREEN}All dependencies installed!${RESET}"

init: ## Initialize the complete project
	@echo "${YELLOW}Initializing complete project...${RESET}"
	make up
	make install
	@echo "${GREEN}Project initialized successfully!${RESET}"
	@echo "${GREEN}Visit: http://localhost:8080${RESET}"

bash: ## Access app container bash
	@echo "${YELLOW}Accessing app container...${RESET}"
	$(DC) exec app bash

frontend-bash: ## Access frontend container bash
	@echo "${YELLOW}Accessing frontend container...${RESET}"
	$(DC) exec frontend sh

logs: ## Show container logs
	$(DC) logs -f

logs-backend: ## Show backend logs
	$(DC) logs -f app nginx

logs-frontend: ## Show frontend logs
	$(DC) logs -f frontend

ps: ## Show container status
	$(DC) ps

clean: ## Stop and remove all containers and volumes
	@echo "${YELLOW}Cleaning up...${RESET}"
	$(DC) down -v --remove-orphans
	@echo "${GREEN}Cleanup complete!${RESET}"

restart: ## Restart all containers
	make down
	make up

restart-frontend: ## Restart frontend service
	@echo "${YELLOW}Restarting frontend...${RESET}"
	$(DC) restart frontend
	@echo "${GREEN}Frontend restarted!${RESET}"

restart-backend: ## Restart backend services
	@echo "${YELLOW}Restarting backend...${RESET}"
	$(DC) restart app nginx
	@echo "${GREEN}Backend restarted!${RESET}"

cache-clear: ## Clear Symfony cache
	@echo "${YELLOW}Clearing Symfony cache...${RESET}"
	$(DC) exec app php bin/console cache:clear
	@echo "${GREEN}Cache cleared!${RESET}"
