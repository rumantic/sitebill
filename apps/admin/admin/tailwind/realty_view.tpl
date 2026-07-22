{literal}
<script>
$(document).ready(function(){
    $('#add_note').submit(function(){
        var form=$(this);
        var note=form.find('[name=note]').val();
        var id=form.find('[name=id]').val();
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: estate_folder+'/js/ajax.php',
            data: {action: 'add_note', id: id, note: note},
            success: function(resp){
                if(resp.status==1){
                    form.find('[name=note]').val('');
                    $('#notes_list').append(resp.html);
                }
            }
        });
        return false;
    });

    $(document).on('click', '#notes_list .delete_note', function(e){
        e.preventDefault();
        var $this=$(this);
        var id=$this.data('id');
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: estate_folder+'/js/ajax.php',
            data: {action: 'delete_note', note_id: id},
            success: function(resp){
                if(resp.status==1){
                    $this.closest('.note-item').remove();
                }
            }
        });
    });
});
</script>
{/literal}

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Data view -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        {$view_data}
    </div>

    <!-- Notes panel -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center space-x-2">
            <i class="fa fa-rss text-amber-500"></i>
            <h4 class="text-sm font-semibold text-gray-700">Заметки</h4>
        </div>

        <div class="p-4">
            <!-- Notes list -->
            <div class="space-y-4" id="notes_list">
                {foreach from=$view_data_notes item=view_data_note}
                    <div class="note-item relative bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-accent-600">{$view_data_note.fio}</span>
                            <div class="flex items-center space-x-3">
                                <span class="text-xs text-gray-500">
                                    <i class="fa fa-clock-o mr-1"></i>{$view_data_note.added_at}
                                </span>
                                <a href="#" class="delete_note text-red-400 hover:text-red-600 transition-colors" data-id="{$view_data_note.data_note_id}">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            </div>
                        </div>
                        <p class="text-sm text-gray-700">{$view_data_note.message|nl2br}</p>
                    </div>
                {/foreach}
            </div>

            <div class="border-t border-gray-200 my-4"></div>

            <!-- Add note form -->
            <form id="add_note">
                <input type="hidden" name="id" value="{intval($smarty.request.id)}">
                <div class="flex space-x-2">
                    <textarea placeholder="Добавьте заметку ..." class="flex-1 block w-full rounded-md border-slate-200 shadow-sm focus:border-accent focus:ring focus:ring-accent/20 focus:ring-opacity-50 text-sm" name="note" rows="2"></textarea>
                    <button type="submit" class="self-end inline-flex items-center px-4 py-2 bg-accent text-white text-sm font-medium rounded-lg hover:bg-accent-700 transition-colors">
                        <i class="fa fa-share mr-1"></i> Добавить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
