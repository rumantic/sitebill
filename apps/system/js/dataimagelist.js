DataImagelist={
	deleteImage: function(el,img_id, data_id, table, key){
		if(confirm('Действительно хотите удалить изображение?')){
			var parentel=$(el).parents('.preview_admin');
			$.ajax({
				url: estate_folder + "/js/ajax.php?action=delete_image",
				data: 'table_name='+table+'&image_id='+img_id+'&data_id='+data_id+'&key='+key,
				dataType: 'text',
				success: function(json){
					if(json=='ok'){
						parentel.fadeOut(function(){parentel.remove();});
					}
					
				}
			});
		}
		
		return false;
	},
	dz_clearImages: function(el, pk_value, table, pk_name, field_name){
		if(confirm('Действительно хотите удалить все изображения?')){
			var parentel=$(el).parents('.dz-preview-uploaded').eq(0);
			$.ajax({
				url: estate_folder + "/js/ajax.php?action=dz_imagework",
				data: 'what=delete_all&table_name='+table+'&key='+pk_name+'&key_value='+pk_value+'&field_name='+field_name,
				dataType: 'text',
				success: function(json){
					if(json=='ok'){
						parentel.find('.dz-preview-uploaded-list').remove();
					}
				}
			});
		}
		return false;
	},
	dz_deleteImage: function(el, pk_value, table, pk_name, field_name){
		if(confirm('Действительно хотите удалить изображение?')){
			var parentel=$(el).parents('.dz-preview-uploaded-item').eq(0);
			var parent_el=$(el).parents('.dz-preview-uploaded-list').eq(0);
			var all_els=parent_el.find('.dz-preview-uploaded-item');
			var current_element_index=all_els.index(parentel);
			if(current_element_index!==-1){
				$.ajax({
					url: estate_folder + "/js/ajax.php?action=dz_imagework",
					data: 'what=delete&table_name='+table+'&current_position='+current_element_index+'&key='+pk_name+'&key_value='+pk_value+'&field_name='+field_name,
					dataType: 'text',
					success: function(json){
						if(json=='ok'){
							parentel.fadeOut(function(){parentel.remove();});
						}
						
					}
				});
			}
			
		}
		
		return false;
	},
	dz_upImage: function(el, pk_value, table, pk_name, field_name){
		var parent_el=$(el).parents('.dz-preview-uploaded-list').eq(0);
		var parentel=$(el).parents('.dz-preview-uploaded-item').eq(0);
		var all_els=parent_el.find('.dz-preview-uploaded-item');
		var current_element_index=1+all_els.index(parentel);
		if(current_element_index>1){
			var prev=parentel.prev('.dz-preview-uploaded-item');
			$.ajax({
				url: estate_folder + "/js/ajax.php?action=dz_imagework",
				data: 'what=reorder&table_name='+table+'&current_position='+(current_element_index-1)+'&key='+pk_name+'&reorder=up&key_value='+pk_value+'&field_name='+field_name,
				dataType: 'text',
				success: function(json){
					if(json=='ok'){
						parentel.fadeOut('slow',function(){
							parentel.insertBefore(prev).fadeIn('slow');
						});
					}
				}
			});
		}
		return false;
	},
	dz_downImage: function(el, pk_value, table, pk_name, field_name){
		var parent_el=$(el).parents('.dz-preview-uploaded-list').eq(0);
		var parentel=$(el).parents('.dz-preview-uploaded-item').eq(0);
		var all_els=parent_el.find('.dz-preview-uploaded-item');
		var total_count=all_els.length;
		var current_element_index=1+all_els.index(parentel);
		if(current_element_index<total_count){
			var next=parentel.next('.dz-preview-uploaded-item');
			$.ajax({
				url: estate_folder + "/js/ajax.php?action=dz_imagework",
				data: 'what=reorder&table_name='+table+'&current_position='+(current_element_index-1)+'&key='+pk_name+'&reorder=down&key_value='+pk_value+'&field_name='+field_name,
				dataType: 'text',
				success: function(json){
					if(json=='ok'){
						parentel.fadeOut('slow',function(){
							parentel.insertAfter(next).fadeIn('slow');
						});
					}
				}
			});
		}
		return false;
	},
	dz_makeMain: function(el, pk_value, table, pk_name, field_name){
		var parent_el=$(el).parents('.dz-preview-uploaded-list').eq(0);
		var parentel=$(el).parents('.dz-preview-uploaded-item').eq(0);
		var all_els=parent_el.find('.dz-preview-uploaded-item');
		var current_element_index=1+all_els.index(parentel);
		
		
		
		var gross_parent=parentel.parents().eq(0);
		$.ajax({
			url: estate_folder + "/js/ajax.php?action=dz_imagework",
			data: 'what=make_main&table_name='+table+'&current_position='+(current_element_index-1)+'&key='+pk_name+'&reorder=down&key_value='+pk_value+'&field_name='+field_name,
			dataType: 'text',
			success: function(json){
				if(json=='ok'){
					parentel.fadeOut('slow',function(){
						parentel.prependTo(parent_el).fadeIn('slow');
					});
				}
			}
		});
		
		
		return false;
	},
	dz_dblClick: function(el, pk_value, table, pk_name, field_name){
		var description_block=$(el);
		var description_editable_block=description_block.next('.dz-preview-uploaded-item-description-editable').eq(0);
		
		
		description_block.hide();
		description_editable_block.show();
		
		var saveBtn=description_editable_block.find('.save_desc');
		var cancBtn=description_editable_block.find('.canc_desc');
		var content_input=description_editable_block.find('input');
		var parent=description_block.parents('.dz-preview-uploaded-list').eq(0);
		var all_els=parent.find('.dz-preview-uploaded-item-description');
		var current_element_index=all_els.index(description_block);
		//console.log(current_element_index);
		//return false;
		var old_content=content_input.val();
		
		saveBtn.click(function(e){
			var _description_block=description_block;
			var _description_editable_block=description_editable_block;
			var _content_input=content_input;
			var _old_content=old_content;
			
			var new_content=content_input.val();
			$.ajax({
				url: estate_folder+'/js/ajax.php?action=dz_imagework',
				type: 'POST',
				dataType: 'text',
				data: 'what=change_title&title='+new_content+'&table_name='+table+'&current_position='+current_element_index+'&key='+pk_name+'&key_value='+pk_value+'&field_name='+field_name,
				success: function(title) {
					_content_input.val(title);
					_description_block.text(title);
					_description_block.show();
					_description_editable_block.hide();
				},
				error: function(){
					_content_input.val(_old_content);
					_description_block.show();
					_description_editable_block.hide();
				}
			});
			$(this).unbind('click');
			e.preventDefault();
		});
		
		cancBtn.click(function(e){
			content_input.val(old_content);
			description_block.show();
			description_editable_block.hide();
			$(this).unbind('click');
			e.preventDefault();
		});
		
	},
	dz_attachDblclick: function(class_name, pk_value, table, pk_name, field_name){
		var uplblock=$('.'+class_name);
		
		uplblock.find('.dz-preview-uploaded-item-description').click(function(e){
			e.preventDefault();
		});
		uplblock.find('.dz-preview-uploaded-item-description').dblclick(function(e){
			var _this=$(this);
			var editable=_this.next('.dz-preview-uploaded-item-description-editable');
			_this.hide();
			editable.show();
			var input=editable.find('input');
			editable.find('.save_desc').click(function(e){
				var _this=_this;
				var c=input.val();
				$.ajax({
					url: estate_folder+'/js/ajax.php?action=dz_imagework',
					type: 'POST',
					dataType: 'text',
					data: 'what=change_title&title='+c,
					success: function(title) {
						_this.html(c);
					},
					error: function(){
						_this.html(content);
					}
				});
				e.preventDefault();
			});
			editable.find('.canc_desc').click(function(e){
				editable.find('input').val(_this.text());
				_this.show();
				editable.hide();
				e.preventDefault();
			});
			/*var old_element=$(this);
			var old_content=old_element.html();
			
			var new_element=$('<input type="text" value="'+old_content+'" />');
			_this.replaceWith(new_element);
			
			var all_editable=uplblock.find('.editable');
			
			
			var current_element_index=all_editable.index(_this);
			console.log(current_element_index);*/
			e.preventDefault();
		});
		
		
	},
	upImage: function(el, img_id, data_id, table, key){
		var parentel=$(el).parents('.preview_admin').eq(0);
		var prev=parentel.prevAll('.preview_admin');
		if(prev.length>0){
			$.ajax({
				url: estate_folder + "/js/ajax.php?action=reorder_image",
				data: 'table_name='+table+'&image_id='+img_id+'&key='+key+'&reorder=up&key_value='+data_id,
				success: function(){
					parentel.fadeOut('slow',function(){
						parentel.insertBefore(prev.eq(0)).fadeIn('slow');
					})
					
				}
			});
		}
		
		return false;
	},
	downImage: function(el, img_id, data_id, table, key){
		var parentel=$(el).parents('.preview_admin').eq(0);
		var next=parentel.nextAll('.preview_admin');
		if(next.length>0){
			$.ajax({
				url: estate_folder + "/js/ajax.php?action=reorder_image",
				data: 'table_name='+table+'&image_id='+img_id+'&key='+key+'&reorder=down&key_value='+data_id,
				success: function(){
					parentel.fadeOut('slow',function(){
						parentel.insertAfter(next.eq(0)).fadeIn('slow');
					})
				}
			});
		}
		return false;
	},
	makeMain: function(el, img_id, data_id, table, key){
		var parentel=$(el).parents('.preview_admin').eq(0);
		var gross_parent=parentel.parents().eq(0);
		$.ajax({
			url: estate_folder + "/js/ajax.php?action=make_main_image",
			data: 'table_name='+table+'&image_id='+img_id+'&key='+key+'&key_value='+data_id,
			success: function(){
				parentel.fadeOut('slow',function(){
					parentel.prependTo(gross_parent).fadeIn('slow');
					DataImagelist.markMainImage();
				});
			}
		});
		
		
		return false;
	},
	attachDblclick: function(){
		$(document).ready(function(){
			$('.preview_admin .field_tab').dblclick(function(){
				var _this=$(this);
				var content=$(this).html();
				var id=$(this).attr('alt');
				_this.html('');
				var new_content=$('<input type="text" value="'+content+'" />');
				
				new_content.blur(function(){
					var c=new_content.val();
					_this.html(c);
					$.ajax({
						url: estate_folder+'/js/ajax.php',
						type: 'POST',
						dataType: 'text',
						data: 'action=change_image_title&title='+c+'&image_id='+id,
						success: function(data) {
							_this.html(c);
						},
						error: function(){
							_this.html(content);
						}
					});
					//_this.html(c);
				});
				_this.append(new_content);
				new_content.focus();
			});
			
			$('.preview_admin .field_tab_description').dblclick(function(){
				var _this=$(this);
				var content=$(this).html();
				var id=$(this).attr('alt');
				var description_id = 'description'+id;
				var n_description_id = '#description'+id;
				_this.html('');
				var new_content=$('<textarea id="'+description_id+'">'+content+'</textarea><div id="save">Сохранить</div>');
				_this.append(new_content);
				$(n_description_id).ckeditor();
				$('#save').click(function(){
					var c= $(n_description_id).val();
					if (CKEDITOR.instances[description_id]) {
						CKEDITOR.instances[description_id].destroy();
					}
					_this.html(c);
					var datas = {
							action:'change_image_description',
							image_id:id,
							description:c
					};
					$.ajax({
						url: estate_folder+'/js/ajax.php',
						type: 'POST',
						data: datas,
						success: function(data) {
							_this.html(c);
						},
						error: function(){
							_this.html(content);
						}
					});
				});
				new_content.focus();
			});
			
		});
		
	},
	markMainImage: function(){
		$('.preview_admin').find('td > img').css({'width':'100px'});
		$('.preview_admin:first').find('td > img').css({'width':'200px'});
	}
}

$(document).ready(function(){
	DataImagelist.markMainImage();
});