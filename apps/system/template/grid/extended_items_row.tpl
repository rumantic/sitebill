{if isset($disable_excel_export) and !$disable_excel_export}<a class="btn btn-warning btn-xs pull-right" href="?action={$action}&do=export" title="{_e t="Скачать записи в формате Excel"}"><i class="icon-white icon-download-alt"></i></a>{/if}
{if isset($disable_excel_import) and !$disable_excel_import}<a class="btn btn-info btn-xs pull-right" href="?action={$action}&do=import" title="{_e t="Загрузить записи в формате Excel"}"><i class="icon-white icon-upload"></i></a>{/if}
{if isset($disable_format_grid) and !$disable_format_grid}<a class="btn btn-pink btn-xs pull-right" href="?action={$action}&do=formatgrid" title="{_e t="Формировать сетку"}"><i class="icon-white icon-align-justify"></i></a>{/if}
{if isset($disable_pdf) and !$disable_pdf}{if $pdf_enable == 1}<a class="btn btn-warning btn-xs pull-right" href="?action={$action}&do=getpdf" title="{_e t="Скачать записи в формате PDF"}" download><i class="icon-white fa-print"></i></a>{/if}{/if}
{if $total_count != ''}<button class="btn btn-xs pull-right" disabled="disabled"><i class="icon-white icon-ok"></i> {_e t="Всего:"} {$total_count}</button>{/if}
{literal}
<script>
$(document).ready(function(){
	$('.editable_name_field').dblclick(function(e){
		var $this=$(this);
		var prev_text=$this.text();
		var target_table=$this.data('tbl');
		var target_id=$this.data('fid');
		var target_key=$this.data('key');

		var $description_editable_block=$('<div class="dz-preview-uploaded-item-description-editable"><input type="text" value="'+prev_text+'"><button class="btn btn-success btn-small save_desc"><i class="icon-white icon-ok"></i></button><button class="btn btn-danger btn-small canc_desc"><i class="icon-white icon-remove"></i></button></div>');


		//description_block.hide();
		//description_editable_block.show();

		var saveBtn=$description_editable_block.find('.save_desc');
		var cancBtn=$description_editable_block.find('.canc_desc');

		cancBtn.click(function(e){
			$description_editable_block.remove();
			$this.text(prev_text);
		});

		saveBtn.click(function(e){
			e.preventDefault();
			var val=$(this).prev('input').eq(0).val();
			if(val=='' || val==prev_text){
				$this.text(prev_text);
				$description_editable_block.remove();
			}else{
				$.ajax({
					url: estate_folder+'/js/ajax.php',
					data: {action: 'change_element_name', table: target_table, key: target_key, target_id: target_id, value: val},
					type: 'post',
					dataType: 'json',
					success: function(json){
						if(json.status==1){
							$this.text(json.text);
						}else{
							$this.text(prev_text);
						}
						$description_editable_block.remove();
					}
				});
			}
		});

		$this.append($description_editable_block);
		//console.log(3);
	});
});
</script>
{/literal}
