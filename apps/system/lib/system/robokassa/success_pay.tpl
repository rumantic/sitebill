<div class="alert alert-success" role="alert">
<h4 class="alert-heading">Платеж успешно выполнен!</h4>
<p>Сумма платежа: {$sum} {$currency}</p>
{if $accessor_enable}
<p>Проверьте ваш почтовый ящик, на него будет отправлен код доступа к контактам собственников.</p>
{else}
<p>Ваш баланс: {$balance} {$currency}</p>
{/if}
</div>
