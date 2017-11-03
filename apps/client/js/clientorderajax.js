ClientOrder={
	init_form: function(container_id, model, options, custom_template = false){
		var options = options || {/*horizontal: false*/};
		
		var container=$('#'+container_id);
		if(container.length==0){
			return;
		}
		var model=model;
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			data: {action: 'get_order_form', model: model, options: options, _app: 'client', custom_template: custom_template},
			dataType: 'html',
			type: 'post',
			success: function(html){
				container.html(html);
				
				var form=container.find('form');
				form.removeClass('form-horizontal').addClass('form-inline');
				/*if(options.horizontal){
					form.removeClass('form-inline').addClass('form-horizontal');
				}*/
								
				var errorb=$('<p class="error"></p>');
				errorb.hide();
				form.prepend(errorb);
				form.submit(function(e){
					ClientOrder.save_form($(this), model, options.redirect_after);
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
		data._app='client';			
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			data: data,
			dataType: 'json',
			type: 'post',
			success: function(json){
				if(json.status=='ok'){
					form.replaceWith($('<div>'+json.message+'</div>'));
					if(typeof redirect_after !== 'undefined'){
						window.location.replace(redirect_after);
					}
				}else{
					errorb.html(json.message).show();
				}
			}
		});
	},
	init_prepared_form: function(container_id, model, options){
		var options = options || {};
		var container=$('#'+container_id);
		if(container.length==0){
			return;
		}
		var model=model;
		var form=container.find('form');
		var errorb=form.find('.error');
		if(errorb.length==0){
			var errorb=$('<p class="error"></p>');
			form.prepend(errorb);
		}
		if(form.find('.captcha_placeholder')){
			var data={};
			data.action='build_captcha';
			$.ajax({
				url: estate_folder+'/js/ajax.php',
				data: data,
				dataType: 'html',
				type: 'post',
				success: function(html){
					form.find('.captcha_placeholder').replaceWith(html);
				}
			});
		}
		errorb.hide();
		
		form.submit(function(e){
			ClientOrder.save_form($(this), model, options.redirect_after);
			e.preventDefault();
		});
	}
};