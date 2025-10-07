# Документация по параметрам формы поиска недвижимости

## Обзор

Данный документ описывает все параметры поисковой формы и их применимость к различным типам недвижимости. Это позволяет создать адаптивную форму поиска, где отображаются только релевантные параметры для выбранного типа недвижимости.

## Типы недвижимости (topic_id)

Согласно форме поиска, поддерживаются следующие типы недвижимости:

- **6162** - Квартира
- **6163** - Дома и Дачи
- **6164** - Комната
- **6165** - Коммерческая
- **6166** - Коттедж
- **6167** - Новостройка
- **6168** - Пентхаус
- **6169** - Участок земли
- **6170** - Элитное жилье

## Параметры формы поиска

### Универсальные параметры (применимы ко всем типам)

| Параметр | Тип поля | Имя параметра | Описание |
|----------|----------|---------------|----------|
| **Расположение** | checkbox[] | `city_id[]` | Город/район расположения объекта |
| **Тип объявления** | checkbox[] | `optype[]` | Продажа/Аренда/Покупка/Съем/Обмен (1-5) |
| **Тип объекта** | checkbox[] | `topic_id[]` | Тип недвижимости (см. список выше) |
| **Цена** | text | `price_min`, `price` | Диапазон цены (от-до) |
| **Фото** | checkbox | `has_photo` | Наличие фотографий (0/1) |
| **Год постройки** | text | `year_from`, `year_to` | Диапазон года постройки |
| **Площадь** | text | `square_min`, `square_max` | Площадь в м² (от-до) |
| **Расстояние до центра** | text | `center_distance` | Расстояние до центра в метрах |

### Параметры для жилой недвижимости

Применимо к: **Квартира** (6162), **Комната** (6164), **Новостройка** (6167), **Пентхаус** (6168), **Элитное жилье** (6170)

| Параметр | Тип поля | Имя параметра | Описание |
|----------|----------|---------------|----------|
| **Срок аренды** | select | `limitation` | Посуточно/На всегда/Длительный срок (1-4) |
| **Планировка** | checkbox[] | `planning[]` | Старые/Новостройка/Недостроенные (1-3) |
| **Материал стен** | checkbox[] | `walls[]` | Глина/Кирпич/Металл/Бетон/Дерево/Другое (1-7) |
| **Ремонт** | checkbox[] | `repair_quality[]` | Без ремонта/Старый/Косметический/Евро/Люкс (1-6) |
| **Количество комнат** | text | `room_count_min`, `room_count_max` | Диапазон количества комнат |
| **Этаж** | text | `floor_min`, `floor_max` | Диапазон этажей |
| **Этажность здания** | text | `floor_count_min`, `floor_count_max` | Диапазон этажности здания |
| **Не последний этаж** | checkbox | `not_last_floor` | Исключить последний этаж (0/1) |
| **С мебелью** | checkbox | `hasfurniture` | Наличие мебели (0/1) |

### Параметры для частных домов

Применимо к: **Дома и Дачи** (6163), **Коттедж** (6166)

| Параметр | Тип поля | Имя параметра | Описание |
|----------|----------|---------------|----------|
| **Срок аренды** | select | `limitation` | Посуточно/На всегда/Длительный срок (1-4) |
| **Планировка** | checkbox[] | `planning[]` | Старые/Новостройка/Недостроенные (1-3) |
| **Материал стен** | checkbox[] | `walls[]` | Глина/Кирпич/Металл/Бетон/Дерево/Другое (1-7) |
| **Ремонт** | checkbox[] | `repair_quality[]` | Без ремонта/Старый/Косметический/Евро/Люкс (1-6) |
| **Количество комнат** | text | `room_count_min`, `room_count_max` | Диапазон количества комнат |
| **Этажность здания** | text | `floor_count_min`, `floor_count_max` | Диапазон этажности дома |
| **С мебелью** | checkbox | `hasfurniture` | Наличие мебели (0/1) |

**Исключаемые параметры**: `floor_min`, `floor_max`, `not_last_floor` (не применимо для частных домов)

### Параметры для участков земли

Применимо к: **Участок земли** (6169)

| Параметр | Тип поля | Имя параметра | Описание |
|----------|----------|---------------|----------|
| *(только универсальные параметры)* | - | - | - |

**Исключаемые параметры**:
- `limitation` - срок аренды
- `planning[]` - планировка
- `walls[]` - материал стен
- `repair_quality[]` - ремонт
- `room_count_min`, `room_count_max` - количество комнат
- `floor_min`, `floor_max` - этаж
- `floor_count_min`, `floor_count_max` - этажность
- `not_last_floor` - не последний этаж
- `hasfurniture` - мебель

### Параметры для коммерческой недвижимости

Применимо к: **Коммерческая** (6165)

| Параметр | Тип поля | Имя параметра | Описание |
|----------|----------|---------------|----------|
| **Срок аренды** | select | `limitation` | Посуточно/На всегда/Длительный срок (1-4) |
| **Планировка** | checkbox[] | `planning[]` | Старые/Новостройка/Недостроенные (1-3) |
| **Материал стен** | checkbox[] | `walls[]` | Глина/Кирпич/Металл/Бетон/Дерево/Другое (1-7) |
| **Ремонт** | checkbox[] | `repair_quality[]` | Без ремонта/Старый/Косметический/Евро/Люкс (1-6) |
| **Этаж** | text | `floor_min`, `floor_max` | Диапазон этажей |
| **Этажность здания** | text | `floor_count_min`, `floor_count_max` | Диапазон этажности здания |

**Исключаемые параметры**: `room_count_min`, `room_count_max`, `not_last_floor`, `hasfurniture`

## Матрица применимости параметров

| Параметр | Квартира | Комната | Дома/Дачи | Коттедж | Новостр. | Пентхаус | Элитное | Участок | Коммерч. |
|----------|:--------:|:-------:|:---------:|:-------:|:--------:|:--------:|:-------:|:-------:|:--------:|
| city_id | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| optype | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| topic_id | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| price | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| has_photo | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| year_from/to | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| square | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| center_distance | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| limitation | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| planning | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| walls | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| repair_quality | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| room_count | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| floor | ✓ | ✓ | ✗ | ✗ | ✓ | ✓ | ✓ | ✗ | ✓ |
| floor_count | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| not_last_floor | ✓ | ✓ | ✗ | ✗ | ✓ | ✓ | ✓ | ✗ | ✗ |
| hasfurniture | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |

## Пример реализации адаптивной формы

### JavaScript для управления видимостью полей

```javascript
// Конфигурация параметров для каждого типа недвижимости
const propertyTypeConfig = {
    '6162': { // Квартира
        show: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor', 'floor_count', 'not_last_floor', 'hasfurniture'],
        hide: []
    },
    '6163': { // Дома и Дачи
        show: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor_count', 'hasfurniture'],
        hide: ['floor', 'not_last_floor']
    },
    '6164': { // Комната
        show: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor', 'floor_count', 'not_last_floor', 'hasfurniture'],
        hide: []
    },
    '6165': { // Коммерческая
        show: ['limitation', 'planning', 'walls', 'repair_quality', 
               'floor', 'floor_count'],
        hide: ['room_count', 'not_last_floor', 'hasfurniture']
    },
    '6166': { // Коттедж
        show: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor_count', 'hasfurniture'],
        hide: ['floor', 'not_last_floor']
    },
    '6167': { // Новостройка
        show: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor', 'floor_count', 'not_last_floor', 'hasfurniture'],
        hide: []
    },
    '6168': { // Пентхаус
        show: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor', 'floor_count', 'not_last_floor', 'hasfurniture'],
        hide: []
    },
    '6169': { // Участок земли
        show: [],
        hide: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor', 'floor_count', 'not_last_floor', 'hasfurniture']
    },
    '6170': { // Элитное жилье
        show: ['limitation', 'planning', 'walls', 'repair_quality', 'room_count', 
               'floor', 'floor_count', 'not_last_floor', 'hasfurniture'],
        hide: []
    }
};

// Функция для управления видимостью полей
function updateFormFields(selectedTypes) {
    // Если ничего не выбрано, показываем все поля
    if (selectedTypes.length === 0) {
        $('.search-form-field').show();
        return;
    }
    
    // Собираем параметры для скрытия
    let fieldsToHide = new Set();
    
    selectedTypes.forEach(typeId => {
        if (propertyTypeConfig[typeId] && propertyTypeConfig[typeId].hide) {
            propertyTypeConfig[typeId].hide.forEach(field => fieldsToHide.add(field));
        }
    });
    
    // Скрываем нерелевантные поля
    fieldsToHide.forEach(fieldName => {
        $(`.search-form-field[data-field="${fieldName}"]`).hide();
    });
    
    // Показываем релевантные поля
    Object.keys(propertyTypeConfig).forEach(typeId => {
        if (selectedTypes.includes(typeId) && propertyTypeConfig[typeId].show) {
            propertyTypeConfig[typeId].show.forEach(fieldName => {
                if (!fieldsToHide.has(fieldName)) {
                    $(`.search-form-field[data-field="${fieldName}"]`).show();
                }
            });
        }
    });
}

// Обработчик изменения типа недвижимости
$(document).on('change', 'input[name="topic_id[]"]', function() {
    let selectedTypes = [];
    $('input[name="topic_id[]"]:checked').each(function() {
        selectedTypes.push($(this).val());
    });
    updateFormFields(selectedTypes);
});
```

### HTML-разметка с data-атрибутами

Для корректной работы скрипта необходимо добавить атрибут `data-field` к контейнерам полей формы:

```html
<!-- Пример для параметра "Этаж" -->
<div class="beds control-group search-form-field" data-field="floor" style="display: block;">
    <label class="control-label beds1-floor" for="inputType">Этаж от</label>
    <div class="controls bed1-floor">
        <input name="floor_min" type="text" value="0">
    </div>
</div>

<!-- Пример для параметра "Этажность" -->
<div class="beds control-group search-form-field" data-field="floor_count" style="display: block;">
    <label class="control-label beds-floorcount" for="inputType">Этажность от</label>
    <div class="controls beds1-floorcount">
        <input name="floor_count_min" type="text" value="0">
    </div>
</div>
```

## Рекомендации по использованию

1. **Добавьте атрибут `data-field`** ко всем полям формы, которые должны скрываться/показываться в зависимости от типа недвижимости.

2. **Группируйте связанные поля** - например, `floor_min` и `floor_max` должны иметь одинаковый `data-field="floor"`.

3. **Используйте класс `search-form-field`** для всех управляемых полей формы.

4. **Очищайте значения скрытых полей** при их скрытии, чтобы они не влияли на результаты поиска.

5. **Сохраняйте состояние формы** при навигации назад из результатов поиска.

## Значения перечислимых параметров

### optype (Тип объявления)
- `1` - Продам
- `2` - Сдам
- `3` - Куплю
- `4` - Сниму
- `5` - Обменяю

### limitation (Срок аренды)
- `0` - выбрать срок (не выбрано)
- `1` - Посуточно
- `2` - На всегда
- `3` - На длительный срок
- `4` - Любая

### planning (Планировка)
- `1` - Старые
- `2` - Новостройка
- `3` - Недостроенные

### walls (Материал стен)
- `1` - Глина
- `2` - Кирпич жжёный
- `3` - Металлический
- `4` - Бетон
- `5` - Деревянный
- `6` - Другое
- `7` - Любая

### repair_quality (Ремонт)
- `1` - Без ремонта
- `2` - Старый
- `3` - Косметический
- `4` - Евро
- `5` - Люкс
- `6` - Любая

## Примечания

- Все параметры типа checkbox могут принимать множественные значения (массив)
- Параметры диапазонов (цена, площадь, комнаты, этаж) обрабатываются парами (min/max или from/to)
- Слайдеры автоматически синхронизируются с текстовыми полями для удобства пользователя
- Для участков земли площадь может измеряться в других единицах (сотки, гектары)
