<!--Каталог со скриптом ипотечного калькулятора-->
{assign var=src_url value='/template/frontend/realia/angular/'}
<!--Ширина блока-->
{assign var=width value='100%'}
<!--Высота блока-->
{assign var=height value='650'}

<!--Идентификатор объявления-->
{assign var=realty_id value=$data.id.value}
<!--Цена объекта-->
{assign var=realty_price value=$data.price.value}
<!--Срок кредита в годах-->
{assign var=years value=15}
<!--Первоначальный взнос в процентах от стоимости объекта-->
{assign var=down_percent value=50}
<!--Ставка по кредиту в процентах-->
{assign var=percent value=6}
<!--Показывать переплату 0/1-->
{assign var=show_overpayment value=0}
<!--Показывать сумму всех платежей 0/1-->
{assign var=show_credit_sum value=0}
<!--Верхний текст-->
{assign var=top_text value="Ежемесячный платеж:"}
<!--Нижний текст-->
{assign var=bottom_text value="по двум документам!"}

<iframe src="{$estate_folder}/apps/cloud/runner.php?run=calculator&realty_id={$realty_id}&realty_price={$realty_price}&years={$years}&down_percent={$down_percent}&percent={$percent}&top_text={$top_text}&bottom_text={$bottom_text}&show_credit_sum={$show_credit_sum}&show_overpayment={$show_overpayment}" style="border: 0px; overflow: hidden;" border="0" width="{$width}" height="{$height}"></iframe>
<div align="center"><a href="/ipotekaorder/" class="btn btn-primary" ><i class="icon-white icon-envelope"></i> Оформить ипотеку</a></div>
