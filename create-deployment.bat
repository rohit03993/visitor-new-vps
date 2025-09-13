@echo off
echo Creating CRM Deployment Package...

REM Create zip file with all necessary files
powershell "Compress-Archive -Path app,config,database,resources,routes,storage,public,artisan,composer.json,composer.lock,.env.example -DestinationPath crm-deployment.zip -Force"

echo.
echo Deployment package created: crm-deployment.zip
echo.
echo Files included:
echo - app/ (CRM application)
echo - config/ (Configuration)
echo - database/ (Migrations and seeders)
echo - resources/ (Views with Paytm theme)
echo - routes/ (Route definitions)
echo - storage/ (Storage directory)
echo - public/ (Public assets including Paytm CSS)
echo - artisan (Laravel command line)
echo - composer.json (Dependencies)
echo - .env.example (Environment template)
echo.
echo Ready for upload to server!
pause
