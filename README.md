# Olimp Digital Test Task
## Installation & Setup

### 1. Клонування репозиторію
git clone <URL_ВАШОГО_РЕПОЗИТОРІЮ>
cd <НАЗВА_ПАПКИ_ПРОЄКТУ>
### 2. Налаштування файлу конфігурації
Створіть .env файл на основі прикладу:
- cp .env.example .env
### 3. Встановлення залежностей (Composer)
Оскільки папка vendor відсутня в репозиторії, встановіть PHP-залежності через тимчасовий Docker-контейнер (офіційний метод Laravel):


```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```
### 4. Запуск контейнерів
Підніміть середовище Sail у фоновому режимі:

./vendor/bin/sail up -d

### 5. Ініціалізація додатка та імпорт бази даних
Генеруємо ключ застосунку та імпортуємо готовий дамп бази даних, який знаходиться в папці database/seeders/dump.sql:

### Генерація ключа додатка
- ./vendor/bin/sail artisan key:generate

### Імпорт дампу бази даних безпосередньо в контейнер MySQL
- ./vendor/bin/sail mysql < database/seeders/dump.sql
