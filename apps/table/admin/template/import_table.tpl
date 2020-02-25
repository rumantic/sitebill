<form method="post" enctype="multipart/form-data" action="{$estate_folder}/admin/">
  <fieldset>
    <legend>Импорт модели</legend>
    <label>Описание поля</label>
    <input type="file" name="importfile" />
    <span class="help-block">Файл модели в формате JSON</span>
    <label class="checkbox">
      <input type="checkbox" name="rewrite_fields"> Перезаписать существующие поля
    </label>
    <input type="hidden" value="{$table_id}" name="table_id" />
    <input type="hidden" value="table" name="action" />
    <input type="hidden" value="importtable" name="do" />
    <input type="submit" name="submit" class="btn" value="Импортировать">
  </fieldset>
</form>