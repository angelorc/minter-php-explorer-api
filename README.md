# Minter Explorer API

The official Minter Explorer API repository.

_NOTE: This project in active development stage so feel free to send us questions, issues, and wishes_

### Run:
Copy service/.env.example to service/.env

1. Start containers with  `docker-compose up` command 
2. Apply migrations `docker-compose exec app php artisan migrate`
3. Start to pulling data from node `docker-compose exec app php artisan minter:api:pull-node-data`
4. Run workers:
- Store transactions `php artisan queue:work --queue=transactions --tries=3 --daemon`
- Store validators `php artisan queue:work --queue=validators --tries=3 --daemon`
- Store block events `php artisan queue:work --queue=block-events --tries=3 --daemon`
- Main queue `php artisan queue:work --queue=main --tries=3 --daemon`
- Broadcast queue `php artisan queue:work --queue=broadcast --tries=3 --daemon`
- Broadcast transactions queue `php artisan queue:work --queue=broadcast_tx --tries=3 --daemon`


### Misc
docker-compose exec app vendor/bin/swagger app --output public/help

http://localhost:8000/help/index.html - SWAGGER