<form  action="{$MAIN_URL}/sharelist/{$sharelist_id}" method="post">
    <input type="hidden" name="data_id" value="{$sharelist_data_id}">
    <input type="hidden" name="do" value="delete_done">
    <input type="hidden" name="sharelist_id" value="{$sharelist_id}">
    <button class="btn btn-sm btn-danger" type="submit">Удалить из списка</button>
</form>
