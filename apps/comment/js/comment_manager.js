Comment_Manager={
	object_type : '',
	run : function(obj_type){
		this.object_type=obj_type;
		var comment_panel=$('#app_comment_panel');
		var form=comment_panel.find('#app_comment_form');
		this.object_type=form.find('[name=object_type]').val();
		var _this=this;
		form.submit(function(e){
			e.preventDefault();
			_this.submitComment(this);
		});
	},
	submitComment : function(formElement){

		var form=$(formElement);
		form.find('textarea[name=text]').focus(function(){
			form.find('.errors').hide();
		});

		var submit=form.find('#submit');

		var message=$('<div>').text('Идет отправка...');
		message.insertAfter(submit);
		submit.hide();

		var object_type=this.object_type;
		var object_id=new Number($.trim(form.find('input[name=object_id]').val()));
		var user_id=new Number($.trim(form.find('input[name=user_id]').val()));
		var text=$.trim(form.find('textarea[name=text]').val());
		if(text==''){
			form.find('.errors').show();
			submit.show().next('div').remove();
			return false;
		}

		$.ajax({
			url: estate_folder+'/apps/comment/js/ajax.php',
			type: 'post',
			dataType: 'text',
			data: {action:'save_comment', object_type: object_type, object_id: object_id, user_id: user_id, comment_text: text},
			success: function(json){
				if(json=='Ok'){
					form.find('textarea[name=text]').val('');
					submit.show().next('div').remove();
					$.ajax({
						url: estate_folder+'/apps/comment/js/ajax.php',
						type: 'post',
						dataType: 'html',
						data: {action: 'get_comments', object_id: object_id, object_type: object_type},
						success: function(html){
							$('#app_comments_list .app_comments_list_container').html(html);
						}
					});
					//.load(estate_folder+'/apps/comment/js/ajax.php?action=get_comments&object_id='+object_id+'&object_type='+object_type);
				}else{
					submit.show().next('div').remove();
				}
			}
		});
	},
	refreshCommentList : function(){

	}

}
