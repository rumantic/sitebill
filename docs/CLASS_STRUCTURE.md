# Структура классов CMS Sitebill

## Основные классы системы

### 1. Класс SiteBill

**Файл:** `/apps/system/lib/sitebill.php`

Главный класс системы, предоставляющий базовую функциональность.

#### Основные свойства

```php
class SiteBill {
    // Сообщение об ошибке
    var $error_message = false;
    
    // Директории для загрузок и хранения
    var $uploadify_dir = '/cache/upl/';
    var $storage_dir = '/img/data/';
    
    // Статические свойства
    protected static $config_loaded = false;
    protected static $config_array = array();
    protected static $storage = array();
    protected static $Heaps = array();
    
    // Локальные настройки
    protected static $localSettings = false;
    
    // Grid конструктор
    public static $_grid_constructor_local = null;
    
    // Cookie домен
    public static $_cookiedomain = '';
    
    // CSRF токен
    public static $_csrf_token = '';
    
    // Request данные
    public static $_request = null;
    
    // Illuminate компоненты
    public static $illuminate_database_registred = false;
    public static $illuminate_request_registred = false;
    public static $iRequest = null; // Request instance
    
    // Event Dispatcher
    private static $iEventDispatcher; // Illuminate\Events\Dispatcher
    
    // Debug Bar
    private static $debugbar; // DebugBar\StandardDebugBar
}
```

#### Ключевые методы

```php
// Конфигурация
public function getConfigValue($key)
public function setConfigValue($key, $value)

// Работа с запросами
public static function initRequest()
public function getRequestValue($key)
public function setRequestValue($key, $value)

// Маршрутизация
public static function getClearRequestURI()
public function createUrlTpl($uri, $params, $locale)
public function go301($url)

// Работа с медиа
public function uploadImage($field_name)
public function deleteImage($image_id)
public function getMediaPath($filename)

// Безопасность
public static function generateCSRFToken()
public static function checkCSRFToken($token)

// Логирование
public function writeLog($message, $level = 'NOTICE')

// Шаблоны
public static function getTemplate($template_name)
public function assignSmartyVariables($vars)

// Database
public static function getDB()
public function query($sql)
```

### 2. Класс Db (MySQL)

**Файл:** `/apps/system/lib/db/MySQL.php`

Класс для работы с базой данных (Legacy подход).

#### Структура класса

```php
class Db {
    var $host;       // Хост БД
    var $dbname;     // Имя БД
    var $login;      // Логин
    var $password;   // Пароль
    var $id = false; // Connection ID
    var $error = ""; // Текст ошибки
    var $success = false;
    var $errno = 0;
    var $query = "";
    var $res = false;
    var $row;        // Текущая строка результата
    
    // Методы
    function __construct($db_host, $db_name, $db_user, $db_pass)
    function connect()
    function close()
    function exec($query)
    function fetch_assoc()
    function fetch_row()
    function num_rows()
    function insert_id()
    function escape($string)
}
```

#### Пример использования

```php
$db = new Db(DB_HOST, DB_BASE, DB_USER, DB_PASS);
$db->exec("SELECT * FROM " . DB_PREFIX . "_data WHERE id = ?");
$db->fetch_assoc();
$result = $db->row;
```

### 3. Класс Init

**Файл:** `/apps/system/lib/system/init.php`

Класс инициализации системы (большинство функций deprecated).

```php
class Init {
    // Инициализация глобальных переменных
    function initGlobals()
}
```

### 4. Класс SiteBill_Krascap

**Файл:** `/apps/system/lib/sitebill_krascap.php`

Главный обработчик запросов и маршрутизатор системы.

```php
class SiteBill_Krascap {
    // Главный метод обработки запроса
    public function main()
    
    // Маршрутизация
    protected function route()
    
    // Выполнение действия модуля
    protected function executeAction($module, $action)
    
    // Подключение модуля
    protected function loadModule($module_name)
}
```

### 5. Entity модели (Eloquent ORM)

**Расположение:** `/entity/src/app/Models/`

Современные модели на основе Laravel Eloquent.

#### Базовая структура модели

```php
namespace Sitebill\Entity\App\Models;

use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model {
    // Имя таблицы
    protected $table = 'prefix_table_name';
    
    // Первичный ключ
    protected $primaryKey = 'id';
    
    // Автоинкремент
    public $incrementing = true;
    
    // Timestamps
    public $timestamps = false;
    
    // Fillable поля
    protected $fillable = [
        'field1',
        'field2',
        'field3'
    ];
    
    // Guarded поля
    protected $guarded = ['id'];
    
    // Casts
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'settings' => 'array'
    ];
    
    // Связи
    public function relatedModel() {
        return $this->hasMany(RelatedModel::class);
    }
}
```

#### Примеры Entity моделей

**Data Model** (`/entity/src/app/Models/Data.php`):
```php
namespace Sitebill\Entity\App\Models;

use Illuminate\Database\Eloquent\Model;

class Data extends Model {
    protected $table = 'prefix_data';
    public $timestamps = false;
    
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'status'
    ];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function images() {
        return $this->hasMany(Image::class, 'data_id');
    }
}
```

**Client Model** (`/entity/src/app/Models/Client.php`):
```php
namespace Sitebill\Entity\App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model {
    protected $table = 'prefix_client';
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company'
    ];
    
    protected $casts = [
        'created_at' => 'datetime'
    ];
}
```

### 6. Модули приложений

#### Базовая структура модуля

```php
class Module_Name {
    // Ссылка на главный объект
    protected $sitebill;
    
    // Database
    protected $db;
    
    // Конфигурация модуля
    protected $config = array();
    
    // Конструктор
    public function __construct() {
        $this->sitebill = new SiteBill();
        $this->db = SiteBill::getDB();
    }
    
    // Главный метод
    public function main() {
        // Логика модуля
    }
    
    // Действия
    public function actionList() {
        // Список элементов
    }
    
    public function actionView($id) {
        // Просмотр элемента
    }
    
    public function actionEdit($id) {
        // Редактирование
    }
    
    public function actionDelete($id) {
        // Удаление
    }
    
    // Вспомогательные методы
    protected function getData($id) {
        // Получение данных
    }
    
    protected function saveData($data) {
        // Сохранение данных
    }
    
    protected function validateData($data) {
        // Валидация
    }
}
```

#### Пример: Admin модуль

```php
class Admin_Manager {
    protected $sitebill;
    protected $db;
    
    public function __construct() {
        $this->sitebill = new SiteBill();
        $this->db = SiteBill::getDB();
    }
    
    public function main() {
        // Проверка прав доступа
        if (!$this->checkPermissions()) {
            return false;
        }
        
        // Получение действия
        $action = $this->sitebill->getRequestValue('do');
        
        // Маршрутизация действий
        switch($action) {
            case 'list':
                return $this->actionList();
            case 'edit':
                return $this->actionEdit();
            case 'delete':
                return $this->actionDelete();
            default:
                return $this->actionList();
        }
    }
    
    protected function checkPermissions() {
        return $this->sitebill->checkUserRole('admin');
    }
}
```

### 7. Manager классы

Классы для управления различными сущностями системы.

#### Structure Manager

**Файл:** `/apps/system/lib/admin/structure/structure_manager.php`

```php
class Structure_Manager {
    // Управление структурой сайта
    public function getStructure()
    public function addNode($parent_id, $data)
    public function updateNode($id, $data)
    public function deleteNode($id)
    public function moveNode($id, $new_parent_id)
}
```

#### Data Manager

**Файл:** `/apps/system/lib/admin/data/data_manager.php`

```php
class Data_Manager {
    // Управление данными
    public function getList($params)
    public function getItem($id)
    public function create($data)
    public function update($id, $data)
    public function delete($id)
    
    // Фильтрация и поиск
    public function filter($conditions)
    public function search($query)
    
    // Пагинация
    public function paginate($page, $per_page)
}
```

#### Menu Manager

**Файл:** `/apps/system/lib/admin/menu/menu_manager.php`

```php
class Menu_Manager {
    // Управление меню
    public function getMenuItems($menu_id)
    public function addMenuItem($menu_id, $data)
    public function updateMenuItem($id, $data)
    public function deleteMenuItem($id)
    public function reorderItems($menu_id, $order)
}
```

### 8. Вспомогательные классы

#### Grid_Constructor

Класс для построения таблиц данных.

```php
class Grid_Constructor {
    protected $columns = array();
    protected $data = array();
    protected $filters = array();
    
    public function addColumn($name, $config)
    public function setData($data)
    public function addFilter($name, $config)
    public function render()
}
```

#### Form_Constructor

Класс для построения форм.

```php
class Form_Constructor {
    protected $fields = array();
    protected $validation = array();
    
    public function addField($name, $type, $config)
    public function setValidation($rules)
    public function validate($data)
    public function render()
}
```

### 9. Trait система

**Расположение:** `/apps/system/traits/`

#### AfterRequestInitTrait

```php
trait AfterRequestInitTrait {
    public function afterRequestInit() {
        // Код выполняется после инициализации запроса
    }
}
```

### 10. Service Provider (Entity)

**Файл:** `/entity/src/EntityServiceProvider.php`

```php
namespace Sitebill\Entity;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;

class EntityServiceProvider extends ServiceProvider {
    public function register() {
        // Регистрация Eloquent
        $this->registerEloquent();
    }
    
    public function boot() {
        // Загрузка конфигурации
        $this->loadConfiguration();
    }
    
    protected function registerEloquent() {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => DB_HOST,
            'database' => DB_BASE,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => DB_PREFIX . '_',
        ]);
        
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
```

## Иерархия классов

```
SiteBill (Core)
    │
    ├── Module Classes
    │   ├── Admin_Manager
    │   ├── API_Controller
    │   ├── Billing_Manager
    │   └── ...
    │
    ├── Manager Classes
    │   ├── Structure_Manager
    │   ├── Data_Manager
    │   ├── Menu_Manager
    │   └── ...
    │
    ├── Helper Classes
    │   ├── Grid_Constructor
    │   ├── Form_Constructor
    │   └── ...
    │
    └── Utility Classes
        ├── Db
        ├── Init
        └── SiteBill_Krascap

Entity (Modern ORM)
    │
    └── Eloquent Models
        ├── Data
        ├── Client
        ├── User
        └── ...
```

## Взаимодействие классов

```
┌─────────────────┐
│   Request       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   SiteBill      │◄──────┐
│   Krascap       │       │
└────────┬────────┘       │
         │                │
         ▼                │
┌─────────────────┐       │
│   Module        │───────┤
│   Class         │       │
└────────┬────────┘       │
         │                │
         ▼                │
┌─────────────────┐       │
│   Manager       │───────┘
│   Class         │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Database /    │
│   Entity        │
└─────────────────┘
```

## Именование классов

### Конвенции

1. **Module Classes** - `Module_Name` (snake_case)
2. **Manager Classes** - `Entity_Manager` (snake_case)
3. **Entity Models** - `ModelName` (PascalCase)
4. **Helper Classes** - `Helper_Name` (snake_case)

### Namespace

```php
// Entity models
namespace Sitebill\Entity\App\Models;

// Service Providers
namespace Sitebill\Entity;

// Legacy classes (no namespace)
class SiteBill { }
```

## Автозагрузка классов

### PSR-4 (Entity система)

```json
{
  "autoload": {
    "psr-4": {
      "Sitebill\\Entity\\": "entity/src/"
    }
  }
}
```

### Manual (Legacy)

```php
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/module/lib/module.php');
```

## Паттерны использования

### Singleton pattern

```php
class SiteBill {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### Factory pattern

```php
class ModuleFactory {
    public static function create($module_name) {
        $class_name = ucfirst($module_name) . '_Manager';
        if (class_exists($class_name)) {
            return new $class_name();
        }
        return false;
    }
}
```

### Repository pattern (через Eloquent)

```php
class DataRepository {
    protected $model;
    
    public function __construct(Data $model) {
        $this->model = $model;
    }
    
    public function find($id) {
        return $this->model->find($id);
    }
    
    public function all() {
        return $this->model->all();
    }
}
```
