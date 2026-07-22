# Admin Template Development Guide

Руководство по разработке и переключению шаблонов админки SiteBill CMS.

## Структура

```
apps/admin/admin/
├── backend.php              # Основной entry point (Smarty init + switching)
├── template1/               # Шаблон по умолчанию (Bootstrap 3/4 + ACE Admin)
│   ├── main.tpl             # Корневой HTML-обёртка
│   ├── main_body_classic.tpl
│   ├── main_body_object_only.tpl
│   ├── sidebar.tpl
│   ├── top_nav_notify.tpl
│   ├── data_form.tpl
│   ├── controls.tpl
│   ├── controls_js.tpl
│   ├── data_top_menu.tpl.html
│   ├── tabs.tpl
│   ├── page.tpl
│   ├── realty_view.tpl
│   ├── realty_grid.tpl
│   ├── realty_grid_wdg.tpl
│   ├── batch_field_edit.tpl
│   ├── data_form_batch_update.tpl
│   ├── data_form_front.tpl
│   ├── attachments_block.tpl
│   ├── main_simple.tpl
│   ├── css/
│   └── assets/              # ACE Admin CSS/JS/fonts
├── tailwind/                # Новый шаблон (Tailwind CSS)
│   ├── main.tpl
│   ├── main_body_classic.tpl
│   ├── ... (аналогичный набор .tpl)
│   └── css/custom.css       # Совместимость Bootstrap-классов
└── TEMPLATE_DEVELOPMENT.md  # Этот файл
```

## Механизм переключения шаблонов

### Приоритет выбора шаблона

1. **GET-параметр** `?admin_template=tailwind` — наивысший приоритет, сохраняется в сессию
2. **Сессия** `$_SESSION['admin_template']` — сохраняется между загрузками
3. **Конфиг** `apps.admin.template` — значение из БД (глобальная настройка)
4. **Default** — `template1`

### Белый список

Допустимые значения задаются массивом:
```php
$_admin_template_allowed = array('template1', 'tailwind');
```

### Файлы с логикой переключения

| Файл | Назначение |
|------|-----------|
| `apps/admin/admin/backend.php` | Основная админка (GET + session + config) |
| `apps/system/bootstrap.php` | Laravel bridge (session + config) |
| `apps/api/api_header.php` | API запросы (session + config) |
| `apps/admin3/Http/Controllers/Admin3Controller.php` | Admin3 legacy fallback (session + config) |
| `tests/SitebillTestCase.php` | Тесты (session only) |

**Важно**: при добавлении нового шаблона нужно обновить массив `$_admin_template_allowed` **во всех 5 файлах**.

### Smarty-переменные для UI

В `backend.php` присваиваются:
```php
$smarty->assign('admin_template', $_admin_template);           // текущий шаблон
$smarty->assign('admin_template_allowed', $_admin_template_allowed); // список доступных
```

Используйте в `.tpl` для отображения переключателя.

## Как добавить новый шаблон

1. Создать папку `apps/admin/admin/<имя_шаблона>/`
2. Скопировать все `.tpl` файлы из `template1/` (или `tailwind/`)
3. Добавить `<имя_шаблона>` в массив `$_admin_template_allowed` во всех 5 файлах выше
4. Обновить страницу с `?admin_template=<имя_шаблона>`
5. Повысить версию в `apps/admin/admin.xml`

## Smarty-переменные (доступны в шаблонах)

### Основные

| Переменная | Описание |
|-----------|----------|
| `$assets_folder` | URL папки текущего шаблона (для CSS/JS/images) |
| `$MAIN_URL` | Корневой URL сайта |
| `$ADMIN_BASE` | URL админки |
| `$estate_folder` | URL фронтенда |
| `$estate_folder_control` | URL панели управления |
| `$SITEBILL_DOCUMENT_ROOT` | Абсолютный путь к корню проекта |
| `$content` | HTML-контент текущей страницы |
| `$iframe_mode` | Режим показа: пустой = полный, иначе — минимальный |

### Навигация

| Переменная | Описание |
|-----------|----------|
| `$interface.sidebar` | Массив меню (categories → items → submenu) |
| `$breadcrumbs_array` | Хлебные крошки `[{url, title}]` |
| `$admin_template` | Имя текущего шаблона |
| `$admin_template_allowed` | Массив допустимых шаблонов |

### Форма данных (`data_form.tpl`)

| Переменная | Описание |
|-----------|----------|
| `$form_elements` | Основной массив полей формы |
| `$tab_array` | Массив вкладок (если multi-tab) |
| `$tabs_mode` | Режим: `single_tab`, `left_tab`, `step_tab` |
| `$table_name` | Имя текущей таблицы |
| `$primary_key` | Имя первичного ключа |
| `$eid` | ID записи (0 = новая) |
| `$edit_mode` | true при редактировании |
| `$action` | Имя текущего действия |

### Пользователь

| Переменная | Описание |
|-----------|----------|
| `$current_user_info` | Объект текущего пользователя |
| `$current_user_info.fio.value` | ФИО |
| `$current_user_info.imgalt.value` | URL аватара |

## Tailwind-специфика

### CDN

Tailwind подключается через CDN в `main.tpl`:
```html
<script src="https://cdn.tailwindcss.com"></script>
```
Для production рекомендуется перейти на сборку через PostCSS/Vite.

### Совместимость с Bootstrap JS

Все jQuery/Bootstrap JS плагины остаются подключёнными (bootstrap-editable, jQuery UI, gritter, sitebillcore.js и т.д.). Их стили обеспечиваются файлом `css/custom.css`, который переопределяет Bootstrap-классы (`.btn`, `.table`, `.alert`, `.pagination`, `.widget-box`) под Tailwind-стиль.

### Скоп стилей

Все стили в `css/custom.css` обёрнуты в `.tw-admin` — класс на `<body>`:
```css
.tw-admin .btn { ... }
.tw-admin .table { ... }
```

### Шаблоны без переработки

Следующие .tpl скопированы из template1 без изменений (содержат сложную JS-логику):
- `realty_grid.tpl` — грид объектов
- `realty_grid_wdg.tpl` — грид виджетов
- `batch_field_edit.tpl` — пакетное редактирование поля
- `data_form_batch_update.tpl` — пакетное обновление
- `data_form_front.tpl` — фронтенд-форма
- `attachments_block.tpl` — блок вложений
- `main_simple.tpl` — минимальный layout

Они стилизуются через `css/custom.css`. Для полной переработки достаточно заменить файл — Smarty подхватит автоматически.

## Важные JavaScript-зависимости

Шаблоны завязаны на следующие глобальные скрипты:
- **jQuery 3.3.1** + jQuery UI + jQuery Cookie
- **Bootstrap 3.x JS** (modals, dropdowns, tooltips, collapse)
- **sitebillcore.js** — AJAX-обёртки, grid refresh, формы
- **form_tabs.js** — логика табов в data_form
- **bootstrap-editable** — inline-редактирование в гриде
- **bootstrap-tag.min.js** — теги

Эти скрипты подключаются в `main.tpl` и НЕ должны быть удалены при создании нового шаблона.

## Workflow для AI-агента

1. Ознакомиться с этим документом
2. Изучить конкретный `.tpl` из `template1/` (оригинал)
3. Открыть аналогичный `.tpl` из `tailwind/` (текущая версия)
4. Внести изменения, сохраняя все Smarty-переменные и JS-вызовы
5. Проверить `css/custom.css` на наличие стилей для используемых Bootstrap-классов
6. При добавлении нового Bootstrap-класса — добавить его стиль в `css/custom.css`
7. Повысить версию в `apps/admin/admin.xml`
