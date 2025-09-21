Symfony 7 minimal skeleton (DDD + CQRS hints) with Nelmio Swagger included.

How to use:
1. Extract and enter folder.
2. Run `composer install` locally or inside container.
3. Run `docker compose up --build -d` to start MySQL and PHP container (build uses this Dockerfile).
4. The database is created automatically once the Docker image is running. If the database doesn't start, the tables may not have been created. Just write the following line to migrate the models for the tables: `docker-compose exec php php bin/console doctrine:migrations:migrate`
5. Visit http://localhost:8000/api/doc for Swagger UI (Nelmio).
