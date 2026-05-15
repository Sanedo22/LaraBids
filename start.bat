@echo off
echo Starting LaraBids Development Services...

:: Start Laravel Web Server
start "Laravel Server" cmd /k "title Laravel Server && php artisan serve"

:: Start Laravel Queue Worker
start "Laravel Queue" cmd /k "title Laravel Queue && php artisan queue:work"

:: Start Laravel Reverb (WebSockets)
start "Laravel Reverb" cmd /k "title Laravel Reverb && php artisan reverb:start"

:: Start Vite Frontend Server
start "Vite Dev Server" cmd /k "title Vite Dev Server && npm run dev"

echo All services are booting up in separate windows!
echo Close those windows when you want to stop the services.
