# API Документация CMS Sitebill

## Обзор

CMS Sitebill предоставляет RESTful API для взаимодействия с системой. API позволяет выполнять операции CRUD (Create, Read, Update, Delete) с различными типами данных.

## Базовая информация

### Base URL

```
http://yourdomain.com/api/v1/
```

### Версионирование

API использует версионирование через URL:
- `/api/v1/` - Версия 1.x (текущая)
- `/api/v2/` - Версия 2.x (планируется)

### Формат данных

- **Request:** JSON, Form Data, Query Parameters
- **Response:** JSON

### Кодировка

UTF-8

## Аутентификация

### Методы аутентификации

#### 1. API Token

Получение токена:

```http
POST /api/v1/auth/token
Content-Type: application/json

{
  "username": "user@example.com",
  "password": "your_password"
}
```

Ответ:

```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 3600
}
```

Использование токена:

```http
GET /api/v1/data
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### 2. Session Authentication

Использование существующей сессии пользователя:

```http
GET /api/v1/data
Cookie: PHPSESSID=abc123def456
```

#### 3. Basic Authentication

```http
GET /api/v1/data
Authorization: Basic dXNlcjpwYXNzd29yZA==
```

## Endpoints

### Данные (Data)

#### Получить список объектов

```http
GET /api/v1/data
```

**Query параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| page | integer | Номер страницы (по умолчанию: 1) |
| per_page | integer | Элементов на странице (по умолчанию: 20, макс: 100) |
| status | string | Фильтр по статусу (active, inactive, pending) |
| type_id | integer | Фильтр по типу |
| region_id | integer | Фильтр по региону |
| search | string | Поиск по названию |
| sort | string | Сортировка (id, title, date_create) |
| order | string | Направление (asc, desc) |

**Пример запроса:**

```http
GET /api/v1/data?page=1&per_page=10&status=active&sort=date_create&order=desc
```

**Ответ:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Example Title",
      "description": "Example description",
      "status": "active",
      "type_id": 1,
      "region_id": 5,
      "user_id": 10,
      "price": 1000000,
      "date_create": "2025-01-15 10:30:00",
      "images": [
        {
          "id": 1,
          "filename": "image1.jpg",
          "url": "http://yourdomain.com/img/data/image1.jpg"
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 150,
    "total_pages": 15,
    "next_page": 2,
    "prev_page": null
  }
}
```

#### Получить объект по ID

```http
GET /api/v1/data/{id}
```

**Пример:**

```http
GET /api/v1/data/123
```

**Ответ:**

```json
{
  "success": true,
  "data": {
    "id": 123,
    "title": "Example Title",
    "description": "Full description here",
    "content": "Detailed content...",
    "status": "active",
    "type_id": 1,
    "region_id": 5,
    "user_id": 10,
    "price": 1000000,
    "area": 85.5,
    "rooms": 3,
    "floor": 5,
    "floors_total": 9,
    "address": "123 Example Street",
    "lat": 55.751244,
    "lng": 37.618423,
    "date_create": "2025-01-15 10:30:00",
    "date_update": "2025-01-20 14:20:00",
    "images": [
      {
        "id": 1,
        "filename": "image1.jpg",
        "url": "http://yourdomain.com/img/data/image1.jpg",
        "thumbnail": "http://yourdomain.com/img/data/thumb_image1.jpg"
      }
    ],
    "user": {
      "id": 10,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    }
  }
}
```

#### Создать новый объект

```http
POST /api/v1/data
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "New Property",
  "description": "Property description",
  "type_id": 1,
  "region_id": 5,
  "price": 1500000,
  "area": 95.0,
  "rooms": 3,
  "floor": 7,
  "floors_total": 12,
  "address": "456 New Street",
  "lat": 55.755814,
  "lng": 37.617635,
  "status": "active"
}
```

**Ответ:**

```json
{
  "success": true,
  "message": "Object created successfully",
  "data": {
    "id": 456,
    "title": "New Property",
    "date_create": "2025-01-23 11:45:00"
  }
}
```

#### Обновить объект

```http
PUT /api/v1/data/{id}
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Updated Title",
  "price": 1600000,
  "status": "active"
}
```

**Ответ:**

```json
{
  "success": true,
  "message": "Object updated successfully",
  "data": {
    "id": 456,
    "date_update": "2025-01-23 12:00:00"
  }
}
```

#### Удалить объект

```http
DELETE /api/v1/data/{id}
Authorization: Bearer {token}
```

**Ответ:**

```json
{
  "success": true,
  "message": "Object deleted successfully"
}
```

### Клиенты (Clients)

#### Получить список клиентов

```http
GET /api/v1/clients
Authorization: Bearer {token}
```

**Query параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| page | integer | Номер страницы |
| per_page | integer | Элементов на странице |
| active | boolean | Только активные (true/false) |
| search | string | Поиск по имени/email |

**Ответ:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "company": "Example Corp",
      "active": true,
      "date_create": "2025-01-01 09:00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 50,
    "total_pages": 3
  }
}
```

#### Создать клиента

```http
POST /api/v1/clients
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+0987654321",
  "company": "Smith Inc",
  "active": true
}
```

### Изображения (Images)

#### Загрузить изображение

```http
POST /api/v1/images
Content-Type: multipart/form-data
Authorization: Bearer {token}

--boundary
Content-Disposition: form-data; name="image"; filename="photo.jpg"
Content-Type: image/jpeg

{binary data}
--boundary
Content-Disposition: form-data; name="data_id"

123
--boundary--
```

**Ответ:**

```json
{
  "success": true,
  "message": "Image uploaded successfully",
  "data": {
    "id": 789,
    "filename": "photo_123456.jpg",
    "url": "http://yourdomain.com/img/data/photo_123456.jpg",
    "thumbnail": "http://yourdomain.com/img/data/thumb_photo_123456.jpg",
    "size": 245678,
    "mime_type": "image/jpeg"
  }
}
```

#### Удалить изображение

```http
DELETE /api/v1/images/{id}
Authorization: Bearer {token}
```

### Пользователи (Users)

#### Получить текущего пользователя

```http
GET /api/v1/users/me
Authorization: Bearer {token}
```

**Ответ:**

```json
{
  "success": true,
  "data": {
    "id": 10,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "role": "agent",
    "active": true,
    "date_create": "2024-06-15 10:00:00"
  }
}
```

#### Обновить профиль

```http
PUT /api/v1/users/me
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "John Smith",
  "phone": "+1234567891"
}
```

### Справочники (Dictionaries)

#### Типы объектов

```http
GET /api/v1/dictionaries/types
```

**Ответ:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Квартира",
      "name_en": "Apartment"
    },
    {
      "id": 2,
      "name": "Дом",
      "name_en": "House"
    }
  ]
}
```

#### Регионы

```http
GET /api/v1/dictionaries/regions
```

**Ответ:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Москва",
      "name_en": "Moscow",
      "parent_id": null
    },
    {
      "id": 2,
      "name": "Центральный округ",
      "name_en": "Central District",
      "parent_id": 1
    }
  ]
}
```

## Обработка ошибок

### HTTP статус коды

| Код | Описание |
|-----|----------|
| 200 | OK - Запрос выполнен успешно |
| 201 | Created - Объект создан |
| 204 | No Content - Объект удален |
| 400 | Bad Request - Некорректный запрос |
| 401 | Unauthorized - Требуется аутентификация |
| 403 | Forbidden - Недостаточно прав |
| 404 | Not Found - Объект не найден |
| 422 | Unprocessable Entity - Ошибка валидации |
| 429 | Too Many Requests - Превышен лимит запросов |
| 500 | Internal Server Error - Внутренняя ошибка сервера |

### Формат ошибки

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": [
      {
        "field": "email",
        "message": "Email is required"
      },
      {
        "field": "price",
        "message": "Price must be a positive number"
      }
    ]
  }
}
```

### Коды ошибок

| Код | Описание |
|-----|----------|
| VALIDATION_ERROR | Ошибка валидации данных |
| AUTHENTICATION_FAILED | Ошибка аутентификации |
| PERMISSION_DENIED | Недостаточно прав |
| NOT_FOUND | Объект не найден |
| DUPLICATE_ENTRY | Дубликат записи |
| RATE_LIMIT_EXCEEDED | Превышен лимит запросов |
| INTERNAL_ERROR | Внутренняя ошибка |

## Rate Limiting

### Лимиты

- **Аутентифицированные пользователи:** 1000 запросов/час
- **Неаутентифицированные:** 100 запросов/час

### Заголовки ответа

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1642867200
```

### Превышение лимита

```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Rate limit exceeded. Try again in 3600 seconds",
    "retry_after": 3600
  }
}
```

## Пагинация

### Параметры

- `page` - номер страницы (начиная с 1)
- `per_page` - количество элементов (по умолчанию 20, макс 100)

### Ответ с пагинацией

```json
{
  "data": [...],
  "pagination": {
    "current_page": 2,
    "per_page": 20,
    "total": 150,
    "total_pages": 8,
    "next_page": 3,
    "prev_page": 1,
    "first_page_url": "/api/v1/data?page=1",
    "last_page_url": "/api/v1/data?page=8",
    "next_page_url": "/api/v1/data?page=3",
    "prev_page_url": "/api/v1/data?page=1"
  }
}
```

## Фильтрация и сортировка

### Фильтры

Примеры использования:

```http
# Фильтр по одному значению
GET /api/v1/data?status=active

# Фильтр по нескольким значениям
GET /api/v1/data?type_id=1,2,3

# Диапазон значений
GET /api/v1/data?price_min=1000000&price_max=2000000

# Поиск
GET /api/v1/data?search=квартира+москва
```

### Сортировка

```http
# Сортировка по возрастанию
GET /api/v1/data?sort=price&order=asc

# Сортировка по убыванию
GET /api/v1/data?sort=date_create&order=desc

# Множественная сортировка
GET /api/v1/data?sort=region_id,price&order=asc,desc
```

## Webhooks

### Регистрация webhook

```http
POST /api/v1/webhooks
Content-Type: application/json
Authorization: Bearer {token}

{
  "url": "https://yoursite.com/webhook",
  "events": ["data.created", "data.updated", "data.deleted"],
  "secret": "your_webhook_secret"
}
```

### События

- `data.created` - Создан новый объект
- `data.updated` - Объект обновлен
- `data.deleted` - Объект удален
- `client.created` - Создан новый клиент
- `user.registered` - Зарегистрирован новый пользователь

### Формат webhook запроса

```http
POST https://yoursite.com/webhook
Content-Type: application/json
X-Webhook-Signature: sha256=abc123...

{
  "event": "data.created",
  "timestamp": 1642867200,
  "data": {
    "id": 123,
    "title": "New Property",
    "status": "active"
  }
}
```

### Проверка подписи

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$expected = 'sha256=' . hash_hmac('sha256', $payload, $webhook_secret);

if (hash_equals($expected, $signature)) {
    // Подпись валидна
}
```

## Примеры использования

### PHP

```php
<?php

// Получение токена
$ch = curl_init('http://yourdomain.com/api/v1/auth/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'username' => 'user@example.com',
    'password' => 'password'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$data = json_decode($response, true);
$token = $data['token'];

// Получение списка объектов
$ch = curl_init('http://yourdomain.com/api/v1/data?page=1&per_page=10');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token
]);

$response = curl_exec($ch);
$objects = json_decode($response, true);

print_r($objects);
```

### JavaScript (Fetch API)

```javascript
// Получение токена
async function getToken() {
  const response = await fetch('http://yourdomain.com/api/v1/auth/token', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      username: 'user@example.com',
      password: 'password'
    })
  });
  
  const data = await response.json();
  return data.token;
}

// Получение списка объектов
async function getData() {
  const token = await getToken();
  
  const response = await fetch('http://yourdomain.com/api/v1/data?page=1&per_page=10', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  console.log(data);
}

getData();
```

### Python (requests)

```python
import requests

# Получение токена
auth_response = requests.post('http://yourdomain.com/api/v1/auth/token', json={
    'username': 'user@example.com',
    'password': 'password'
})

token = auth_response.json()['token']

# Получение списка объектов
headers = {
    'Authorization': f'Bearer {token}'
}

response = requests.get('http://yourdomain.com/api/v1/data', 
                       params={'page': 1, 'per_page': 10},
                       headers=headers)

data = response.json()
print(data)
```

## Поддержка и помощь

### Документация
- Официальная документация: https://www.sitebill.ru/docs/api/
- GitHub репозиторий: https://github.com/rumantic/cms

### Контакты
- Email: kondin@etown.ru
- Официальный сайт: https://www.sitebill.ru/

### Changelog
- v1.0.0 (2025-01-01) - Первый релиз API
- Актуальный changelog: https://www.sitebill.ru/docs/api/changelog
