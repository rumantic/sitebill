ClientOrder={
	init_form: function(container_id, model, options){
		var options = options || {};
		
		var container=$('#'+container_id);
		if(container.length==0){
			return;
		}
		var model=model;
		$.ajax({
			url: estate_folder+'/apps/client/js/ajax.php',
			data: {action: 'get_order_form', model: model, options: options},
			dataType: 'html',
			type: 'post',
			success: function(html){
				container.html(html);
				var form=container.find('form');
				form.removeClass('form-horizontal').addClass('form-inline');
				
				var errorb=$('<p class="error"></p>');
				errorb.hide();
				form.prepend(errorb);
				form.submit(function(e){
					ClientOrder.save_form($(this), model);
					e.preventDefault();
					
				});
			}
		});
	},
	save_form: function(form, model){
		var model=model;
		var errorb=form.find('.error');
		errorb.hide();
		var data=SitebillCore.serializeFormJSON(form);
		data.action='save_order_form';
		data.model=model;
		$.ajax({
			url: estate_folder+'/apps/client/js/ajax.php',
			data: data,
			dataType: 'json',
			type: 'post',
			success: function(json){
				if(json.status=='ok'){
					form.replaceWith($('<div>'+json.message+'</div>'));
				}else{
					errorb.html(json.message).show();
				}
			}
		});
	}
};