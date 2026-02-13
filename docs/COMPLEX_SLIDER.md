# Слайдер жилых комплексов на главной странице

## Быстрый старт

Для вывода слайдера с жилыми комплексами на главной странице выполните 3 шага:

### Шаг 1: Добавить метод в `apps/complex/site/site.php`

**Найдите** в конце файла закрывающую фигурную скобку класса (перед последним `}`).

**Вставьте** перед ней этот код:

```php
    /**
     * Получить жилые комплексы для слайдера на главной странице
     * @param int $limit Количество комплексов для вывода (по умолчанию 8)
     * @param array $filters Дополнительные фильтры (например, array('is_special' => 1))
     * @return array Массив комплексов для отображения в слайдере
     */
    public function getComplexForSlider($limit = 8, $filters = array())
    {
        $complexes = array();
        
        $where = array();
        $where_val = array();
        
        // Базовые условия - только активные комплексы
        if(isset($this->data_model[$this->table_name]['activity_status'])){
            $where[] = '`activity_status`=?';
            $where_val[] = 1;
        }
        
        // Дополнительные фильтры
        if(!empty($filters)){
            foreach($filters as $field => $value){
                if(isset($this->data_model[$this->table_name][$field])){
                    if(is_array($value)){
                        $where[] = '`'.$field.'` IN ('.implode(',', array_fill(0, count($value), '?')).')';
                        $where_val = array_merge($where_val, $value);
                    }else{
                        $where[] = '`'.$field.'`=?';
                        $where_val[] = $value;
                    }
                }
            }
        }
        
        $DBC = DBC::getInstance();
        
        // Получаем ID комплексов
        $order = '`created_at` DESC';
        if ($this->use_billing) {
            $order = 'premium_status_end DESC, ' . $order;
        }
        
        $query = 'SELECT `'.$this->primary_key.'` FROM '.DB_PREFIX.'_'.$this->table_name.
                 (!empty($where) ? ' WHERE '.implode(' AND ', $where) : '').
                 ' ORDER BY '.$order.' LIMIT '.(int)$limit;
        
        $ids = array();
        $stmt = $DBC->query($query, $where_val);
        if($stmt){
            while($ar = $DBC->fetch($stmt)){
                $ids[$ar[$this->primary_key]] = $ar[$this->primary_key];
            }
        }
        
        // Получаем полные данные по комплексам
        if(!empty($ids)){
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/complex/admin/complex_model.php');
            $Object = new Complex_Model();
            $complexes_data = $Object->init_model_data_from_db_multi(
                $this->table_name, 
                $this->primary_key, 
                $ids, 
                $this->data_model[$this->table_name], 
                true
            );
            
            foreach($complexes_data as $id => $complex){
                $complex = $Object->init_language_values($complex);
                $complex['_href'] = $this->getArticleRoute(
                    $complex[$this->primary_key]['value'], 
                    $complex['alias']['value']
                );
                $complexes[$id] = $complex;
            }
        }
        
        return $complexes;
    }
```

### Шаг 2: Обновить метод в `apps/bridge/Http/Controllers/BlackBoxController.php`

**Найдите** метод `_getTopComplexes()` (примерно на строке 307).

**Замените** весь метод на этот код:

```php
    /** TODO отправить эту выборку в рамки приложения ЖК и вызывать оттуда */
    function _getTopComplexes(){

        $ret = array();
        
        // Проверяем, включен ли модуль комплексов
        if (!$this->sitebill->getConfigValue('apps.complex.enable')) {
            return $ret;
        }
        
        // Используем новый метод из модуля complex
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/complex/admin/admin.php');
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/complex/site/site.php');
        
        $complex_site = new \complex_site();
        
        // Получаем количество комплексов из настроек или используем 8 по умолчанию
        $limit = intval($this->sitebill->getConfigValue('apps.complex.slider_count'));
        if ($limit < 1) {
            $limit = 8;
        }
        
        // Получаем комплексы для слайдера
        // Можно добавить фильтры, например array('is_special' => 1) для особых предложений
        $filters = array();
        if ($this->sitebill->getConfigValue('apps.complex.slider_only_special')) {
            $filters['is_special'] = 1;
        }
        
        $ret = $complex_site->getComplexForSlider($limit, $filters);

        return $ret;

    }
```

### Шаг 3: Подключить слайдер в шаблоне

**Откройте** файл `template/frontend/franch/resources/views/pages/index.blade.php`.

**Найдите** блок с `@if( config('apps.complex.get_new') )` (примерно на строке 49-51).

**Вставьте** перед ним этот код:

```blade
        @if( config('apps.complex.show_slider') && isset($topcomplexes) && !empty($topcomplexes) )
            @include('apps.complex.resources.views.slider', [
                'slider_title' => _ed('Жилые комплексы'),
                'slider_description' => _ed('Актуальные предложения'),
                'slider_alllink_href' => $sitebill->createUrlTpl($sitebill->getConfigValue('apps.complex.alias')),
                'slider_alllink_text' => _ed('Все комплексы'),
                'items' => $topcomplexes,
                'slidesToShow' => 4
            ])
        @endif
```

---

## Параметры слайдера---

## Параметры слайдера

При подключении слайдера можно настроить:

| Параметр | Описание | Значение по умолчанию |
|----------|----------|----------------------|
| `slider_title` | Заголовок секции | 'Жилые комплексы' |
| `slider_description` | Описание секции | 'Актуальные предложения' |
| `slider_alllink_href` | Ссылка "Все комплексы" | URL модуля complex |
| `slider_alllink_text` | Текст ссылки | 'Все комплексы' |
| `items` | Массив комплексов | - |
| `slidesToShow` | Слайдов на экране (desktop) | 4 |

**Пример настройки:**

```blade
@include('apps.complex.resources.views.slider', [
    'slider_title' => _ed('Новостройки Москвы'),
    'slider_description' => _ed('Лучшие предложения от застройщиков'),
    'slider_alllink_href' => '/complex/find?category=2',
    'slider_alllink_text' => _ed('Все новостройки'),
    'items' => $topcomplexes,
    'slidesToShow' => 3
])
```

---

## Готовые варианты использования

### Вариант 1: Только специальные предложения

**В файле** `apps/bridge/Http/Controllers/BlackBoxController.php` в методе `_getTopComplexes()`:

```php
// Замените строку:
$filters = array();

// На:
$filters = array('is_special' => 1);
```

### Вариант 2: Изменить количество комплексов

**В методе** `_getTopComplexes()` замените:

```php
// Было:
if ($limit < 1) {
    $limit = 8;
}

// Стало (например, 12 комплексов):
if ($limit < 1) {
    $limit = 12;
}
```

### Вариант 3: Фильтр по городу

**В методе** `_getTopComplexes()` добавьте после `$filters = array();`:

```php
$filters['city_id'] = 1; // ID нужного города
```

### Вариант 4: Случайный порядок

**В файле** `apps/complex/site/site.php` в методе `getComplexForSlider()` замените:

```php
// Было:
$order = '`created_at` DESC';

// Стало:
$order = 'RAND()';
```

### Вариант 5: Сортировка по цене

**В методе** `getComplexForSlider()` замените:

```php
// Было:
$order = '`created_at` DESC';

// Стало (от дешевых к дорогим):
$order = '`price_min` ASC';

// Или (от дорогих к дешевым):
$order = '`price_min` DESC';
```

---

## Использование на других страницах

### Пример 1: Слайдер в любом blade-шаблоне

**Скопируйте** этот код в нужное место вашего шаблона:

```blade
@php
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/complex/admin/admin.php');
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/complex/site/site.php');
    $complex_site = new \complex_site();
    $my_complexes = $complex_site->getComplexForSlider(6);
@endphp

@if(!empty($my_complexes))
    @include('apps.complex.resources.views.slider', [
        'slider_title' => _ed('Рекомендуем посмотреть'),
        'items' => $my_complexes,
        'slidesToShow' => 3
    ])
@endif
```

### Пример 2: Слайдер с фильтром по категории

```blade
@php
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/complex/admin/admin.php');
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/complex/site/site.php');
    $complex_site = new \complex_site();
    $new_buildings = $complex_site->getComplexForSlider(8, array('category' => 2));
@endphp

@if(!empty($new_buildings))
    @include('apps.complex.resources.views.slider', [
        'slider_title' => _ed('Новостройки'),
        'slider_description' => _ed('Квартиры от застройщика'),
        'items' => $new_buildings
    ])
@endif
```

### Пример 3: Компактный слайдер

```blade
@include('apps.complex.resources.views.slider', [
    'slider_title' => _ed('Жилые комплексы'),
    'items' => $topcomplexes,
    'tiny' => true,
    'slidesToShow' => 3
])
```

---

## Адаптивность

Слайдер автоматически подстраивается под размер экрана:

- **1600px и больше**: количество слайдов по параметру `slidesToShow`
- **992-1599px**: 3 слайда, стрелки скрыты
- **768-991px**: 2 слайда, стрелки видны
- **576-767px**: 2 слайда, стрелки видны
- **До 576px**: 1 слайд, стрелки видны

---

## Что отображается в карточке

- Фото (одно или карусель)
- Название с ссылкой
- Адрес
- Метка "Специальный" (если `is_special = 1`)
- Категория
- Год постройки
- Количество корпусов
- Количество квартир
- Минимальная цена

---

## Устранение проблем

### Слайдер не появляется

**Проверьте** в админ-панели или коде, что установлено:
- `apps.complex.enable = 1`
- `apps.complex.show_slider = 1`

**Проверьте** наличие комплексов в базе данных.

### Изображения не загружаются

**Проверьте** права доступа к папке с медиафайлами.

**В административной панели** убедитесь, что у комплексов загружены изображения.

### Слайдер "ломается"

**Убедитесь**, что подключена библиотека Slick Slider в вашем шаблоне.

**Проверьте** консоль браузера (F12) на наличие JS-ошибок.

---

## Создание вьюшки slider.blade.php

Если файл `apps/complex/resources/views/slider.blade.php` не существует, создайте его со следующим содержимым:

```blade
@php
    $template_root = store('template_root');
    $estate_folder = SITEBILL_MAIN_URL;
    $useslidesonlist = true;
    
    if ( isset($slidesToShow) ) {
        $slidesToShowDeskTop = $slidesToShow;
    } else {
        $slidesToShowDeskTop = 4;
    }
    
    if(!isset($slider_title)){
        $slider_title = _e('Жилые комплексы');
    }
    if(!isset($slider_description)){
        $slider_description = _e('Актуальные предложения');
    }
    if(!isset($slider_alllink_href)){
        $slider_alllink_href = $sitebill->createUrlTpl($sitebill->getConfigValue('apps.complex.alias'));
    }
    if(!isset($slider_alllink_text)){
        $slider_alllink_text = _e('Все комплексы');
    }
@endphp

@if($items && count($items) > 0)
<section class="pt-lg-12 pb-lg-10 py-11">
    <div class="container @if(!isset($tiny)) container-xxl @endif">
        <div class="row">
            <div class="col-md-6">
                <h2 class="text-heading">{!! $slider_title !!}</h2>
                <span class="heading-divider"></span>
                @if($slider_description && $slider_description != '')
                <p class="mb-6">{!! $slider_description !!}</p>
                @endif
            </div>
            @if($slider_alllink_href && $slider_alllink_href != '')
            <div class="col-md-6 text-md-right">
                <a href="{{$slider_alllink_href}}"
                   class="btn fs-14 text-secondary btn-accent py-3 lh-15 px-7 mb-6 mb-lg-0">{!! $slider_alllink_text !!}
                    <i class="far fa-long-arrow-right ml-1"></i>
                </a>
            </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="slick-slider slick-dots-mt-0 custom-arrow-spacing-30 complexslider" data-slick-options='{"slidesToShow": {{$slidesToShowDeskTop}}, "autoplay":true,"dots":true,"responsive":[{"breakpoint": 1600,"settings": {"slidesToShow":3,"arrows":false}},{"breakpoint": 992,"settings": {"slidesToShow":2,"arrows":false}},{"breakpoint": 768,"settings": {"slidesToShow": 2,"arrows":true,"dots":false,"autoplay":true}},{"breakpoint": 576,"settings": {"slidesToShow": 1,"arrows":true,"dots":false,"autoplay":true}}]}'>
                    @foreach($items as $item)
                        @php
                            $imagecount = 0;
                            if(!empty($item['image']['value']) && is_array($item['image']['value'])){
                                $imagecount = count($item['image']['value']);
                            }
                            $current_image = null;
                            if($imagecount > 0) {
                                $current_image = $item['image']['value'][0];
                            }
                        @endphp

                        <div class="box pb-7 pt-2">
                            <div class="card shadow-hover-2" data-animate="zoomIn">
                                <div class="hover-change-image bg-hover-overlay rounded-lg card-img-top">
                                    @if($useslidesonlist && $imagecount > 1)
                                        <div class="owl-carousel owl-theme listing-item-photocarousel">
                                            @foreach($item['image']['value'] as $image)
                                                <img class="owl-lazy lazyload" alt="{{$item['name']['value']}}" data-src="{{$sitebill->createMediaIncPath($image, 'preview')}}" xsrc="{{$sitebill->createMediaIncPath($image, 'preview')}}">
                                            @endforeach
                                        </div>
                                    @elseif($imagecount > 0)
                                        <img src="{{$sitebill->createMediaIncPath($current_image, 'preview')}}" alt="{{$item['name']['value']}}">
                                    @else
                                        <img src="{{$template_root}}images/nophoto.jpg" alt="{{$item['name']['value']}}" class="card-img">
                                    @endif
                                    
                                    <div class="card-img-overlay p-2 d-flex flex-column js-opencard" data-href="{{$item['_href']}}">
                                        @if($useslidesonlist && $imagecount > 1)
                                            <div class="listing-item-photocarousel-nav">
                                                <span class="listing-item-photocarousel-nav-left"></span>
                                                <span class="listing-item-photocarousel-nav-right"></span>
                                            </div>
                                        @endif
                                        <div>
                                            @if(isset($item['is_special']['value']) && $item['is_special']['value'] == 1)
                                                <span class="badge badge-orange">{{_e('Специальный')}}</span>
                                            @endif
                                            @if(isset($item['category']['value']) && $item['category']['value'] > 0)
                                                <span class="badge badge-primary">{{$item['category']['value_string']}}</span>
                                            @endif
                                        </div>
                                        <ul class="list-inline mb-0 mt-auto hover-image">
                                            @if($imagecount > 1)
                                                <li class="list-inline-item mr-2" data-toggle="tooltip" title="{{$imagecount}} {{_e('фото')}}">
                                                    <span class="text-white hover-primary">
                                                        <i class="far fa-images"></i><span class="pl-1">{{$imagecount}}</span>
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body pt-3">
                                    <h2 class="card-title fs-16 lh-2 mb-0">
                                        <a href="{{$item['_href']}}" class="text-dark hover-primary">
                                            {{$item['name']['value']}}
                                        </a>
                                    </h2>
                                    
                                    @if(isset($item['address']['value']) && $item['address']['value'] != '')
                                        <p class="card-text font-weight-500 text-gray-light mb-2">
                                            <i class="fal fa-map-marker-alt mr-1"></i>{{$item['address']['value']}}
                                        </p>
                                    @endif
                                    
                                    <ul class="list-inline d-flex mb-0 flex-wrap mr-n5">
                                        @if(isset($item['built_year']['value']) && $item['built_year']['value'] != '')
                                            <li class="list-inline-item text-gray font-weight-500 fs-13 d-flex align-items-center mr-5" data-toggle="tooltip" title="{{_e('Год постройки')}}">
                                                <i class="fal fa-calendar mr-1"></i>
                                                {{$item['built_year']['value']}}
                                            </li>
                                        @endif
                                        
                                        @if(isset($item['buildingscount']['value']) && $item['buildingscount']['value'] > 0)
                                            <li class="list-inline-item text-gray font-weight-500 fs-13 d-flex align-items-center mr-5" data-toggle="tooltip" title="{{_e('Количество корпусов')}}">
                                                <i class="fal fa-building mr-1"></i>
                                                {{$item['buildingscount']['value']}}
                                            </li>
                                        @endif
                                        
                                        @if(isset($item['flatscount']['value']) && $item['flatscount']['value'] > 0)
                                            <li class="list-inline-item text-gray font-weight-500 fs-13 d-flex align-items-center mr-5" data-toggle="tooltip" title="{{_e('Количество квартир')}}">
                                                <i class="fal fa-home mr-1"></i>
                                                {{$item['flatscount']['value']}}
                                            </li>
                                        @endif
                                    </ul>
                                    
                                    @if(isset($item['price_min']['value']) && $item['price_min']['value'] > 0)
                                        <p class="card-text mt-2 mb-1">
                                            <span class="text-heading fs-17 font-weight-bold">
                                                {{_e('От')}} {{number_format($item['price_min']['value'], 0, '.', ' ')}} {{$item['price_min']['currency']}}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif
```

---

## Техническая информация

**Требования:**
- PHP 7.0+
- jQuery
- Slick Slider
- Owl Carousel

**Зависимости:**
- Blade templates
- Complex_Model
- Система маршрутизации
- Мультиязычность
