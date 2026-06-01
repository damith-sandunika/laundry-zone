@echo off
echo Starting SmartLaundry POS System...

echo 1. Starting Node.js WhatsApp Service...
start cmd /k "cd whatsapp-service && node index.js"

echo 2. Starting Laravel Backend...
start cmd /k "php artisan serve"

echo 3. Starting Vite Frontend (Tailwind CSS/JS)...
start cmd /k "npm run dev"

echo.
echo All services started in separate windows!
echo Access the system at: http://127.0.0.1:8000
echo Access WhatsApp Settings inside the Dashboard to scan the QR if needed.
pause
