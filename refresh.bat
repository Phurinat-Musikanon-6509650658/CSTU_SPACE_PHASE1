@echo off
echo Clearing Laravel caches...
docker exec -it cstu_space_app php artisan config:clear
docker exec -it cstu_space_app php artisan view:clear
docker exec -it cstu_space_app php artisan route:clear
docker exec -it cstu_space_app php artisan cache:clear
echo Done! Refresh your browser now.
pause