Clonar y descargar proyecto:
**git clone https://github.com/dominguitox/Congelandia
**cd Congelandia

Actualizar dependencias y chotas:
**composer install
**cp .env.example .env
**php artisan key:generate
**npm install
**npm run build


Cada vez que se inicie el desarrollo:
**php artisan migrate
**php artisan serve