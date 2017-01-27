<script type="text/javascript">
$(document).ready(function () {
    $( "#show_password" ).on( "click", function() {
	if ($("input[name=jabber_password]").get(0).type=='password')  {
	    $('input[name=jabber_password]').get(0).type='text';
	    $( "#show_password" ).html('скрыть');
	} else  {
	    $('input[name=jabber_password]').get(0).type='password';
	    $( "#show_password" ).html('показать');
	}
    });    
});
</script>

<div class="row-fluid">
    <div class="span12">
	<div class="alert alert-block alert-warning">
	<p>Для отправки и получения сообщений вы также можете подключиться с помощью любого Jabber-клиента. <a href="http://jabberworld.info/%D0%9A%D0%BB%D0%B8%D0%B5%D0%BD%D1%82%D1%8B_Jabber" target="_blank">Полный список доступных клиентов тут</a></p>
	<p>При подключении вам нужно использовать</p>
	<p><strong>sitebill.ru</strong> - jabber сервер</p>
	<p><strong>логин</strong> - логин из формы ниже (если вы хотите сменить логин или зарегистрировать новый то можете это сделать в форме ниже)</p>
	<p><strong>пароль</strong> - пароль из формы ниже</p>
	</div>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
	<a href="?action=messenger" class="btn"><i class="ace-icon fa fa-arrow-left icon-on-left"></i>Назад к списку чатов</a>
	<a href="?action=messenger&do=new" class="btn {if $smarty.request.do == 'new' or $smarty.request.do == 'new_done'}btn-success{/if}">Учетная запись</a>
	<a href="?action=messenger&do=edit" class="btn {if $smarty.request.do == 'edit' or $smarty.request.do == 'edit_done'}btn-success{/if}">Изменить или создать новую учетную запись</a>
    </div>
</div>

