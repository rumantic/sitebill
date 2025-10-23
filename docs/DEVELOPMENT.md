# Руководство разработчика CMS Sitebill

## Настройка окружения разработки

### Системные требования

- **OS:** Linux (рекомендуется), Windows, macOS
- **PHP:** 7.1 или выше
- **MySQL/MariaDB:** 5.6+/10.0+
- **Apache/Nginx:** 2.4+/1.18+
- **Git:** Для контроля версий
- **Composer:** Опционально, для Entity системы

### Установка локального окружения

#### Вариант 1: XAMPP/MAMP (Рекомендуется для начинающих)

1. Скачайте и установите XAMPP с https://www.apachefriends.org/

2. Клонируйте репозиторий:
```bash
cd /opt/lampp/htdocs/  # Linux
# или C:\xampp\htdocs\ для Windows
git clone https://github.com/rumantic/cms.git sitebill
```

3. Настройте виртуальный хост (опционально):
```apache
# В httpd-vhosts.conf
<VirtualHost *:80>
    ServerName sitebill.local
    DocumentRoot "/opt/lampp/htdocs/sitebill"
    <Directory "/opt/lampp/htdocs/sitebill">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. Добавьте в hosts файл:
```
127.0.0.1 sitebill.local
```

#### Вариант 2: Docker (Рекомендуется для продвинутых)

Создайте `docker-compose.yml`:

```yaml
version: '3.8'

services:
  web:
    image: php:7.4-apache
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
    environment:
      - APACHE_DOCUMENT_ROOT=/var/www/html
    
  db:
    image: mariadb:10.6
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: sitebill
      MYSQL_USER: sitebill
      MYSQL_PASSWORD: sitebill
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
  
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: root

volumes:
  db_data:
```

Запуск:
```bash
docker-compose up -d
```

#### Вариант 3: Нативная установка (Linux)

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 php php-mysql php-gd php-mbstring php-xml php-curl mariadb-server git

# Клонирование
cd /var/www/html
sudo git clone https://github.com/rumantic/cms.git sitebill
sudo chown -R www-data:www-data sitebill
sudo chmod -R 755 sitebill
```

### Настройка базы данных

1. Создайте базу данных:
```sql
CREATE DATABASE sitebill CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sitebill'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON sitebill.* TO 'sitebill'@'localhost';
FLUSH PRIVILEGES;
```

2. Запустите установщик:
```
http://localhost/sitebill/install/
```

### Настройка IDE

#### PhpStorm (Рекомендуется)

1. **Откройте проект:**
   - File → Open → Выберите папку sitebill

2. **Настройте PHP интерпретатор:**
   - Settings → Languages & Frameworks → PHP
   - Выберите PHP 7.1+

3. **Настройте Code Style:**
   - Settings → Editor → Code Style → PHP
   - Set from... → PSR-1/PSR-2

4. **Настройте Database Tools:**
   - Database → + → MySQL
   - Host: localhost, Database: sitebill

5. **Включите Xdebug:**
   - Settings → Languages & Frameworks → PHP → Debug
   - Xdebug port: 9003

#### VS Code

Установите расширения:
```json
{
  "recommendations": [
    "bmewburn.vscode-intelephense-client",
    "xdebug.php-debug",
    "mrmlnc.vscode-apache",
    "zobo.php-intellisense"
  ]
}
```

Настройте `.vscode/launch.json`:
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      }
    }
  ]
}
```

## Структура проекта для разработки

```
sitebill/
├── .git/                   # Git репозиторий
├── .gitignore              # Исключения Git
├── index.php               # Точка входа
├── admin/                  # Админка (точка входа)
├── apps/                   # Модули приложения
│   ├── system/             # Ядро системы
│   ├── admin/              # Админ модуль
│   ├── api/                # API модуль
│   └── [your_module]/      # Ваш модуль
├── cache/                  # Кеш (не комитится)
├── entity/                 # Entity система
├── img/                    # Медиа файлы
│   └── data/               # Загрузки (не комитятся)
├── inc/                    # Настройки (не комитится)
│   └── db.inc.php          # Настройки БД
├── template/               # Шаблоны
│   ├── frontend/           # Frontend шаблоны
│   └── backend/            # Backend шаблоны
├── third/                  # Сторонние библиотеки
└── docs/                   # Документация
```

## Workflow разработки

### 1. Создание новой ветки

```bash
# Обновите master
git checkout master
git pull origin master

# Создайте feature ветку
git checkout -b feature/my-new-feature

# Или bugfix ветку
git checkout -b bugfix/issue-123
```

### 2. Разработка модуля

#### Структура модуля

Создайте новый модуль:

```bash
mkdir -p apps/mymodule/lib
mkdir -p apps/mymodule/template
mkdir -p apps/mymodule/language
```

#### XML описание (`apps/mymodule/mymodule.xml`)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<install>
    <name>mymodule</name>
    <creationDate>January 2025</creationDate>
    <author>Your Name</author>
    <authorEmail>you@example.com</authorEmail>
    <authorUrl>https://example.com</authorUrl>
    <copyright>Copyright (C) 2025 Your Company</copyright>
    <license>MIT</license>
    <version>1.0.0</version>
    <description>My awesome module</description>
    <backendMenu>true</backendMenu>
    <administration>
        <menu>My Module</menu>
    </administration>
</install>
```

#### Основной класс (`apps/mymodule/lib/mymodule.php`)

```php
<?php

class MyModule {
    protected $sitebill;
    protected $db;
    
    public function __construct() {
        $this->sitebill = new SiteBill();
        $this->db = SiteBill::getDB();
    }
    
    public function main() {
        // Получаем действие
        $action = $this->sitebill->getRequestValue('do');
        
        // Маршрутизация
        switch($action) {
            case 'list':
                return $this->actionList();
            case 'view':
                return $this->actionView();
            case 'edit':
                return $this->actionEdit();
            case 'save':
                return $this->actionSave();
            case 'delete':
                return $this->actionDelete();
            default:
                return $this->actionList();
        }
    }
    
    protected function actionList() {
        // Получаем данные
        $items = $this->getItems();
        
        // Передаем в шаблон
        $this->sitebill->assignSmartyVariables(array(
            'items' => $items
        ));
        
        return true;
    }
    
    protected function actionView() {
        $id = $this->sitebill->getRequestValue('id');
        $item = $this->getItem($id);
        
        $this->sitebill->assignSmartyVariables(array(
            'item' => $item
        ));
        
        return true;
    }
    
    protected function getItems() {
        $query = "SELECT * FROM " . DB_PREFIX . "_mymodule ORDER BY id DESC";
        $this->db->exec($query);
        
        $items = array();
        while ($this->db->fetch_assoc()) {
            $items[] = $this->db->row;
        }
        
        return $items;
    }
    
    protected function getItem($id) {
        $query = "SELECT * FROM " . DB_PREFIX . "_mymodule WHERE id = " . intval($id);
        $this->db->exec($query);
        $this->db->fetch_assoc();
        
        return $this->db->row;
    }
}
```

#### Шаблон (`apps/mymodule/template/list.tpl`)

```smarty
<div class="mymodule-list">
    <h1>My Module Items</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$items item=item}
            <tr>
                <td>{$item.id}</td>
                <td>{$item.title}</td>
                <td>
                    <a href="?action=mymodule&do=view&id={$item.id}">View</a>
                    <a href="?action=mymodule&do=edit&id={$item.id}">Edit</a>
                    <a href="?action=mymodule&do=delete&id={$item.id}" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
```

### 3. Тестирование

#### Ручное тестирование

1. Откройте модуль в браузере:
   ```
   http://localhost/sitebill/?action=mymodule&do=list
   ```

2. Проверьте все действия:
   - Список
   - Просмотр
   - Редактирование
   - Удаление

#### Использование Entity/Eloquent

Создайте модель (`entity/src/app/Models/MyModel.php`):

```php
<?php

namespace Sitebill\Entity\App\Models;

use Illuminate\Database\Eloquent\Model;

class MyModel extends Model {
    protected $table = 'mymodule';
    public $timestamps = false;
    
    protected $fillable = [
        'title',
        'description',
        'status'
    ];
    
    protected $casts = [
        'status' => 'boolean'
    ];
    
    // Relationships
    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

Использование в модуле:

```php
use Sitebill\Entity\App\Models\MyModel;

class MyModule {
    protected function getItems() {
        return MyModel::where('status', true)
            ->orderBy('id', 'desc')
            ->get();
    }
    
    protected function getItem($id) {
        return MyModel::find($id);
    }
    
    protected function saveItem($data) {
        $item = MyModel::findOrNew($data['id']);
        $item->fill($data);
        $item->save();
        return $item;
    }
    
    protected function deleteItem($id) {
        return MyModel::destroy($id);
    }
}
```

### 4. Debugging

#### Включите debug режим

В `index.php` или `settings.ini.php`:

```php
define('DEBUG_MODE', true);
define('DEBUG_ENABLED', true);
define('LOG_ENABLED', true);
ini_set('display_errors', 'On');
error_reporting(E_ALL);
```

#### Используйте логирование

```php
// Простое логирование
$sitebill->writeLog('Debug message', 'NOTICE');
$sitebill->writeLog('Error occurred', 'ERROR');
$sitebill->writeLog('Warning message', 'WARNING');

// Логирование с данными
$sitebill->writeLog('User data: ' . print_r($user, true), 'NOTICE');
```

#### Xdebug

Настройка в `php.ini`:

```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=127.0.0.1
```

### 5. Git workflow

#### Коммиты

```bash
# Проверьте изменения
git status
git diff

# Добавьте файлы
git add apps/mymodule/

# Коммит с информативным сообщением
git commit -m "Add MyModule with CRUD functionality"

# Или интерактивный коммит
git add -p
git commit -v
```

#### Соглашения о коммитах

```
type(scope): subject

body

footer
```

Примеры:
```
feat(mymodule): Add list and view actions
fix(api): Correct authentication bug
docs(readme): Update installation instructions
refactor(sitebill): Improve performance of getConfig
```

#### Push и Pull Request

```bash
# Push в вашу ветку
git push origin feature/my-new-feature

# Создайте Pull Request на GitHub
# Или через CLI
gh pr create --title "Add MyModule" --body "Implements CRUD for MyModule"
```

### 6. Code Review

При создании PR убедитесь:

- [ ] Код соответствует стандартам проекта
- [ ] Добавлена документация для новых функций
- [ ] Нет закомиченных файлов настроек (db.inc.php, settings.ini.php)
- [ ] Код протестирован
- [ ] Нет SQL injection уязвимостей
- [ ] XSS защита присутствует
- [ ] Используются prepared statements

## Инструменты разработчика

### Composer зависимости

Если используете Entity систему:

```bash
cd entity/
composer install
```

### NPM для frontend (если используете современные шаблоны)

```bash
cd template/frontend/aidom/
npm install
npm run build
```

### Git Hooks

Создайте `.git/hooks/pre-commit`:

```bash
#!/bin/bash

# Проверка PHP синтаксиса
for FILE in $(git diff --cached --name-only --diff-filter=ACMR | grep "\.php$")
do
    php -l $FILE
    if [ $? -ne 0 ]; then
        echo "PHP syntax error in $FILE"
        exit 1
    fi
done

# Проверка на debug кода
if git diff --cached | grep -E "var_dump|print_r|console\.log" > /dev/null; then
    echo "Warning: Debug code found in commit"
    exit 1
fi

exit 0
```

Сделайте исполняемым:
```bash
chmod +x .git/hooks/pre-commit
```

## Частые проблемы и решения

### Проблема: Белый экран (WSOD)

**Решение:**
1. Включите отображение ошибок:
   ```php
   ini_set('display_errors', 'On');
   error_reporting(E_ALL);
   ```

2. Проверьте логи Apache/Nginx:
   ```bash
   tail -f /var/log/apache2/error.log
   ```

3. Проверьте права на файлы:
   ```bash
   chmod -R 755 /path/to/sitebill
   chown -R www-data:www-data /path/to/sitebill
   ```

### Проблема: База данных не подключается

**Решение:**
1. Проверьте настройки в `inc/db.inc.php`
2. Убедитесь, что MySQL запущен:
   ```bash
   systemctl status mysql
   ```
3. Проверьте права пользователя БД

### Проблема: Шаблоны не компилируются

**Решение:**
1. Очистите кеш Smarty:
   ```bash
   rm -rf cache/compile/*
   rm -rf cache/smarty/*
   ```

2. Проверьте права на папки:
   ```bash
   chmod -R 777 cache/compile
   chmod -R 777 cache/smarty
   ```

### Проблема: 404 на всех страницах (кроме главной)

**Решение:**
1. Убедитесь, что mod_rewrite включен (Apache):
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. Проверьте `.htaccess` файл
3. Убедитесь, что AllowOverride включен в конфигурации Apache

## Лучшие практики

### Безопасность

1. **Всегда валидируйте ввод:**
   ```php
   $id = intval($_GET['id']);
   $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
   ```

2. **Используйте prepared statements:**
   ```php
   $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]);
   ```

3. **Экранируйте вывод:**
   ```smarty
   {$user_input|escape:'html'}
   ```

4. **Проверяйте CSRF токены:**
   ```php
   if (!SiteBill::checkCSRFToken($_POST['token'])) {
       die('CSRF token mismatch');
   }
   ```

### Производительность

1. **Кешируйте дорогие операции**
2. **Используйте индексы БД**
3. **Минимизируйте запросы к БД**
4. **Используйте Eloquent для сложных запросов**

### Код-стайл

1. **Используйте осмысленные имена переменных**
2. **Комментируйте сложную логику**
3. **Следуйте PSR стандартам (где возможно)**
4. **Не дублируйте код (DRY principle)**

## Полезные ссылки

- [Официальный сайт CMS Sitebill](https://www.sitebill.ru/)
- [Smarty Documentation](https://www.smarty.net/docs/en/)
- [Laravel Eloquent Docs](https://laravel.com/docs/eloquent)
- [PHP The Right Way](https://phptherightway.com/)
