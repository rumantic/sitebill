<form action="{$estate_folder}/admin/" method="post">
    <fieldset>
        <legend>Добавление наставника</legend>
        <label>Стажер</label>
        {$el.hash.user_id.html}
        <span class="help-block">Выберите аккаунт стажера</span>
        <label>Наставник</label>
        {$el.hash.parent_user_id.html}
        <span class="help-block">Выберите аккаунт наставника</span>
        <button type="submit" class="btn">Сохранить</button>
        <input type="hidden" name="action" value="cowork">
        <input type="hidden" name="do" value="add">
    </fieldset>
</form>