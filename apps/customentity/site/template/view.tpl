Здесь должен быть шаблон для объекта модели {$entity_info.entity_name}<br>
Разместите шаблон для этого вывода в папке /template/frontend/ВАША_ТЕМА/apps/customentiry/site/template/ИМЯ_ШАБЛОНА.tpl и укажите имя шаблона (ИМЯ_ШАБЛОНА.tpl) в настройках обработчика.<br>
Доступные в этом шаблоне переменные:<br>
{literal}{$entity_item}{/literal} - просматриваемый объект<br>
{literal}{$entity_info}{/literal} - информация о текущей модели<br>