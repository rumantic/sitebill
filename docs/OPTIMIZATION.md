# Руководство по оптимизации CMS Sitebill

## Оптимизация базы данных

### 1. Индексы

#### Обязательные индексы

Убедитесь, что следующие таблицы имеют правильные индексы:

```sql
-- Основная таблица данных
ALTER TABLE `{prefix}_data` 
ADD INDEX `idx_status` (`status`),
ADD INDEX `idx_user_id` (`user_id`),
ADD INDEX `idx_created` (`date_create`),
ADD INDEX `idx_region` (`region_id`);

-- Таблица клиентов
ALTER TABLE `{prefix}_client` 
ADD INDEX `idx_email` (`email`),
ADD INDEX `idx_active` (`active`);

-- Таблица изображений
ALTER TABLE `{prefix}_image` 
ADD INDEX `idx_data_id` (`data_id`),
ADD INDEX `idx_type` (`type`);

-- Таблица конфигурации
ALTER TABLE `{prefix}_config` 
ADD UNIQUE KEY `idx_config_key` (`config_key`);
```

#### Композитные индексы

Для часто используемых комбинаций фильтров:

```sql
-- Для поиска активных объектов конкретного пользователя
ALTER TABLE `{prefix}_data` 
ADD INDEX `idx_user_status` (`user_id`, `status`);

-- Для региональной фильтрации
ALTER TABLE `{prefix}_data` 
ADD INDEX `idx_region_type` (`region_id`, `type_id`);
```

### 2. Оптимизация запросов

#### Плохо
```php
// Запрос в цикле (N+1 проблема)
$items = $db->query("SELECT * FROM {prefix}_data");
while ($item = $db->fetch_assoc()) {
    $images = $db->query("SELECT * FROM {prefix}_image WHERE data_id = " . $item['id']);
    // ...
}
```

#### Хорошо
```php
// Используйте JOIN или Eloquent relationships
$query = "
    SELECT d.*, GROUP_CONCAT(i.filename) as images
    FROM {prefix}_data d
    LEFT JOIN {prefix}_image i ON i.data_id = d.id
    GROUP BY d.id
";

// Или через Eloquent
$items = Data::with('images')->get();
```

#### Используйте prepared statements

```php
// Плохо - SQL injection риск
$query = "SELECT * FROM {prefix}_data WHERE id = " . $_GET['id'];

// Хорошо - безопасно и кешируется
$stmt = $db->prepare("SELECT * FROM {prefix}_data WHERE id = ?");
$stmt->execute([$_GET['id']]);
```

### 3. Кеширование запросов

```php
class OptimizedDataManager {
    protected $cache_time = 3600; // 1 час
    
    public function getPopularItems() {
        $cache_key = 'popular_items';
        
        // Проверяем кеш
        $cached = $this->sitebill->getCache($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        
        // Выполняем запрос
        $items = Data::where('status', 'active')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();
        
        // Сохраняем в кеш
        $this->sitebill->setCache($cache_key, $items, $this->cache_time);
        
        return $items;
    }
}
```

### 4. Пагинация

Всегда используйте LIMIT для больших выборок:

```php
// Плохо - загружает все записи
$all_items = Data::all();

// Хорошо - загружает только текущую страницу
$items = Data::paginate(20);

// Еще лучше - курсор пагинация для больших данных
$items = Data::orderBy('id')->cursorPaginate(20);
```

## Оптимизация PHP кода

### 1. Autoloading и require

```php
// Плохо - загружается всегда, даже если не используется
require_once('/path/to/heavy_library.php');

// Хорошо - загружается только при необходимости
spl_autoload_register(function ($class_name) {
    $file = str_replace('_', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Еще лучше - используйте Composer autoload
require_once 'vendor/autoload.php';
```

### 2. Избегайте глобальных переменных

```php
// Плохо
global $sitebill;
global $db;
global $config;

// Хорошо - используйте свойства класса
class MyModule {
    protected $sitebill;
    protected $db;
    
    public function __construct() {
        $this->sitebill = SiteBill::getInstance();
        $this->db = SiteBill::getDB();
    }
}
```

### 3. Оптимизация циклов

```php
// Плохо - вызов count() на каждой итерации
for ($i = 0; $i < count($array); $i++) {
    // ...
}

// Хорошо - вычисляется один раз
$length = count($array);
for ($i = 0; $i < $length; $i++) {
    // ...
}

// Еще лучше - используйте foreach
foreach ($array as $item) {
    // ...
}
```

### 4. Ленивая загрузка

```php
class LazyLoader {
    protected $heavyObject = null;
    
    protected function getHeavyObject() {
        if ($this->heavyObject === null) {
            $this->heavyObject = new HeavyClass();
        }
        return $this->heavyObject;
    }
}
```

## Оптимизация Smarty шаблонов

### 1. Включите кеширование

```php
// В index.php или bootstrap файле
$smarty->caching = 1;
$smarty->cache_lifetime = 3600; // 1 час

// Для конкретного шаблона
$smarty->display('template.tpl', $cache_id);
```

### 2. Компиляция шаблонов

```php
// Убедитесь, что compile_check отключен в продакшене
$smarty->compile_check = false; // Проверка только в dev режиме
$smarty->force_compile = false; // Никогда не форсируйте компиляцию
```

### 3. Оптимизация в шаблонах

```smarty
{* Плохо - выполняется на каждой итерации *}
{foreach from=$items item=item}
    {if $config.show_images}
        <img src="{$item.image}">
    {/if}
{/foreach}

{* Хорошо - условие вне цикла *}
{if $config.show_images}
    {foreach from=$items item=item}
        <img src="{$item.image}">
    {/foreach}
{/if}
```

### 4. Минимизируйте вызовы модификаторов

```smarty
{* Плохо - модификатор в цикле *}
{foreach from=$items item=item}
    {$item.title|truncate:50}
{/foreach}

{* Хорошо - обработайте данные в PHP *}
{* В PHP: $item['short_title'] = truncate($item['title'], 50); *}
{foreach from=$items item=item}
    {$item.short_title}
{/foreach}
```

## Оптимизация медиа файлов

### 1. Оптимизация изображений

```php
class ImageOptimizer {
    public function optimizeImage($source, $destination, $quality = 85) {
        $info = getimagesize($source);
        
        // Создаем изображение в зависимости от типа
        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                imagejpeg($image, $destination, $quality);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                // PNG использует 0-9 для compression
                imagepng($image, $destination, 9);
                break;
        }
        
        imagedestroy($image);
    }
    
    public function createThumbnail($source, $destination, $max_width, $max_height) {
        list($width, $height) = getimagesize($source);
        
        // Вычисляем новые размеры
        $ratio = min($max_width / $width, $max_height / $height);
        $new_width = $width * $ratio;
        $new_height = $height * $ratio;
        
        // Создаем миниатюру
        $thumb = imagecreatetruecolor($new_width, $new_height);
        $source_image = imagecreatefromjpeg($source);
        
        imagecopyresampled(
            $thumb, $source_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $width, $height
        );
        
        imagejpeg($thumb, $destination, 85);
        imagedestroy($thumb);
        imagedestroy($source_image);
    }
}
```

### 2. Lazy loading изображений

```javascript
// В шаблоне
<img data-src="{$image.url}" class="lazy" alt="{$image.alt}">

<script>
document.addEventListener("DOMContentLoaded", function() {
    const lazyImages = document.querySelectorAll('.lazy');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
});
</script>
```

### 3. CDN для статических файлов

```php
// В конфигурации
define('CDN_URL', 'https://cdn.example.com');

// В коде
function getCDNUrl($path) {
    if (defined('CDN_URL') && CDN_URL) {
        return CDN_URL . $path;
    }
    return $path;
}

// Использование
$image_url = getCDNUrl('/img/logo.png');
```

## Оптимизация производительности PHP

### 1. OPcache настройки

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.enable_cli=0
```

### 2. Увеличение лимитов

```ini
; php.ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 32M
post_max_size = 32M
```

### 3. Отключите ненужные модули

```php
// В production отключите debug
define('DEBUG_MODE', false);
define('DEBUG_ENABLED', false);
define('LOG_ENABLED', false);

// Отключите display_errors
ini_set('display_errors', 'Off');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
```

## Оптимизация веб-сервера

### Apache оптимизация

#### .htaccess

```apache
# Включить сжатие
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Кеширование браузера
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
</IfModule>

# ETags
<IfModule mod_headers.c>
    Header unset ETag
    FileETag None
</IfModule>
```

### Nginx оптимизация

```nginx
# nginx.conf

# Gzip сжатие
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;

# Кеширование
location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml)$ {
    expires 365d;
    add_header Cache-Control "public, immutable";
}

# FastCGI кеш
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=SITEBILL:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

location ~ \.php$ {
    fastcgi_cache SITEBILL;
    fastcgi_cache_valid 200 60m;
    fastcgi_cache_bypass $skip_cache;
    fastcgi_no_cache $skip_cache;
}
```

## Оптимизация JavaScript и CSS

### 1. Минификация

```bash
# Установите minifier
npm install -g uglify-js clean-css-cli

# Минифицируйте JS
uglifyjs script.js -o script.min.js -c -m

# Минифицируйте CSS
cleancss -o style.min.css style.css
```

### 2. Объединение файлов

```php
class AssetManager {
    public function combineJS($files, $output) {
        $content = '';
        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }
        file_put_contents($output, $content);
    }
    
    public function combineCSS($files, $output) {
        $content = '';
        foreach ($files as $file) {
            $content .= file_get_contents($file) . "\n";
        }
        file_put_contents($output, $content);
    }
}
```

### 3. Асинхронная загрузка

```html
<!-- Асинхронная загрузка JS -->
<script src="script.js" async></script>

<!-- Отложенная загрузка JS -->
<script src="script.js" defer></script>

<!-- Preload критичных ресурсов -->
<link rel="preload" href="critical.css" as="style">
<link rel="preload" href="critical.js" as="script">
```

## Мониторинг и профилирование

### 1. Измерение времени выполнения

```php
class PerformanceMonitor {
    protected $start_time;
    protected $checkpoints = array();
    
    public function start() {
        $this->start_time = microtime(true);
    }
    
    public function checkpoint($name) {
        $this->checkpoints[$name] = microtime(true) - $this->start_time;
    }
    
    public function getReport() {
        return $this->checkpoints;
    }
}

// Использование
$monitor = new PerformanceMonitor();
$monitor->start();

// Какая-то операция
$monitor->checkpoint('database_query');

// Другая операция
$monitor->checkpoint('template_render');

// Получить отчет
$report = $monitor->getReport();
```

### 2. Профилирование запросов

```php
class QueryLogger {
    protected $queries = array();
    
    public function logQuery($query, $time) {
        $this->queries[] = array(
            'query' => $query,
            'time' => $time,
            'trace' => debug_backtrace()
        );
    }
    
    public function getSlowestQueries($limit = 10) {
        usort($this->queries, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });
        return array_slice($this->queries, 0, $limit);
    }
}
```

## Кеширование на уровне приложения

### 1. Кеш конфигурации

```php
class ConfigCache {
    protected $cache_file = '/cache/config.cache';
    
    public function getConfig() {
        if (file_exists($this->cache_file)) {
            return unserialize(file_get_contents($this->cache_file));
        }
        
        // Загружаем из БД
        $config = $this->loadFromDatabase();
        
        // Сохраняем в кеш
        file_put_contents($this->cache_file, serialize($config));
        
        return $config;
    }
    
    public function clearCache() {
        if (file_exists($this->cache_file)) {
            unlink($this->cache_file);
        }
    }
}
```

### 2. Кеширование результатов

```php
class ResultCache {
    public function remember($key, $ttl, $callback) {
        $cache_key = 'cache_' . md5($key);
        
        // Проверяем кеш в БД
        $cached = $this->getFromCache($cache_key);
        if ($cached !== false && $cached['expires'] > time()) {
            return unserialize($cached['data']);
        }
        
        // Выполняем callback
        $result = $callback();
        
        // Сохраняем в кеш
        $this->saveToCache($cache_key, serialize($result), time() + $ttl);
        
        return $result;
    }
}

// Использование
$items = $cache->remember('popular_items', 3600, function() {
    return Data::orderBy('views', 'desc')->limit(10)->get();
});
```

## Чеклист оптимизации

### База данных
- [ ] Добавлены необходимые индексы
- [ ] Оптимизированы медленные запросы
- [ ] Используются prepared statements
- [ ] Включено кеширование запросов
- [ ] Настроена пагинация

### PHP
- [ ] Включен OPcache
- [ ] Оптимизированы настройки php.ini
- [ ] Используется автозагрузка классов
- [ ] Отключен debug в production
- [ ] Применена ленивая загрузка

### Шаблоны
- [ ] Включено кеширование Smarty
- [ ] Отключена компиляция в production
- [ ] Оптимизированы циклы в шаблонах
- [ ] Минимизированы вызовы модификаторов

### Медиа
- [ ] Оптимизированы изображения
- [ ] Созданы миниатюры
- [ ] Используется lazy loading
- [ ] Настроен CDN

### Веб-сервер
- [ ] Включено gzip сжатие
- [ ] Настроено кеширование браузера
- [ ] Оптимизированы заголовки
- [ ] Включен HTTP/2

### Frontend
- [ ] Минифицированы JS и CSS
- [ ] Объединены файлы
- [ ] Используется асинхронная загрузка
- [ ] Настроен preload критичных ресурсов
