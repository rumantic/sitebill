# Стек технологий CMS Sitebill

## Backend

### Основной язык
- **PHP 7.1+** - Основной язык разработки
  - Поддержка PSR-4 автозагрузки
  - Composer для управления зависимостями
  - Namespace поддержка в Entity системе

### Фреймворки и библиотеки

#### Компоненты Laravel
**Версия:** 5.x - 8.x компоненты

Используемые пакеты:
- **Illuminate/Database** - Eloquent ORM для современной работы с БД
  ```php
  "illuminate/database": "^8.0"
  ```
- **Illuminate/Events** - Event Dispatcher для событийной архитектуры
- **Illuminate/Http** - HTTP Request обработка

#### Template Engine
- **Smarty 3.x** - Основной шаблонизатор
  - Расположение: `/third/smarty/`
  - Компиляция шаблонов в PHP код
  - Кеширование для производительности
  - Разделение логики и представления

### База данных

#### Поддерживаемые СУБД
- **MySQL 5.6+** (Рекомендуется)
- **MariaDB 10.0+**

#### Драйверы
- **mysql** - Legacy драйвер (deprecated)
- **PDO MySQL** - Для Eloquent ORM
- **MySQLi** - Альтернативный драйвер

#### Миграции
Система не использует классические миграции, но поддерживает:
- SQL скрипты установки
- Update скрипты для обновлений
- Автоматическое создание таблиц при установке модулей

### Сторонние библиотеки

#### Обязательные

1. **Smarty Template Engine**
   - Версия: 3.x
   - Путь: `/third/smarty/`

2. **IDNA Convert**
   - Путь: `/third/idna_convert/`
   - Назначение: Поддержка интернациональных доменных имен

3. **KCAPTCHA**
   - Путь: `/third/kcaptcha/`
   - Назначение: Генерация CAPTCHA

4. **Simple HTML DOM**
   - Путь: `/third/simple_html_dom/`
   - Назначение: Парсинг HTML

5. **OAuth Libraries**
   - Путь: `/third/oauth/`
   - Назначение: OAuth авторизация

#### Дополнительные библиотеки

Расположение: `/apps/third/`

- **Doctrine Inflector** - Pluralization/singularization
- **Sabre XML** - XML парсинг и генерация
- **DomPDF** - Генерация PDF документов
- **PHP CSS Parser** - Парсинг CSS
- **Random Compat** - Полифил для random_bytes()
- **Composer** - Автозагрузка классов

## Frontend

### JavaScript

#### Основные библиотеки

1. **jQuery**
   - Версия: 1.x - 3.x (зависит от шаблона)
   - Основная JS библиотека

2. **Angular.js / Angular**
   - Используется для Grid системы
   - Admin панель использует Angular компоненты
   - Расположение: `/apps/angular/`

3. **Bootstrap JavaScript**
   - Компоненты UI (modals, dropdowns, etc.)
   - Версия зависит от шаблона (3.x или 4.x)

#### Дополнительные библиотеки

- **Isotope** - Фильтрация и сортировка
- **OwlCarousel** - Карусели
- **Fotorama** - Галереи изображений
- **Bootstrap Select** - Улучшенные select элементы
- **Bootstrap Fileinput** - Загрузка файлов
- **Autosize** - Автоматический resize textarea
- **ScrollTo** - Плавная прокрутка
- **HTML5 Gallery** - HTML5 галереи
- **intl-tel-input** - Международные телефонные номера

### CSS

#### Препроцессоры и фреймворки

1. **Bootstrap**
   - Версии: 3.x и 4.x
   - Основной CSS фреймворк для responsive дизайна

2. **Tailwind CSS** (в новых шаблонах)
   - Используется в шаблоне `aidom`
   - Utility-first CSS framework

#### Custom CSS
- Стили специфичные для CMS
- Темизация для разных шаблонов
- Responsive медиа-запросы

### Build Tools

#### Современные шаблоны (aidom)
```json
{
  "devDependencies": {
    "tailwindcss": "^3.x",
    "postcss": "^8.x",
    "autoprefixer": "^10.x"
  }
}
```

## Интеграции

### Платежные системы
- **Сбербанк** - Онлайн оплата
- **ЮKassa (Яндекс.Касса)** - Платежный шлюз
- **PayPal** - Международные платежи

### Экспорт площадок
- **Avito** - Экспорт объявлений
  - Module: `/apps/avitoexporter/`
- **CIAN** - Экспорт недвижимости
  - Module: `/apps/cianexporter2/`
- **Яндекс.Недвижимость** - XML feed
- **ЦИАН API** - Двусторонняя интеграция

### CRM и мессенджеры
- **Bitrix24** - CRM интеграция
  - Module: `/apps/bitrix24/`
- **AmoCRM** - CRM система
- **Telegram** - Уведомления и боты
- **Email** - SMTP/Sendmail

### Карты
- **Яндекс.Карты** - Основной картографический сервис
- **Google Maps** - Альтернативный провайдер
- **OpenStreetMap** - Open source карты

### Аналитика
- **Яндекс.Метрика** - Web аналитика
- **Google Analytics** - Web аналитика
- Custom аналитика через `/apps/analytic/`

## Хранилище и кеширование

### Файловое хранилище
- **Локальная ФС** - Основное хранилище
  - Путь: `/img/data/`
  - Структурированное хранение по датам
- **CDN Ready** - Поддержка CDN для статики

### Кеш системы

1. **Smarty Cache**
   - Путь: `/cache/smarty/`
   - Кеш скомпилированных шаблонов

2. **Database Cache**
   - Таблица: `{prefix}_cache`
   - Кеширование запросов и данных

3. **Opcode Cache**
   - OPcache (PHP 5.5+)
   - APCu (опционально)

### Сессии
- **PHP Sessions** - Файловые или DB сессии
- **Cookie** - Дополнительное хранение настроек

## DevOps & Deployment

### Веб-серверы
- **Apache 2.4+** (Рекомендуется)
  - mod_rewrite для ЧПУ
  - .htaccess конфигурация
- **Nginx** (Альтернатива)
  - Требует настройки rewrite rules

### PHP Настройки

Минимальные требования:
```ini
memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 300
```

Рекомендуемые расширения:
- `mysqli` или `pdo_mysql`
- `gd` или `imagick` - Обработка изображений
- `mbstring` - Многобайтовые строки
- `curl` - HTTP запросы
- `xml` - XML обработка
- `zip` - Работа с архивами
- `json` - JSON encode/decode

### Version Control
- **Git** - Основная VCS
- `.gitignore` - Исключение служебных файлов
  - `/cache/` - Кеш
  - `/inc/db.inc.php` - Настройки БД
  - `/img/data/` - Загруженные файлы
  - `node_modules/` - NPM зависимости

### Dependency Management

#### Backend
```json
// entity/composer.json
{
  "require": {
    "php": ">=7.1.0",
    "illuminate/database": "^8.0"
  }
}
```

#### Frontend (для современных шаблонов)
```json
// template/frontend/aidom/package.json
{
  "devDependencies": {
    "tailwindcss": "^3.x"
  }
}
```

## Безопасность

### Защита
- **SQL Injection** - Prepared statements, Eloquent ORM
- **XSS** - Smarty автоматическое экранирование
- **CSRF** - Token система
- **Session Security** - Secure cookies, session regeneration
- **File Upload** - Валидация MIME типов
- **Password Hashing** - PHP password_hash()

### SSL/TLS
- Поддержка HTTPS
- Secure cookies для сессий

## Мониторинг и логирование

### Логи
- **Error Logs** - PHP error log
- **Application Logs** - Кастомная система логирования
  ```php
  $sitebill->writeLog('message');
  ```
- **Access Logs** - Веб-сервер логи

### Debug Mode
```php
define('DEBUG_MODE', true);
define('DEBUG_ENABLED', true);
define('LOG_ENABLED', true);
```

### Профилирование
- **DebugBar** - Laravel DebugBar integration
  - Используется в dev режиме
  - SQL queries профилирование
  - Timeline запросов

## API & Web Services

### REST API
- **Формат:** JSON
- **Версионирование:** /api/v1/, /api/v2/
- **Аутентификация:** Token-based, Session

### SOAP (Legacy)
- Поддержка SOAP сервисов для интеграций
- XML-RPC для старых систем

### Webhooks
- Поддержка webhook'ов для событий
- Интеграция с внешними сервисами

## Поддержка браузеров

### Современные шаблоны
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Legacy поддержка
- IE 11 (ограниченная поддержка)
- Старые версии браузеров через полифилы

## Мобильная разработка

### Responsive Design
- Bootstrap Grid System
- Tailwind CSS (новые шаблоны)
- Mobile-first подход

### PWA (Частичная поддержка)
- Service Workers (в разработке)
- Offline режим (планируется)

## Локализация и интернационализация

### Языки
- Русский (основной)
- Английский
- Другие (через модуль multilanguage)

### Encoding
- **UTF-8** - Рекомендуется
- **Windows-1251** - Legacy поддержка
- **CP1251** - Database encoding (legacy)

## Документация и стандарты

### Кодинг стандарты
- **PSR-1** - Basic Coding Standard (частично)
- **PSR-4** - Autoloading Standard (Entity система)
- **PSR-12** - Extended Coding Style (рекомендуется)

### Документация кода
- PHPDoc комментарии
- Inline комментарии для сложной логики

## Инструменты разработки

### IDE
- PHPStorm (рекомендуется)
- VS Code с PHP расширениями
- NetBeans

### Debugging
- Xdebug
- PHP DebugBar
- Browser DevTools

### Testing
- Ручное тестирование
- Unit тесты (в разработке)
