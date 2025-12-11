# Документация шаблона Realia для разработчиков и вебмастеров

## Содержание

1. [Введение](#введение)
2. [Структура шаблона](#структура-шаблона)
3. [Система макетов (Layouts)](#система-макетов-layouts)
4. [Основные компоненты](#основные-компоненты)
5. [Стилизация и CSS](#стилизация-и-css)
6. [JavaScript и интерактивность](#javascript-и-интерактивность)
7. [Переменные Smarty и данные](#переменные-smarty-и-данные)
8. [Настройка и доработка](#настройка-и-доработка)
9. [Создание пользовательских компонентов](#создание-пользовательских-компонентов)
10. [Интеграция с приложениями](#интеграция-с-приложениями)
11. [Устранение неполадок](#устранение-неполадок)
12. [Лучшие практики](#лучшие-практики)

---

## Введение

**Realia** — это современный адаптивный шаблон для CMS Sitebill, специально разработанный для сайтов недвижимости. Шаблон построен на базе Bootstrap 2.x и использует систему шаблонов Smarty.

### Ключевые особенности

- ✅ Адаптивный дизайн на основе Bootstrap 2.x
- ✅ Поддержка различных типов карт (Yandex, Google, OpenStreetMap)
- ✅ Гибкая система макетов для разных типов страниц
- ✅ Встроенная интеграция с приложением "Клиент"
- ✅ Поддержка слайдеров и галерей изображений
- ✅ Система поиска недвижимости с фильтрами
- ✅ Мультиязычность

### Требования

1. **CKEditor** — Шаблон использует версию jQuery, несовместимую с CLEditor
   - В **Настройки → Общее → WYSIWYG-редактор** должен быть выбран **CKEditor**
   - Папка `ckeditor` должна находиться в корне сайта
   - Скачать: https://ckeditor.com/download

2. **Приложение "Клиент"** — версия 1.0.2 или выше
   - Должно быть установлено и активировано
   - Необходима модель `contactus` для приема сообщений

3. **PHP** >= 7.1
4. **MySQL** >= 5.6

> ⚠️ **Важно:** Шаблон не поддерживает сильно вложенные структуры меню. Избегайте глубокой вложенности пунктов навигации.

---

## Структура шаблона

Шаблон расположен в директории: `/template/frontend/realia/`

```
realia/
├── css/                    # Стили
│   ├── bootstrap.css       # Bootstrap framework
│   ├── style.css           # Основные стили шаблона
│   ├── realia-blue.css     # Цветовая схема (синяя)
│   └── realia-dark-blue.css # Альтернативная цветовая схема
│
├── js/                     # JavaScript файлы
│   ├── jquery.js           # jQuery библиотека
│   ├── bootstrap.min.js    # Bootstrap JS
│   ├── realia.js           # Основной функционал шаблона
│   ├── interface.js        # Интерфейсные взаимодействия
│   └── search_form.js      # Логика формы поиска
│
├── main/                   # PHP классы и логика
│   ├── main.php            # Основной класс шаблона
│   ├── realia_menu_decorator.php # Декоратор меню
│   └── ajax/               # AJAX обработчики
│
├── images/                 # Изображения шаблона
├── img/                    # Дополнительные изображения
│
├── libraries/              # Внешние библиотеки
│   ├── chosen/             # Стилизованные select
│   ├── bootstrap-fileupload/ # Загрузка файлов
│   ├── jquery-ui/          # jQuery UI компоненты
│   └── iosslider/          # Слайдер изображений
│
├── plugins/                # Плагины
│   ├── fotorama/           # Галерея фотографий
│   └── html5gallery/       # HTML5 галерея
│
├── apps/                   # Шаблоны для приложений
│   └── complex/            # Жилые комплексы
│
├── language/               # Языковые файлы
│
├── *.tpl                   # Файлы шаблонов Smarty
└── readme.txt              # Краткая информация
```

---

## Система макетов (Layouts)

Шаблон использует разные макеты для различных типов страниц. Макеты определяются в файле `main/main.php`:

### Доступные макеты

#### 1. `layout_home.tpl` — Главная страница

**Назначение:** Домашняя страница с слайдером и избранными объектами

**Особенности:**
- Полноэкранный слайдер изображений (IOSSlider)
- Форма быстрого поиска
- Блок избранных объектов недвижимости
- Карусель партнеров
- Новости в колонках

**Пример использования:**
```php
// В main.php
'home' => 'layout_home.tpl'
```

#### 2. `layout_basic.tpl` — Базовый макет

**Назначение:** Стандартные страницы контента

**Структура:**
```smarty
<div class="container">
    <div class="span9">
        {* Основной контент *}
        <h1>{$title}</h1>
        {$main}
    </div>
    <div class="sidebar span3">
        {* Боковая панель *}
        {include file="agents_list.tpl"}
        {include file='right_special.tpl'}
    </div>
</div>
```

**Используется для:**
- Страницы "О компании"
- Контактные страницы
- Статические страницы

#### 3. `layout_full.tpl` — Полноширинный макет

**Назначение:** Страницы без боковой панели

**Используется для:**
- Списки объектов недвижимости (`realtygrid`)
- Детальная страница объекта (`realtyview`)

#### 4. `layout_account.tpl` — Личный кабинет

**Назначение:** Страницы личного кабинета пользователя

**Особенности:**
- Специальное меню пользователя
- Упрощенная навигация
- Хлебные крошки

#### 5. `layout_find.tpl` — Страница поиска

**Назначение:** Расширенный поиск недвижимости

**Особенности:**
- Форма с фильтрами
- Результаты поиска
- Сортировка и пагинация

#### 6. `layout_map.tpl` — Карта объектов

**Назначение:** Отображение объектов на карте

**Особенности:**
- Полноэкранная карта
- Кластеризация маркеров
- Всплывающие окна с информацией

### Выбор макета

Макет выбирается автоматически в зависимости от типа страницы:

```php
// В main/main.php
$layouts = array(
    '_default' => 'layout_basic.tpl',
    'home' => 'layout_home.tpl',
    'realtygrid' => 'layout_full.tpl',
    'realtyview' => 'layout_full.tpl',
    'account' => 'layout_account.tpl',
    'find' => 'layout_find.tpl',
    'realtymap' => 'layout_map.tpl'
);
```

---

## Основные компоненты

### 1. Header (header.tpl)

Содержит метатеги, подключение CSS и JavaScript.

**Основные элементы:**
```smarty
<head>
    <meta charset="UTF-8">
    <title>{if $meta_title != ''}{$meta_title}{else}{$title}{/if}</title>
    
    {* CSS *}
    <link href="{$theme_folder}/css/bootstrap.css" rel="stylesheet">
    <link href="{$theme_folder}/css/style.css" rel="stylesheet">
    
    {* JavaScript *}
    <script src="{$theme_folder}/js/jquery.js"></script>
    <script src="{$theme_folder}/js/bootstrap.min.js"></script>
    <script src="{$theme_folder}/js/realia.js"></script>
</head>
```

**Переменные:**
- `{$theme_folder}` — путь к папке шаблона
- `{$estate_folder}` — путь к корню сайта
- `{$meta_title}` — заголовок страницы
- `{$meta_description}` — описание для SEO
- `{$meta_keywords}` — ключевые слова

### 2. Main Template (main.tpl)

Основной файл, объединяющий все компоненты.

**Структура:**
```smarty
<!DOCTYPE HTML>
<html lang="{$CurrentLang}">
    {include file="header.tpl"}
    <body>
        <div id="wrapper">
            {* Верхнее меню и навигация *}
            {include file='header_contact_add.tpl'}
            
            {* Основной контент (выбранный layout) *}
            {include file=$layout_file}
            
            {* Подвал *}
            {include file="footer.tpl"}
        </div>
    </body>
</html>
```

### 3. Footer (footer.tpl)

Подвал сайта с виджетами.

**Разделы:**
- Новые объекты недвижимости
- Избранные объекты
- Блог/Новости
- Контактная информация
- Социальные сети

**Редактируемые блоки:**
```smarty
<editable id="new_obj_title_edit" data-file="footer.tpl">
    {$LT_NEW_OBJECTS}
</editable>
```

### 4. Навигационное меню

#### Header Contact (header_contact_add.tpl)

Верхняя панель с контактами и кнопкой "Добавить объявление".

#### Top Menu

Главное меню генерируется динамически из структуры сайта.

**Декоратор меню:** `main/realia_menu_decorator.php`

### 5. Формы поиска

#### Стандартная форма (standart_search_form.tpl)
```smarty
<form action="{formaturl path='find'}" method="get">
    <input type="text" name="search_string" placeholder="Поиск...">
    <button type="submit">Искать</button>
</form>
```

#### Расширенная форма (advance_search_form.tpl)

Включает фильтры:
- Тип недвижимости
- Цена (от/до)
- Площадь
- Количество комнат
- Город/Район

#### Новая форма поиска (new_search_form.tpl)

Современный интерфейс с autocomplete и динамической загрузкой.

### 6. Сетки объектов (Grid Templates)

#### realty_grid.tpl
Стандартная сетка объектов с превью.

#### realty_grid_thumbs.tpl
Отображение объектов в виде миниатюр.

#### realty_grid_list.tpl
Список объектов в табличном виде.

#### realty_grid_ajax.tpl
AJAX-загрузка объектов без перезагрузки страницы.

### 7. Детальная страница объекта (realty_view.tpl)

**Секции:**
- Галерея фотографий (Fotorama)
- Основная информация
- Характеристики
- Описание
- Карта расположения
- Похожие объекты
- Форма обратной связи

---

## Стилизация и CSS

### Основные файлы стилей

#### 1. bootstrap.css
Фреймворк Bootstrap 2.x

#### 2. style.css
Основные стили шаблона

**Основные классы:**
```css
/* Контейнеры */
.container { ... }
.wrapper { ... }

/* Навигация */
.navigation { ... }
.breadcrumb { ... }

/* Карточки объектов */
.property { ... }
.property-item { ... }

/* Формы */
.search-form { ... }
.contact-form { ... }
```

#### 3. realia-blue.css / realia-dark-blue.css
Цветовые схемы

**Переключение цветовой схемы:**
```smarty
{* В header.tpl *}
<link href="{$theme_folder}/css/realia-blue.css" rel="stylesheet">
{* Или *}
<link href="{$theme_folder}/css/realia-dark-blue.css" rel="stylesheet">
```

### Настройка цветов

**Основные переменные цветов** (в realia-blue.css):

```css
/* Основной цвет */
.btn-primary,
.navbar,
#footer-top {
    background-color: #3aa0d1; /* Синий */
}

/* Ссылки */
a {
    color: #3aa0d1;
}

/* Ховер состояния */
a:hover {
    color: #2a90c1;
}
```

**Создание своей цветовой схемы:**

1. Скопируйте `realia-blue.css`
2. Переименуйте в `realia-custom.css`
3. Измените цвета
4. Подключите в `header.tpl`

### Адаптивность

Шаблон использует Bootstrap responsive grid:

```css
/* Мобильные устройства */
@media (max-width: 767px) {
    .container { width: 100%; }
    .span9, .span3 { width: 100%; }
}

/* Планшеты */
@media (min-width: 768px) and (max-width: 979px) {
    .container { width: 724px; }
}

/* Десктоп */
@media (min-width: 980px) {
    .container { width: 940px; }
}
```

### Пользовательские стили

**Рекомендуемый подход:**

1. Создайте файл `custom.css` в папке `css/`
2. Добавьте свои стили
3. Подключите в `header.tpl`:

```smarty
<link href="{$theme_folder}/css/custom.css" rel="stylesheet">
```

**Пример:**
```css
/* custom.css */

/* Изменение цвета кнопок */
.btn-primary {
    background-color: #e74c3c;
}

/* Увеличение размера заголовков */
h1.page-header {
    font-size: 36px;
}

/* Скругление углов карточек */
.property {
    border-radius: 10px;
}
```

---

## JavaScript и интерактивность

### Основные библиотеки

#### 1. jQuery (jquery.js)
Версия, совместимая с Bootstrap 2.x

#### 2. Bootstrap JS (bootstrap.min.js)
Компоненты Bootstrap:
- Модальные окна
- Всплывающие подсказки
- Выпадающие меню
- Табы

#### 3. realia.js
Основной функционал шаблона

**Инициализация:**
```javascript
$(document).ready(function() {
    // Chosen (стилизованные select)
    $('select').chosen();
    
    // Слайдеры
    $('.slider').iosSlider();
    
    // Pretty Photo (галерея)
    $("a[rel^='prettyPhoto']").prettyPhoto();
});
```

#### 4. interface.js
Обработка пользовательских взаимодействий

**Основные функции:**
```javascript
// Добавление в избранное
function addToFavorites(id) { ... }

// Сравнение объектов
function addToCompare(id) { ... }

// Обновление счетчика
function updateCounter(elementId, count) { ... }
```

#### 5. search_form.js
Логика формы поиска

**Функционал:**
- Автокомплит для адресов
- Динамическая загрузка районов при выборе города
- Валидация полей
- AJAX-отправка формы

### Интеграция карт

#### Yandex Maps
```javascript
ymaps.ready(function() {
    var myMap = new ymaps.Map('map', {
        center: [55.76, 37.64],
        zoom: 10
    });
});
```

#### Google Maps
```javascript
var map = new google.maps.Map(document.getElementById('map'), {
    center: {lat: 55.76, lng: 37.64},
    zoom: 10
});
```

#### OpenStreetMap (Leaflet)
```javascript
var map = L.map('map').setView([55.76, 37.64], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
```

### Слайдеры и галереи

#### IOSSlider (layout_home.tpl)
```javascript
$('.iosSlider').iosSlider({
    desktopClickDrag: true,
    snapToChildren: true,
    infiniteSlider: true,
    autoSlide: true,
    scrollbar: true
});
```

#### Fotorama (realty_view.tpl)
```javascript
$('.photoslider').fotorama({
    nav: "thumbs",
    allowfullscreen: true,
    width: "100%",
    ratio: "800/500"
});
```

### AJAX запросы

**Загрузка объектов:**
```javascript
$.ajax({
    url: '/ajax/load_properties.php',
    type: 'GET',
    data: {
        page: pageNumber,
        filter: filterData
    },
    success: function(response) {
        $('#properties-list').html(response);
    }
});
```

### Пользовательские скрипты

**Добавление своего JavaScript:**

1. Создайте файл `custom.js` в папке `js/`
2. Добавьте код
3. Подключите в `header.tpl`:

```smarty
<script src="{$theme_folder}/js/custom.js"></script>
```

**Пример:**
```javascript
// custom.js

$(document).ready(function() {
    // Плавная прокрутка к якорям
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.hash);
        $('html, body').animate({
            scrollTop: target.offset().top
        }, 800);
    });
    
    // Анимация при скролле
    $(window).scroll(function() {
        $('.fade-in').each(function() {
            var position = $(this).offset().top;
            var scroll = $(window).scrollTop();
            if (scroll + $(window).height() > position) {
                $(this).addClass('visible');
            }
        });
    });
});
```

---

## Переменные Smarty и данные

### Глобальные переменные

```smarty
{* Пути *}
{$estate_folder}    {* Корневая директория сайта *}
{$theme_folder}     {* Путь к текущему шаблону *}
{$admin_folder}     {* Путь к админ-панели *}

{* Пользователь *}
{$smarty.session.user_id}      {* ID текущего пользователя *}
{$smarty.session.user_name}    {* Имя пользователя *}
{$smarty.session.favorites}    {* Избранные объекты *}

{* Локализация *}
{$CurrentLang}      {* Текущий язык (ru, en и т.д.) *}
{$L_HOME}           {* Текст "Главная" *}
{$L_LOGIN_BUTTON}   {* Текст "Войти" *}
{$L_LOGOUT_BUTTON}  {* Текст "Выйти" *}

{* SEO *}
{$title}            {* Заголовок страницы *}
{$meta_title}       {* SEO заголовок *}
{$meta_description} {* SEO описание *}
{$meta_keywords}    {* Ключевые слова *}

{* Контент *}
{$main}             {* Основной контент страницы *}
{$breadcrumbs}      {* Хлебные крошки *}
```

### Переменные объектов недвижимости

```smarty
{* В циклах foreach для объектов *}
{foreach from=$grid_items item=item}
    {$item.id}              {* ID объекта *}
    {$item.title}           {* Заголовок *}
    {$item.price}           {* Цена *}
    {$item.currency_name}   {* Название валюты *}
    {$item.city}            {* Город *}
    {$item.street}          {* Улица *}
    {$item.number}          {* Номер дома *}
    {$item.square}          {* Площадь *}
    {$item.rooms}           {* Количество комнат *}
    {$item.href}            {* Ссылка на объект *}
    {$item.img}             {* Массив изображений *}
    {$item.preview_text}    {* Краткое описание *}
    {$item.detail_text}     {* Полное описание *}
{/foreach}
```

### Модификаторы Smarty

```smarty
{* Форматирование числа *}
{$price|number_format:0:",":" "}
{* Результат: 1 500 000 *}

{* Обрезка текста *}
{$description|truncate:150:"..."}
{* Результат: Первые 150 символов... *}

{* Дата *}
{$date|date_format:"%d.%m.%Y"}
{* Результат: 11.12.2025 *}

{* Верхний регистр *}
{$text|upper}

{* Нижний регистр *}
{$text|lower}

{* Экранирование HTML *}
{$text|escape:'html'}
```

### Функции Smarty

```smarty
{* Форматирование URL *}
{formaturl path="find"}
{* Результат: /find/ или /ru/find/ *}

{* Получение конфигурации *}
{getConfig key='apps.geodata.api_key'}

{* Путь к медиафайлу *}
{mediaincpath data=$image type='preview'}
{mediaincpath data=$image type='full'}

{* Проверка прав доступа *}
{if $is_admin}
    {* Контент для администратора *}
{/if}
```

### Условные операторы

```smarty
{* IF-ELSE *}
{if $user_logged_in}
    <p>Добро пожаловать, {$user_name}!</p>
{else}
    <a href="/login">Войти</a>
{/if}

{* Множественные условия *}
{if $price > 0 && $price < 1000000}
    <span class="affordable">Доступная цена</span>
{elseif $price >= 1000000 && $price < 5000000}
    <span class="medium">Средняя цена</span>
{else}
    <span class="premium">Премиум</span>
{/if}

{* Проверка существования *}
{if isset($variable)}
    {$variable}
{/if}
```

### Циклы

```smarty
{* Foreach *}
{foreach from=$items item=item}
    <div class="item">{$item.name}</div>
{/foreach}

{* Foreach с ключом *}
{foreach from=$items key=id item=item}
    <div data-id="{$id}">{$item.name}</div>
{/foreach}

{* Section (устаревший, но используется) *}
{section name=i loop=$menu}
    <li><a href="{$menu[i].url}">{$menu[i].name}</a></li>
{/section}
```

---

## Настройка и доработка

### 1. Изменение логотипа

**Расположение:** `/template/frontend/realia/img/logo.png`

**Рекомендации:**
- Формат: PNG с прозрачностью
- Размер: 200x60 px
- Высокое разрешение для Retina-дисплеев

**В коде (header_contact_add.tpl):**
```smarty
<a href="{$estate_folder}/" class="logo">
    <img src="{$theme_folder}/img/logo.png" alt="{$site_name}">
</a>
```

### 2. Настройка контактов

**Файл:** `header_contact_add.tpl`

```smarty
<div class="contact-info">
    <i class="fa fa-phone"></i>
    <span>+7 (495) 123-45-67</span>
</div>
<div class="contact-info">
    <i class="fa fa-envelope"></i>
    <span>info@example.com</span>
</div>
```

### 3. Настройка социальных сетей

**Файл:** `footer.tpl`

```smarty
<ul class="social-icons">
    <li><a href="https://facebook.com/yourpage"><i class="fa fa-facebook"></i></a></li>
    <li><a href="https://twitter.com/yourpage"><i class="fa fa-twitter"></i></a></li>
    <li><a href="https://instagram.com/yourpage"><i class="fa fa-instagram"></i></a></li>
    <li><a href="https://vk.com/yourpage"><i class="fa fa-vk"></i></a></li>
</ul>
```

### 4. Изменение количества колонок в сетке

**Файл:** `realty_grid.tpl`

```smarty
{* 3 колонки (по умолчанию) *}
<div class="span4">
    {* Карточка объекта *}
</div>

{* Для 4 колонок измените на *}
<div class="span3">
    {* Карточка объекта *}
</div>

{* Для 2 колонок измените на *}
<div class="span6">
    {* Карточка объекта *}
</div>
```

### 5. Добавление нового поля в карточку объекта

**Файл:** `realty_grid.tpl`

```smarty
{foreach from=$grid_items item=item}
    <div class="property">
        {* Существующие поля *}
        <div class="price">{$item.price}</div>
        <div class="square">{$item.square} м²</div>
        
        {* Новое поле - этаж *}
        {if $item.floor ne ''}
            <div class="floor">
                <i class="fa fa-building"></i>
                Этаж: {$item.floor}
            </div>
        {/if}
    </div>
{/foreach}
```

### 6. Настройка слайдера на главной странице

**Файл:** `layout_home.tpl`

**Изменение настроек:**
```javascript
$('.iosSlider').iosSlider({
    autoSlide: true,              // Автопрокрутка
    autoSlideTimer: 5000,         // Интервал в мс (5 секунд)
    autoSlideTransTimer: 1000,    // Время перехода
    infiniteSlider: true,         // Бесконечный цикл
    snapToChildren: true,         // Привязка к слайдам
    desktopClickDrag: true        // Перетаскивание мышью
});
```

**Добавление нового слайда:**
```smarty
<div class="item">
    <img src="{$theme_folder}/images/slide-new.jpg" alt="Новый слайд">
    <div class="slider-info">
        <h2>Заголовок слайда</h2>
        <p>Описание слайда</p>
        <a href="/link" class="btn btn-primary">Подробнее</a>
    </div>
</div>
```

### 7. Изменение фильтров поиска

**Файл:** `advance_search_form.tpl`

**Добавление нового фильтра:**
```smarty
{* Существующие фильтры *}
<div class="form-group">
    <label>Город</label>
    <select name="city_id">...</select>
</div>

{* Новый фильтр - Тип отопления *}
<div class="form-group">
    <label>Тип отопления</label>
    <select name="heating_type" class="form-control">
        <option value="">Любой</option>
        <option value="central">Центральное</option>
        <option value="autonomous">Автономное</option>
        <option value="electric">Электрическое</option>
    </select>
</div>
```

### 8. Настройка пагинации

**Файл:** `common_pager.tpl`

**Параметры:**
```smarty
{* Количество элементов на странице *}
{assign var="per_page" value=12}

{* Количество страниц в навигации *}
{assign var="page_range" value=5}
```

### 9. Добавление виджета в сайдбар

**Файл:** `layout_basic.tpl`

```smarty
<div class="sidebar span3">
    {include file="agents_list.tpl"}
    {include file='right_special.tpl'}
    
    {* Новый виджет *}
    {include file='custom_widget.tpl'}
</div>
```

**Создайте файл** `custom_widget.tpl`:
```smarty
<div class="widget">
    <div class="title">
        <h3>Мой виджет</h3>
    </div>
    <div class="content">
        {* Содержимое виджета *}
        <p>Контент виджета</p>
    </div>
</div>
```

### 10. Редактирование подвала

**Файл:** `footer.tpl`

**Структура:**
```smarty
<div id="footer-top">
    <div class="row">
        <div class="span3">
            {* Колонка 1 - Новые объекты *}
        </div>
        <div class="span3">
            {* Колонка 2 - Избранные объекты *}
        </div>
        <div class="span3">
            {* Колонка 3 - Новости *}
        </div>
        <div class="span3">
            {* Колонка 4 - Контакты *}
            {* Можно изменить содержимое *}
        </div>
    </div>
</div>
```

---

## Создание пользовательских компонентов

### 1. Создание нового шаблона страницы

**Шаг 1:** Создайте файл `custom_page.tpl`

```smarty
<div class="custom-page">
    <div class="container">
        <h1>{$title}</h1>
        <div class="content">
            {$main}
        </div>
    </div>
</div>
```

**Шаг 2:** Зарегистрируйте в `main/main.php`

```php
$layouts = array(
    '_default' => 'layout_basic.tpl',
    'custom' => 'custom_page.tpl',  // Добавьте эту строку
    // ... остальные макеты
);
```

### 2. Создание виджета недавно просмотренных объектов

**Файл:** `recently_viewed.tpl`

```smarty
{if isset($smarty.session.recently_viewed) && is_array($smarty.session.recently_viewed)}
<div class="widget recently-viewed">
    <div class="title">
        <h3>Недавно просмотренные</h3>
    </div>
    <div class="content">
        {foreach from=$smarty.session.recently_viewed item=item}
            <div class="property-mini">
                <div class="image">
                    {if $item.img ne ''}
                        <img src="{mediaincpath data=$item.img type='preview'}" alt="{$item.title}">
                    {/if}
                </div>
                <div class="info">
                    <h4><a href="{$item.href}">{$item.title}</a></h4>
                    <span class="price">{$item.price|number_format:0:",":" "} ₽</span>
                </div>
            </div>
        {/foreach}
    </div>
</div>
{/if}
```

**Стили (custom.css):**
```css
.recently-viewed .property-mini {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.recently-viewed .image {
    width: 80px;
    height: 60px;
    margin-right: 10px;
    overflow: hidden;
}

.recently-viewed .image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recently-viewed .info h4 {
    font-size: 14px;
    margin: 0 0 5px 0;
}

.recently-viewed .price {
    font-weight: bold;
    color: #3aa0d1;
}
```

### 3. Создание калькулятора ипотеки

**Файл:** `mortgage_calculator_custom.tpl`

```smarty
<div class="widget mortgage-calculator">
    <div class="title">
        <h3>Калькулятор ипотеки</h3>
    </div>
    <div class="content">
        <form id="mortgage-form">
            <div class="form-group">
                <label>Стоимость недвижимости</label>
                <input type="number" id="property-cost" class="form-control" placeholder="5000000">
            </div>
            <div class="form-group">
                <label>Первоначальный взнос (%)</label>
                <input type="number" id="initial-payment" class="form-control" placeholder="20">
            </div>
            <div class="form-group">
                <label>Срок кредита (лет)</label>
                <input type="number" id="loan-term" class="form-control" placeholder="15">
            </div>
            <div class="form-group">
                <label>Процентная ставка (%)</label>
                <input type="number" id="interest-rate" class="form-control" placeholder="9.5" step="0.1">
            </div>
            <button type="button" onclick="calculateMortgage()" class="btn btn-primary">
                Рассчитать
            </button>
        </form>
        
        <div id="mortgage-result" style="display:none; margin-top: 20px;">
            <h4>Результат:</h4>
            <p>Сумма кредита: <strong id="loan-amount"></strong></p>
            <p>Ежемесячный платеж: <strong id="monthly-payment"></strong></p>
            <p>Переплата: <strong id="overpayment"></strong></p>
        </div>
    </div>
</div>

<script>
function calculateMortgage() {
    var cost = parseFloat($('#property-cost').val());
    var initialPercent = parseFloat($('#initial-payment').val());
    var years = parseFloat($('#loan-term').val());
    var rate = parseFloat($('#interest-rate').val());
    
    var initialPayment = cost * initialPercent / 100;
    var loanAmount = cost - initialPayment;
    var monthlyRate = rate / 100 / 12;
    var months = years * 12;
    
    var monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, months)) / 
                         (Math.pow(1 + monthlyRate, months) - 1);
    
    var totalPayment = monthlyPayment * months;
    var overpayment = totalPayment - loanAmount;
    
    $('#loan-amount').text(loanAmount.toLocaleString('ru-RU') + ' ₽');
    $('#monthly-payment').text(monthlyPayment.toLocaleString('ru-RU') + ' ₽');
    $('#overpayment').text(overpayment.toLocaleString('ru-RU') + ' ₽');
    $('#mortgage-result').fadeIn();
}
</script>
```

### 4. Блок сравнения объектов

**Файл:** `compare_widget.tpl`

```smarty
<div class="widget compare-widget">
    <div class="title">
        <h3>Сравнение объектов</h3>
    </div>
    <div class="content">
        <div id="compare-list">
            {if isset($smarty.session.compare) && is_array($smarty.session.compare)}
                <p>Выбрано объектов: <strong>{$smarty.session.compare|count}</strong></p>
                {if $smarty.session.compare|count > 0}
                    <a href="{formaturl path='compare'}" class="btn btn-primary btn-small">
                        Перейти к сравнению
                    </a>
                    <button onclick="clearCompare()" class="btn btn-small">
                        Очистить
                    </button>
                {/if}
            {else}
                <p>Нет объектов для сравнения</p>
            {/if}
        </div>
    </div>
</div>

<script>
function clearCompare() {
    if (confirm('Очистить список сравнения?')) {
        $.post('/ajax/clear_compare.php', function() {
            location.reload();
        });
    }
}
</script>
```

### 5. Виджет новостей с миниатюрами

**Файл:** `news_widget.tpl`

```smarty
{if isset($news_items) && is_array($news_items)}
<div class="widget news-widget">
    <div class="title">
        <h3>Новости</h3>
    </div>
    <div class="content">
        {foreach from=$news_items item=news}
            <div class="news-item">
                {if $news.image ne ''}
                    <div class="news-image">
                        <img src="{$news.image}" alt="{$news.title}">
                    </div>
                {/if}
                <div class="news-info">
                    <h4><a href="{$news.href}">{$news.title}</a></h4>
                    <span class="date">{$news.date|date_format:"%d.%m.%Y"}</span>
                    <p>{$news.preview_text|truncate:100:"..."}</p>
                </div>
            </div>
        {/foreach}
        <a href="{formaturl path='news'}" class="more-link">Все новости &rarr;</a>
    </div>
</div>
{/if}
```

---

## Интеграция с приложениями

### 1. Интеграция с приложением "Клиент"

Шаблон Realia тесно интегрирован с приложением "Клиент" (Client).

**Требования:**
- Версия приложения >= 1.0.2
- Приложение активировано в админ-панели

**Функционал:**
- Форма обратной связи
- Заказ звонка
- Онлайн-консультант

**Файлы:**
- `header_contact_add.tpl` — кнопка "Заказать звонок"
- `realty_view.tpl` — форма связи с агентом

**Пример формы:**
```smarty
<form id="contact-form" class="client-form">
    <input type="hidden" name="property_id" value="{$data.id.value}">
    <div class="form-group">
        <input type="text" name="name" placeholder="Ваше имя" required>
    </div>
    <div class="form-group">
        <input type="tel" name="phone" placeholder="Телефон" required>
    </div>
    <div class="form-group">
        <textarea name="message" placeholder="Сообщение"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Отправить</button>
</form>

<script src="{$estate_folder}/apps/client/js/clientorderajax.js"></script>
```

### 2. Интеграция с модулем "Жилые комплексы" (Complex)

**Файлы шаблонов:**
```
/template/frontend/realia/apps/complex/site/template/
├── complex_view.tpl    # Детальная страница ЖК
└── grid.tpl            # Список ЖК
```

**Пример вывода списка ЖК:**
```smarty
{if isset($complexes) && is_array($complexes)}
<div class="complexes-grid">
    {foreach from=$complexes item=complex}
        <div class="complex-item">
            <div class="complex-image">
                {if $complex.image ne ''}
                    <img src="{$complex.image}" alt="{$complex.name}">
                {/if}
            </div>
            <div class="complex-info">
                <h3><a href="{$complex.href}">{$complex.name}</a></h3>
                <div class="complex-address">{$complex.address}</div>
                <div class="complex-price">От {$complex.min_price|number_format:0:",":" "} ₽</div>
            </div>
        </div>
    {/foreach}
</div>
{/if}
```

### 3. Модель ContactUs

Модель для приема сообщений от пользователей.

**Проверка наличия:**
```php
// В админ-панели: Система → Модели → Проверить наличие contactus
```

**Создание, если отсутствует:**
1. Система → Модели → Добавить модель
2. Название: `contactus`
3. Добавьте поля:
   - `name` (text)
   - `phone` (text)
   - `email` (text)
   - `message` (textarea)
   - `property_id` (number)

### 4. Интеграция с картами

**Настройка типа карт:**

В админ-панели: **Настройки → Общее → Тип карт**

Доступные варианты:
- `yandex` — Яндекс.Карты
- `google` — Google Maps
- `leaflet_osm` — OpenStreetMap

**В шаблоне (header.tpl):**
```smarty
{if $map_type == 'yandex'}
    <script src="https://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU"></script>
{elseif $map_type == 'leaflet_osm'}
    <link rel="stylesheet" href="{$estate_folder}/apps/system/js/leaflet/leaflet.css" />
    <script src="{$estate_folder}/apps/system/js/leaflet/leaflet.js"></script>
{elseif $map_type == 'google'}
    <script src="https://maps.googleapis.com/maps/api/js?key={$google_api_key}"></script>
{/if}
```

> 🔒 **Примечание по безопасности:** При использовании внешних CDN рекомендуется добавлять атрибуты SRI (Subresource Integrity) для защиты от подмены скриптов. Пример:
> ```html
> <script src="https://cdn.example.com/script.js" 
>         integrity="sha384-hash..." 
>         crossorigin="anonymous"></script>
> ```

---

## Устранение неполадок

### Проблема 1: Не работает WYSIWYG-редактор

**Симптомы:**
- Поля textarea_editor не загружаются
- Ошибки JavaScript в консоли

**Решение:**
1. Проверьте, что используется CKEditor (не CLEditor)
2. Убедитесь, что папка `ckeditor` существует в корне сайта
3. Скачайте CKEditor: http://ckeditor.com/download
4. Настройки → Общее → WYSIWYG-редактор → Выберите CKEditor

### Проблема 2: Не отображаются изображения

**Симптомы:**
- Вместо изображений отображается "no_foto"
- Ошибки 404 для изображений

**Решение:**
1. Проверьте права на папку `/img/` (должны быть 755)
2. Убедитесь, что путь к изображениям корректен
3. Проверьте функцию `{mediaincpath}`:
```smarty
{mediaincpath data=$item.img[0] type='preview'}
```

### Проблема 3: Меню не раскрывается

**Симптомы:**
- Выпадающие меню не работают
- Субменю не отображаются

**Решение:**
1. Проверьте, что загружен `bootstrap.min.js`
2. Убедитесь, что нет конфликтов JavaScript
3. Откройте консоль браузера (F12) и проверьте ошибки

### Проблема 4: Карта не загружается

**Симптомы:**
- Пустая область вместо карты
- Ошибки в консоли

**Решение:**
1. Проверьте API ключ для Google Maps (если используется)
2. Убедитесь, что координаты корректны
3. Проверьте тип карты в настройках
4. Для Yandex/Google проверьте доступность API

### Проблема 5: Слайдер не работает

**Симптомы:**
- Изображения не переключаются
- Слайдер статичен

**Решение:**
1. Проверьте загрузку `jquery.iosslider.min.js`
2. Убедитесь, что jQuery загружен до IOSSlider
3. Проверьте инициализацию в `layout_home.tpl`:
```javascript
$(document).ready(function() {
    InitImageSlider();
});
```

### Проблема 6: Формы не отправляются

**Симптомы:**
- При нажатии "Отправить" ничего не происходит
- Ошибки отправки формы

**Решение:**
1. Проверьте, что приложение "Клиент" активно
2. Убедитесь, что модель `contactus` существует
3. Проверьте загрузку `clientorderajax.js`
4. Откройте консоль и проверьте AJAX запросы

### Проблема 7: Стили не применяются

**Симптомы:**
- Неправильное отображение элементов
- Отсутствие стилей

**Решение:**
1. Очистите кеш браузера (Ctrl+F5)
2. Проверьте порядок подключения CSS в `header.tpl`
3. Убедитесь, что файлы CSS доступны (проверьте в браузере)
4. Очистите кеш Smarty:
```bash
rm -rf /cache/smarty/*
```

### Проблема 8: Избранное не сохраняется

**Симптомы:**
- Объекты не добавляются в избранное
- Счетчик не обновляется

**Решение:**
1. Проверьте, что сессии PHP работают корректно
2. Убедитесь, что JavaScript функция `addToFavorites()` определена
3. Проверьте AJAX запросы в консоли браузера

### Проблема 9: Мобильная версия отображается неправильно

**Симптомы:**
- На мобильных устройствах элементы наезжают друг на друга
- Неправильный масштаб

**Решение:**
1. Проверьте мета-тег viewport в `header.tpl`:
```smarty
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```
2. Убедитесь, что подключен `bootstrap-responsive.css`
3. Проверьте медиа-запросы в CSS

### Проблема 10: Поиск не работает

**Симптомы:**
- Форма поиска не находит объекты
- Пустые результаты

**Решение:**
1. Проверьте, что в базе данных есть объекты
2. Убедитесь, что форма отправляется на правильный URL
3. Проверьте настройки индексации поиска
4. Проверьте логи ошибок PHP

---

## Лучшие практики

### 1. Организация кода

**Разделяйте логику и представление:**
```smarty
{* Плохо: *}
{if $data.city_id.value_string ne '' && $data.district_id.value_string ne '' && ...}

{* Хорошо: *}
{assign var="has_location" value=false}
{if $data.city_id.value_string ne ''}
    {assign var="has_location" value=true}
{/if}

{if $has_location}
    {* Код для отображения *}
{/if}
```

**Используйте include для повторяющихся блоков:**
```smarty
{* Вместо копирования кода в нескольких местах *}
{include file="property_card.tpl" item=$property}
```

### 2. Производительность

**Оптимизация изображений:**
```smarty
{* Используйте preview для списков *}
{mediaincpath data=$item.img[0] type='preview'}

{* Используйте full только для детальных страниц *}
{mediaincpath data=$item.img[0] type='full'}
```

**Ленивая загрузка изображений:**
```html
<img data-src="{$image_url}" class="lazy" alt="{$title}">

<script>
$(window).on('load', function() {
    $('.lazy').each(function() {
        var src = $(this).data('src');
        $(this).attr('src', src);
    });
});
</script>
```

**Минификация CSS и JS:**
```bash
# Используйте инструменты минификации
uglifyjs realia.js -o realia.min.js -c -m
cssnano style.css style.min.css
```

### 3. SEO оптимизация

**Правильные заголовки:**
```smarty
<title>{if $meta_title != ''}{$meta_title}{else}{$title} - {$site_name}{/if}</title>
<meta name="description" content="{$meta_description}">
<meta name="keywords" content="{$meta_keywords}">
```

**Микроразметка для объектов:**
```smarty
<div itemscope itemtype="http://schema.org/RealEstateListing">
    <h1 itemprop="name">{$data.title.value}</h1>
    <span itemprop="price">{$data.price.value}</span>
    <span itemprop="priceCurrency">RUB</span>
    <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
        <span itemprop="addressLocality">{$data.city.value_string}</span>
        <span itemprop="streetAddress">{$data.street.value_string}</span>
    </div>
</div>
```

**Canonical URL:**
```smarty
{if isset($canonicalurl)}
    <link rel="canonical" href="{$canonicalurl}"/>
{/if}
```

### 4. Безопасность

**Экранирование вывода:**
```smarty
{* Экранируйте пользовательский ввод *}
{$user_input|escape:'html'}

{* Для JavaScript *}
{$user_input|escape:'javascript'}

{* Для URL *}
{$user_input|escape:'url'}
```

**CSRF защита:**
```smarty
<form method="post">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    {* Поля формы *}
</form>
```

### 5. Доступность (Accessibility)

**Alt теги для изображений:**
```smarty
<img src="{$image}" alt="{$title}" title="{$title}">
```

**ARIA атрибуты:**
```html
<button aria-label="Добавить в избранное" onclick="addToFavorites({$id})">
    <i class="fa fa-heart"></i>
</button>
```

**Навигация с клавиатуры:**
```html
<a href="{$link}" tabindex="0">Ссылка</a>
```

### 6. Кросс-браузерная совместимость

**Префиксы CSS:**
```css
.element {
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
}
```

**Проверка поддержки:**
```javascript
if ('geolocation' in navigator) {
    // Код для геолокации
}
```

### 7. Версионирование и кеширование

**Версионирование статики:**
```smarty
<link href="{$theme_folder}/css/style.css?v=1.2.0" rel="stylesheet">
<script src="{$theme_folder}/js/realia.js?v=1.2.0"></script>
```

### 8. Тестирование

**Тестирование на разных устройствах:**
- Десктоп (Chrome, Firefox, Safari, Edge)
- Планшеты (iPad, Android)
- Мобильные (iPhone, Android)

**Инструменты:**
- Chrome DevTools (F12)
- BrowserStack для кросс-браузерного тестирования
- Google PageSpeed Insights для производительности

### 9. Документирование изменений

**Комментарии в коде:**
```smarty
{* 
  Блок избранных объектов
  Автор: Имя Разработчика
  Дата: 11.12.2025
  Описание: Отображает последние 6 избранных объектов пользователя
*}
{foreach from=$featured_properties item=property}
    {* Код *}
{/foreach}
```

**Changelog:**
Ведите файл `CHANGELOG.md` с историей изменений.

### 10. Резервное копирование

**Перед изменениями:**
```bash
# Создайте резервную копию шаблона
cp -r /template/frontend/realia /template/frontend/realia_backup_2025-12-11
```

**Git контроль версий:**
```bash
git add template/frontend/realia/
git commit -m "Добавлен новый виджет в sidebar"
```

---

## Дополнительные ресурсы

### Официальная документация

- **Сайт Sitebill:** https://www.sitebill.ru/
- **Форум поддержки:** https://www.sitebill.ru/forum/
- **Контакты:** https://www.sitebill.ru/ (форма обратной связи на сайте)

### Документация по технологиям

- **Smarty шаблоны:** https://www.smarty.net/docs/
- **Bootstrap 2.3:** https://getbootstrap.com/2.3.2/
- **jQuery:** https://api.jquery.com/

### Полезные инструменты

- **Chrome DevTools** — отладка и инспектирование
- **Sublime Text / VS Code** — редактирование кода
- **FileZilla** — FTP клиент
- **MAMP / XAMPP** — локальный сервер для разработки

---

## Заключение

Шаблон Realia предоставляет мощную и гибкую основу для создания сайта недвижимости. Следуя этой документации, вы сможете:

✅ Понять структуру и организацию шаблона
✅ Настроить внешний вид под свои требования
✅ Добавить новые функции и компоненты
✅ Интегрировать сторонние сервисы
✅ Оптимизировать производительность
✅ Обеспечить безопасность и доступность

Если у вас возникли вопросы или нужна помощь, обращайтесь в службу поддержки Sitebill.

**Удачной разработки! 🚀**
