var SitebillCore={
	getBodyScrollTop: function(){
		return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
	},
	frozenBody: function(){
		$('body').css('overflow','hidden');
	},
	defrozenBody: function(){
		$('body').css({'overflow':'auto'});
	},
	getDialogPositionCoords: function(dialog_width, dialog_height){
		var w_width=(window.innerWidth) ? window.innerWidth : ((document.all) ? document.body.offsetWidth : null); 
		var w_height=(window.innerHeight) ? window.innerHeight : ((document.all) ? document.body.offsetHeight : null);
		var scroll=this.getBodyScrollTop();
		var dialog_w=dialog_width;
		var dialog_h=dialog_height;
		var dialog_top=((w_height-dialog_h)/2);
		var dialog_left=((w_width-dialog_w)/2);
		return Array(dialog_left,dialog_top+scroll);
	},
	isValidEmail: function (email)
	{
	    return (/^([a-z0-9_-]+.)*[a-z0-9_-]+@([a-z0-9-]*[a-z0-9].)+[a-z]{2,4}$/i).test(email);
	},
	serializeFormJSON: function(el) {
		var el=el;
		var o = {};
		var a = el.serializeArray();
		$.each(a, function() {
			var name=this.name.replace('[]','');
			if (o[name]) {
				if (!o[name].push) {
					o[name] = [o[name]];
				}
				o[name].push(this.value || '');
			} else {
				o[name] = this.value || '';
			}
		});
		return o;
	},
	formsubmit: function(el){
		var _this=$(el);
		
		if(_this.data('valid_me')!==undefined && _this.data('valid_me').length>0){
			var alertwin=$('<div class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>Ошибка</h3></div><div class="modal-body"></div></div></div></div>');
			var vm=_this.data('valid_me');
			var form=_this.parents('form').eq(0);
			for(var i in vm){
				var validated_element=form.find('#'+vm[i].id);
				var validated_element_container_uploaded=validated_element.parents('.dropzone_outer').eq(0).parent().eq(0).find('ul.dz-preview-uploaded-list').eq(0);
				
			
				
				var uploaded_now=validated_element.find('.dz-preview.dz-success').length;
				var uploaded_yet=validated_element_container_uploaded.find('li').length;
				if((uploaded_now+uploaded_yet)<vm[i].count){
					$('html, body').animate({
                        scrollTop: $('#'+vm[i].id).offset().top
                    }, 2000);
					alertwin.find('.modal-body').text('Согласно правилам сайта, необходимо добавить не менее '+vm[i].count+' фотографий!!!!');
					alertwin.appendTo($('body'));
					alertwin.modal('show');
					//alert('Необходимо указать минимум '+vm[i].count+' изображений');
					return false;
				}
			}
		}
		
		
		_this.hide();
		$('<p class="loading">Сохраняю данные...</p>').insertAfter(_this).slideDown("fast");
		return true;
	},
	number_format: function( number, decimals, dec_point, thousands_sep ) {
		var i, j, kw, kd, km;

		if( isNaN(decimals = Math.abs(decimals)) ){
			decimals = 2;
		}
		if( dec_point == undefined ){
			dec_point = ",";
		}
		if( thousands_sep == undefined ){
			thousands_sep = ".";
		}

		i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

		if( (j = i.length) > 3 ){
			j = j % 3;
		} else{
			j = 0;
		}

		km = (j ? i.substr(0, j) + thousands_sep : "");
		kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
		kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");

		return km + kw + kd;
	}
};