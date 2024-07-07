# Guest Service

Сервис, разработанный на языке программирования PHP с использованием фреймворка Symfony,
предоставляет API для управления информацией о гостях, включая создание, чтение, обновление и удаление записей.
При создании новой записи о госте, если не указана страна, система автоматически определит страну на основе номера 
телефона гостя с использованием библиотеки `libphonenumber-for-php`.

## Запуск проекта

### 1. Клонирование репозитория

```bash
git clone git@github.com:Klem65/guest-service.git
cd guest-service
```

### 2. Сборка и запуск Docker контейнеров

```bash
docker compose build
docker compose up -d
```

### 3. Установка зависимостей и выполнение миграций базы данных

```bash
docker compose exec php bash
composer install
docker compose exec php php bin/console doctrine:migrations:migrate
```

### 5. Тестирование API

Импортируйте коллекцию запросов Postman `Guest App.postman_collection.json` для тестирования API.

Примеры запросов: 

Получить запись гостя по id
```bash
curl --location 'http://localhost:8080/info?id=1'
```

Получить все записи гостей
```bash
curl --location 'http://localhost:8080/api/guests'
```

Создать запись гостя
```bash
curl --location 'http://localhost:8080/api/guests' \
--header 'Content-Type: application/json' \
--data-raw '{
    "firstName": "Frank",
    "lastName": "Woodson",
    "email": "frank.woodson@example.com",
    "phone": "+33 1 09 75 83 51",
    "country": "France"
}'
```

Обновить данные гостя
```bash
curl --location --request PATCH 'http://localhost:8080/api/guest/1' \
--header 'Content-Type: application/json' \
--data '{
	"phone": "+79999999999"
}'
```

Удалить запись гостя
```bash
curl --location --request PATCH 'http://localhost:8080/api/guest/1' \
--header 'Content-Type: application/json' \
--data '{
	"phone": "+79999999999"
}'
```

## Дополнительная информация

- Убедитесь, что порт `8080` доступен.
