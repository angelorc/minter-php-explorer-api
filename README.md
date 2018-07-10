# Minter Explorer API

### Run:
Copy source/.env.example to source/.env

1. docker-compose up
2. docker-compose exec app php artisan migrate
3. docker-compose exec app php artisan block:pull

### Misc
docker-compose exec app vendor/bin/swagger app --output public/help

http://localhost:8000/help/index.html - SWAGGER