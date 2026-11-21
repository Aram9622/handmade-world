# Handmade Work

Русскоязычная площадка объявлений об изделиях ручной работы на Laravel 12 и Blade. Мастера регистрируются, публикуют изделия с фотографией и снимают их с продажи. Покупатели могут искать товары, фильтровать их и писать продавцам по электронной почте.

## Локальный запуск через Docker

Установите Docker с Compose и выполните из корня проекта:

```sh
docker compose up --build -d
```

Откройте http://localhost:8000. При первом запуске Compose поднимает MySQL 8.4. Laravel ждёт готовности базы, создаёт ключ приложения и применяет миграции. База хранится в томе `handmade-mysql`, фотографии и ключ — в `handmade-data`. Данные сохраняются после `docker compose down`. Если ранее использовалась SQLite, её данные в MySQL автоматически не переносятся. После изменения исходников повторите команду запуска со сборкой.

Если порт 8000 занят, выполните `HANDMADE_PORT=8080 docker compose up --build -d` и откройте http://localhost:8080. Для проверки логов используйте `docker compose logs app`, для запуска тестов — `docker compose exec app php artisan test --do-not-cache-result`.

MySQL доступен внутри сети Compose. Перед первым запуском можно задать `HANDMADE_DB_PASSWORD` и `HANDMADE_DB_ROOT_PASSWORD` в локальном файле `.env`. Тесты используют отдельную SQLite-базу в памяти.

## Локальный запуск без Docker

Нужны PHP 8.2 или новее с расширениями Laravel, PDO SQLite и XMLWriter, Composer и SQLite. Отдельная сборка фронтенда не требуется.

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Откройте http://127.0.0.1:8000. Чтобы отправлять фотографии до 5 МБ, настройте в PHP `upload_max_filesize = 5M` и `post_max_size = 8M`.

Проверка проекта: `php artisan test`.

## Публикация

Разместите приложение на PHP-хостинге, укажите корневой каталог сайта `public/`, задайте `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` и настройки базы данных, затем запустите `php artisan migrate --force`. Храните базу SQLite и каталог `storage/app/private` на постоянном диске. Для сервера с несколькими экземплярами используйте общую базу и общее хранилище, которое подключено как диск `local`.

Цены указаны в рублях. Покупатель и продавец договариваются об оплате и доставке по почте.

После запуска на публичном HTTPS-домене проверьте, что адреса в `/robots.txt` и `/sitemap.xml` используют этот домен. Добавьте карту сайта в Google Search Console и Яндекс Вебмастер.
