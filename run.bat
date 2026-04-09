@echo off
IF "%1"=="dev" (
    echo Running development commands...
    git stash
    git pull
    composer install
    npm install
    php artisan migrate:fresh --seed
) ELSE IF "%1"=="prod" (
    echo Running production commands...
    git stash
    git pull
    composer install
    npm install
    npm run build
    php artisan migrate:fresh --seed
) ELSE (
    echo Usage: run.bat [dev|prod]
)


::run.bat dev / prod
