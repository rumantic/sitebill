{literal}
<script>


$(document).ready(function(){
	$('#add_note').submit(function(){
		var form=$(this);
		var note=form.find('[name=note]').val();
		var id=form.find('[name=id]').val();
		//console.log(note);
		//console.log(id);
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
					$this.parents('.itemdiv').eq(0).remove();
				}
			}
		});
	});
});
</script>
{/literal}
<div class="row-fluid">
	<div class="span6">{$view_data}</div>
	<div class="span6">
		<div class="widget-box no-padding">
			<div class="widget-header">
				<h4 class="widget-title lighter smaller">
					<i class="ace-icon fa fa-rss orange"></i>Заметки
				</h4>

			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					<!-- #section:pages/dashboard.comments -->
							<div class="comments" id="notes_list">
								{foreach from=$view_data_notes item=view_data_note}
								<div class="itemdiv commentdiv">
									<div class="body">
										<div class="name">
											<a href="#">{$view_data_note.fio}</a>
										</div>

										<div class="time">
											<i class="ace-icon fa fa-clock-o"></i>
											<span class="green">{$view_data_note.added_at}</span>
										</div>

										<div class="text">
											<i class="ace-icon fa fa-quote-left"></i>{$view_data_note.message|nl2br}
										</div>
									</div>

									<div class="tools">
										<div class="action-buttons bigger-125">
											<a href="#" class="delete_note" data-id="{$view_data_note.data_note_id}">
												<i class="ace-icon fa fa-trash-o red"></i>
											</a>
										</div>
									</div>
								</div>
								{/foreach}
								
								
							</div>

							<div class="hr hr8"></div>
							

							<!-- /section:pages/dashboard.comments -->
							<form id="add_note">
					<div class="form-actions">
						<div class="input-group">
							<input type="hidden" name="id" value="{intval($smarty.request.id)}">
							<textarea placeholder="Добавьте заметку ..." class="form-control span12" name="note" width="100%"></textarea>
							<span class="input-group-btn">
								<button class="btn btn-sm btn-info no-radius" type="submit">
									<i class="ace-icon fa fa-share"></i>
									Добавить заметку
								</button>
							</span>
						</div>
					</div>
				</form>
				</div><!-- /.widget-main -->
				
			</div><!-- /.widget-body -->
		</div>
		
	</div>
</div>