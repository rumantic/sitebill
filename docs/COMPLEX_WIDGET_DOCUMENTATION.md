# Документация: Вывод виджета жилых комплексов на главную страницу

## Содержание
1. [Введение](#введение)
2. [Предварительные требования](#предварительные-требования)
3. [Шаг 1: Включение генерации списка комплексов](#шаг-1-включение-генерации-списка-комплексов)
4. [Шаг 2: Создание файла виджета](#шаг-2-создание-файла-виджета)
5. [Шаг 3: Подключение виджета к странице](#шаг-3-подключение-виджета-к-странице)
6. [Шаг 4: Настройка отображения](#шаг-4-настройка-отображения)
7. [Примеры кода](#примеры-кода)
8. [Устранение неполадок](#устранение-неполадок)

---

## Введение

Данная инструкция поможет вывести список жилых комплексов (из приложения `apps/complex`) на главную страницу вашего сайта. Виджет будет отображать последние добавленные жилые комплексы с фотографиями, описанием и ссылками на их детальные страницы.

**Целевой файл страницы:** `d:\OpenServer\domains\qplan\template\frontend\franch\resources\views\pages\index.blade.php`

**Используемое приложение:** `apps/complex`

---

## Предварительные требования

Перед началом убедитесь, что:

1. ✅ Приложение `apps/complex` установлено и включено в админ-панели
2. ✅ В системе созданы жилые комплексы (есть данные для отображения)
3. ✅ Вы имеете доступ к файлам на сервере
4. ✅ Вы знакомы с основами Blade-шаблонов (Laravel)

---

## Шаг 1: Включение генерации списка комплексов

### 1.1. Войдите в административную панель

Откройте админ-панель вашего сайта: `http://ваш-домен.com/admin/`

### 1.2. Перейдите в настройки приложения Complex

Навигация: **Система → Настройки → Complex (Жилые комплексы)**

### 1.3. Включите параметр "Генерировать список последних комплексов"

Найдите параметр:
```
apps.complex.get_new
```

Установите значение:
- **`0`** - не генерировать список
- **`1`** - генерировать только на главной странице (рекомендуется)
- **`2`** - генерировать на всех страницах

**Рекомендуемое значение:** `1`

### 1.4. Настройте количество комплексов

Найдите параметр:
```
apps.complex.new_count
```

Установите количество комплексов для отображения (например: `6`, `8`, `12`)

**Рекомендуемое значение:** `6` или `8`

### 1.5. Сохраните настройки

Нажмите кнопку **"Сохранить"** внизу страницы.

---

## Шаг 2: Создание файла виджета

### 2.1. Создайте директорию для виджета

Если директория не существует, создайте её:
```
d:\OpenServer\domains\qplan\apps\complex\resources\views\
```

### 2.2. Создайте файл виджета

Создайте новый файл:
```
d:\OpenServer\domains\qplan\apps\complex\resources\views\widget-list.blade.php
```

### 2.3. Добавьте код виджета

Скопируйте и вставьте следующий код в файл `widget-list.blade.php`:

```blade
@php
    $new_complexes = store('new_complexes');
    $estate_folder = SITEBILL_MAIN_URL;
    $complex_alias = config('apps.complex.alias', 'complex');
@endphp

@if(!empty($new_complexes) && is_array($new_complexes) && count($new_complexes) > 0)
<section class="pt-10 pb-10 bg-gray-01">
    <div class="container">
        <div class="row mb-6">
            <div class="col-md-8">
                <h2 class="fs-32 lh-14 mb-0">{{ _ed('Новые жилые комплексы') }}</h2>
                <p class="text-gray">{{ _ed('Актуальные предложения от застройщиков') }}</p>
            </div>
            <div class="col-md-4 text-md-right">
                <a href="{{ $estate_folder }}/{{ $complex_alias }}" 
                   class="btn btn-lg btn-primary">
                    {{ _ed('Все комплексы') }}
                    <i class="far fa-long-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
        
        <div class="row">
            @foreach($new_complexes as $complex)
            @php
                $complex_id = $complex['complex_id']['value'] ?? 0;
                $complex_name = $complex['name']['value'] ?? '';
                $complex_url = $complex['url']['value'] ?? '';
                $complex_href = $complex['href'] ?? ($estate_folder . '/' . $complex_alias . '/' . $complex_url);
                
                // Получаем изображение
                $complex_image = '';
                if(isset($complex['imgfile']['image_array']) && !empty($complex['imgfile']['image_array'])) {
                    $first_image = reset($complex['imgfile']['image_array']);
                    $complex_image = $estate_folder . '/img/data/' . $first_image['normal'];
                } else {
                    $complex_image = $estate_folder . '/template/frontend/franch/assets/img/no-image.jpg';
                }
                
                // Получаем описание
                $complex_description = $complex['description']['value'] ?? '';
                if(strlen($complex_description) > 150) {
                    $complex_description = mb_substr($complex_description, 0, 150) . '...';
                }
                
                // Получаем город
                $complex_city = $complex['city_id']['value_string'] ?? '';
                
                // Получаем дату сдачи
                $complex_ready = '';
                if(isset($complex['built_year']['value']) && $complex['built_year']['value']) {
                    $complex_ready = $complex['built_year']['value'];
                    if(isset($complex['ready_quarter']['value']) && $complex['ready_quarter']['value']) {
                        $complex_ready .= ' (' . $complex['ready_quarter']['value'] . ' квартал)';
                    }
                }
            @endphp
            
            <div class="col-md-6 col-lg-4 mb-6">
                <div class="card border-0 shadow-hover-1">
                    <div class="card-img-top position-relative">
                        <a href="{{ $complex_href }}">
                            <img src="{{ $complex_image }}" 
                                 alt="{{ $complex_name }}"
                                 class="card-img-top"
                                 style="height: 250px; object-fit: cover;">
                        </a>
                        @if($complex_ready)
                        <span class="badge badge-primary position-absolute pos-fixed-top m-3">
                            <i class="far fa-calendar-alt"></i> {{ $complex_ready }}
                        </span>
                        @endif
                    </div>
                    
                    <div class="card-body pt-4 pb-4">
                        <h4 class="card-title fs-18 lh-17 mb-2">
                            <a href="{{ $complex_href }}" 
                               class="text-dark hover-primary">
                                {{ $complex_name }}
                            </a>
                        </h4>
                        
                        @if($complex_city)
                        <p class="card-text mb-1">
                            <i class="far fa-map-marker-alt text-primary mr-1"></i>
                            {{ $complex_city }}
                        </p>
                        @endif
                        
                        @if($complex_description)
                        <p class="card-text text-gray-light mb-3">
                            {{ $complex_description }}
                        </p>
                        @endif
                        
                        <a href="{{ $complex_href }}" 
                           class="btn btn-outline-primary btn-sm">
                            {{ _ed('Подробнее') }}
                            <i class="far fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
```

### 2.4. Сохраните файл

Сохраните файл `widget-list.blade.php`.

---

## Шаг 3: Подключение виджета к странице

### 3.1. Откройте файл главной страницы

Откройте файл:
```
d:\OpenServer\domains\qplan\template\frontend\franch\resources\views\pages\index.blade.php
```

### 3.2. Найдите место для вставки виджета

В файле `index.blade.php` уже есть подключение виджета комплексов (строки 42-44):

```blade
@if( config('apps.complex.get_new') )
    @include('apps.complex.widget-list')
@endif
```

Это означает, что виджет уже подключен! Он будет отображаться, если:
1. ✅ Параметр `apps.complex.get_new` включен в настройках
2. ✅ Файл `widget-list.blade.php` создан

### 3.3. Альтернативные места размещения виджета

Вы можете переместить блок подключения в другое место страницы:

**Перед блоком "Наши услуги":**
```blade
@if( config('apps.complex.get_new') )
    @include('apps.complex.widget-list')
@endif

@if( config('template.franch.pages.index.show_our_services_grid') )
    @include('layout.partials._topdestinations')
@endif
```

**После блока "Лучшие предложения":**
```blade
@if( config('template.franch.index.best1') )
    @include('layout.partials._bestproperties', [...])
@endif

@if( config('apps.complex.get_new') )
    @include('apps.complex.widget-list')
@endif
```

**В самом низу страницы:**
```blade
@if( config('template.franch.pages.index.partners') )
    @include('apps.partners.resources.views.widget')
@endif

@if( config('apps.complex.get_new') )
    @include('apps.complex.widget-list')
@endif
```

### 3.4. Сохраните изменения

Сохраните файл `index.blade.php`.

---

## Шаг 4: Настройка отображения

### 4.1. Очистите кэш

После внесения изменений очистите кэш сайта:

1. В админ-панели: **Система → Очистка кэша**
2. Или удалите содержимое папки: `d:\OpenServer\domains\qplan\cache\`

### 4.2. Проверьте результат

Откройте главную страницу сайта: `http://ваш-домен.com/`

Вы должны увидеть секцию с жилыми комплексами.

### 4.3. Настройка стилей (опционально)

Если виджет не соответствует дизайну сайта, вы можете изменить CSS-классы в файле `widget-list.blade.php`:

**Изменить цвет фона секции:**
```blade
<section class="pt-10 pb-10 bg-white">  <!-- было: bg-gray-01 -->
```

**Изменить размер карточек:**
```blade
<div class="col-md-6 col-lg-3 mb-6">  <!-- было: col-lg-4 (будет 4 колонки) -->
```

**Изменить высоту изображения:**
```blade
style="height: 300px; object-fit: cover;">  <!-- было: 250px -->
```

---

## Примеры кода

### Пример 1: Простой виджет (список без карточек)

```blade
@php
    $new_complexes = store('new_complexes');
    $estate_folder = SITEBILL_MAIN_URL;
    $complex_alias = config('apps.complex.alias', 'complex');
@endphp

@if(!empty($new_complexes))
<section class="py-5">
    <div class="container">
        <h2>{{ _ed('Новые жилые комплексы') }}</h2>
        <ul class="list-unstyled">
            @foreach($new_complexes as $complex)
            <li class="mb-2">
                <a href="{{ $complex['href'] }}">
                    {{ $complex['name']['value'] }}
                </a>
                @if(isset($complex['city_id']['value_string']))
                    - {{ $complex['city_id']['value_string'] }}
                @endif
            </li>
            @endforeach
        </ul>
        <a href="{{ $estate_folder }}/{{ $complex_alias }}" class="btn btn-primary">
            Все комплексы
        </a>
    </div>
</section>
@endif
```

### Пример 2: Горизонтальный слайдер

```blade
@php
    $new_complexes = store('new_complexes');
    $estate_folder = SITEBILL_MAIN_URL;
@endphp

@if(!empty($new_complexes))
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4">{{ _ed('Новые жилые комплексы') }}</h2>
        
        <div class="owl-carousel owl-theme" id="complexes-slider">
            @foreach($new_complexes as $complex)
            <div class="item">
                <div class="card">
                    @if(isset($complex['imgfile']['image_array']))
                    <img src="{{ $estate_folder }}/img/data/{{ reset($complex['imgfile']['image_array'])['normal'] }}" 
                         class="card-img-top" alt="{{ $complex['name']['value'] }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $complex['name']['value'] }}</h5>
                        <a href="{{ $complex['href'] }}" class="btn btn-sm btn-primary">Подробнее</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<script>
    $(document).ready(function(){
        $('#complexes-slider').owlCarousel({
            loop: true,
            margin: 20,
            nav: true,
            responsive: {
                0: { items: 1 },
                768: { items: 2 },
                992: { items: 3 }
            }
        });
    });
</script>
@endif
```

### Пример 3: Виджет с фильтрацией по городу

```blade
@php
    $new_complexes = store('new_complexes');
    $estate_folder = SITEBILL_MAIN_URL;
    
    // Группируем комплексы по городам
    $grouped = [];
    if(!empty($new_complexes)) {
        foreach($new_complexes as $complex) {
            $city = $complex['city_id']['value_string'] ?? 'Другие';
            if(!isset($grouped[$city])) {
                $grouped[$city] = [];
            }
            $grouped[$city][] = $complex;
        }
    }
@endphp

@if(!empty($grouped))
<section class="py-5">
    <div class="container">
        <h2 class="mb-4">{{ _ed('Новые жилые комплексы') }}</h2>
        
        <!-- Навигация по городам -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            @foreach($grouped as $city => $complexes)
            <li class="nav-item">
                <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                   data-toggle="tab" 
                   href="#city-{{ $loop->index }}">
                    {{ $city }} ({{ count($complexes) }})
                </a>
            </li>
            @endforeach
        </ul>
        
        <!-- Контент вкладок -->
        <div class="tab-content">
            @foreach($grouped as $city => $complexes)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                 id="city-{{ $loop->index }}">
                <div class="row">
                    @foreach($complexes as $complex)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>{{ $complex['name']['value'] }}</h5>
                                <a href="{{ $complex['href'] }}" class="btn btn-sm btn-primary">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
```

---

## Устранение неполадок

### Проблема 1: Виджет не отображается

**Возможные причины и решения:**

1. **Параметр `apps.complex.get_new` выключен**
   - Решение: Включите параметр в админ-панели (см. Шаг 1.3)

2. **В базе данных нет жилых комплексов**
   - Решение: Создайте минимум 1 жилой комплекс через админ-панель
   - Проверка: Админка → Complex → Список комплексов

3. **Файл `widget-list.blade.php` не создан**
   - Решение: Создайте файл по пути (см. Шаг 2.2)

4. **Кэш не очищен**
   - Решение: Очистите кэш (см. Шаг 4.1)

### Проблема 2: Отображаются не все комплексы

**Причина:** Ограничение по количеству в настройках

**Решение:**
1. Откройте админ-панель
2. Найдите параметр `apps.complex.new_count`
3. Увеличьте значение (например, с `6` на `12`)
4. Сохраните и очистите кэш

### Проблема 3: Изображения не загружаются

**Возможные причины:**

1. **Неправильный путь к изображениям**
   - Проверьте: `$estate_folder` должен быть `SITEBILL_MAIN_URL`
   - Путь к изображениям: `/img/data/{filename}`

2. **Изображения не загружены для комплексов**
   - Решение: Загрузите изображения через админ-панель
   - Формат: JPG, PNG (рекомендуемый размер: 800x600px)

### Проблема 4: Ошибка "Call to undefined function store()"

**Причина:** Данные не были предзагружены

**Решение:**
1. Убедитесь, что параметр `apps.complex.get_new` = `1` или `2`
2. Проверьте файл `apps/complex/admin/admin.php` (строки 936-985)
3. Перезагрузите страницу (данные загружаются при рендере)

### Проблема 5: Стили виджета отличаются от дизайна сайта

**Решение:**
1. Откройте файл `widget-list.blade.php`
2. Измените CSS-классы на соответствующие вашей теме:
   - `bg-gray-01` → `bg-white` или `bg-light`
   - `btn-primary` → `btn-accent` или другой класс
   - `fs-32` → `h2` или другой размер шрифта

---

## Дополнительные возможности

### Отображение дополнительных полей

Вы можете добавить вывод других полей жилого комплекса:

```blade
{{-- Застройщик --}}
@if(isset($complex['developer_id']['value_string']))
<p><strong>Застройщик:</strong> {{ $complex['developer_id']['value_string'] }}</p>
@endif

{{-- Класс жилья --}}
@if(isset($complex['class_id']['value_string']))
<p><strong>Класс:</strong> {{ $complex['class_id']['value_string'] }}</p>
@endif

{{-- Количество корпусов --}}
@if(isset($complex['buildings_count']['value']))
<p><strong>Корпусов:</strong> {{ $complex['buildings_count']['value'] }}</p>
@endif

{{-- Координаты (для карты) --}}
@if(isset($complex['geo_lat']['value']) && isset($complex['geo_lng']['value']))
<p>
    <i class="fas fa-map-marker-alt"></i>
    {{ $complex['geo_lat']['value'] }}, {{ $complex['geo_lng']['value'] }}
</p>
@endif
```

### Добавление карты с маркерами

```blade
<div id="complexes-map" style="height: 400px; margin-bottom: 30px;"></div>

<script>
var complexesData = @json($new_complexes);
// Инициализация карты (Yandex, Google или OpenStreetMap)
// Добавление маркеров для каждого комплекса
</script>
```

---

## Структура данных комплекса

Для справки, структура массива `$complex`:

```php
[
    'complex_id' => ['value' => 123],
    'name' => ['value' => 'Название ЖК'],
    'url' => ['value' => 'zhk-nazvanie'],
    'href' => '/complex/zhk-nazvanie',
    'city_id' => [
        'value' => 1,
        'value_string' => 'Душанбе'
    ],
    'description' => ['value' => 'Описание комплекса...'],
    'imgfile' => [
        'image_array' => [
            ['normal' => 'image1.jpg', 'preview' => 'preview1.jpg'],
            ['normal' => 'image2.jpg', 'preview' => 'preview2.jpg']
        ]
    ],
    'built_year' => ['value' => '2025'],
    'ready_quarter' => ['value' => '3'],
    'developer_id' => [
        'value' => 10,
        'value_string' => 'Название застройщика'
    ],
    'class_id' => [
        'value' => 2,
        'value_string' => 'Бизнес-класс'
    ],
    'geo_lat' => ['value' => '38.5598'],
    'geo_lng' => ['value' => '68.7738'],
    // ... другие поля
]
```

---

## Заключение

Следуя этой инструкции, вы успешно добавили виджет жилых комплексов на главную страницу вашего сайта. 

**Основные шаги:**
1. ✅ Включили генерацию списка в настройках
2. ✅ Создали файл виджета `widget-list.blade.php`
3. ✅ Подключили виджет к странице `index.blade.php`
4. ✅ Настроили отображение и очистили кэш

Если у вас возникли проблемы, обратитесь к разделу [Устранение неполадок](#устранение-неполадок).

---

**Автор документации:** Generated for Sitebill CMS  
**Версия:** 1.0  
**Дата:** Декабрь 2025  
**Применимо к:** Sitebill CMS с приложением Complex
