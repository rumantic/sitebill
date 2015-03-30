(function( $ ) {
    $.widget( "my.mycombobox", {
    	options : {
    		ajax_options: []
    	},
        _create: function() {
        	//console.log(this.options.ajax_options);
            var self = this,
                select = this.element.hide(),
                selected = select.children( ":selected" ),
                value = selected.val() && selected.val()!=0 ? selected.text() : "";
                if(value=="" && select.children( "option" ).eq(1)!=undefined){
                	value = select.children( "option" ).eq(1).text();
                }
                
            var input = this.input = $( "<input>" )
                .insertAfter( select )
                .val( value )
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: function( request, response ) {
                        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
                        
                       
                    	var _options=select.children( "option" );
                        
                         var _sorted_options=[];
                         _options.each(function(){
                         	
                         	if ( this.value && this.value!=0){
                         		
                         		
                         		var text = $(this).text();
                         		
                             	var p=text.toLowerCase().indexOf(request.term.toLowerCase());
                             	
                             	if(p==0){
                             		_sorted_options.unshift({
                             			label: text.replace(
                                                 new RegExp(
                                                     "(?![^&;]+;)(?!<[^<>]*)(" +
                                                     $.ui.autocomplete.escapeRegex(request.term) +
                                                     ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                                 ), "<strong>$1</strong>" ),
                                             value: text,
                                             option: this
                             		});
                             	}else if(p!=-1){
                             		_sorted_options.push({
                             			label: text.replace(
                                                 new RegExp(
                                                     "(?![^&;]+;)(?!<[^<>]*)(" +
                                                     $.ui.autocomplete.escapeRegex(request.term) +
                                                     ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                                 ), "<strong>$1</strong>" ),
                                             value: text,
                                             option: this
                             		});
                             	}
                         	}
                         });
                        
                        
                        
                        response(_sorted_options);
                        
                        /*
                        response( select.children( "option" ).map(function() {
                            var text = $( this ).text();
                            
                            if ( this.value && this.value!=0 && ( !request.term || matcher.test(text) ) ){
                            	console.log({
                                    label: text.replace(
                                            new RegExp(
                                                "(?![^&;]+;)(?!<[^<>]*)(" +
                                                $.ui.autocomplete.escapeRegex(request.term) +
                                                ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                            ), "<strong>$1</strong>" ),
                                        value: text,
                                        option: this
                                    });
                                return {
                                    label: text.replace(
                                        new RegExp(
                                            "(?![^&;]+;)(?!<[^<>]*)(" +
                                            $.ui.autocomplete.escapeRegex(request.term) +
                                            ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                        ), "<strong>$1</strong>" ),
                                    value: text,
                                    option: this
                                };}
                        }) );*/
                    },
                    select: function( event, ui ) {
                    	self.newValue.val('');
                        ui.item.option.selected = true;
                        
                        for(var i in self.options.ajax_options){
                        	for(var j=0; j<self.options.ajax_options[i].length; j++){
                    			window[i](self.options.ajax_options[i][j], select);
                    		}
                        	
                        	/*
                        	if(i==='update'){
                        		for(var j=0; j<self.options.ajax_options[i].length; j++){
                        			update ( self.options.ajax_options[i][j], select  );
                        		}
                        		
                        	}else if(i==='set_empty'){
                        		for(var j=0; j<self.options.ajax_options[i].length; j++){
                        			//update ( self.options.ajax_options[i][j], select  );
                        			_empty( self.options.ajax_options[i][j], select );
                        		}
                        		
                        	}*/
                        }
                        //reload(select);
                        
                        self._trigger( "selected", event, {
                            item: ui.item.option
                        });
                        
                    },
                    change: function( event, ui ) {
                    	
                        if ( !ui.item ) {
                            var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
                                valid = false;
                            select.children( "option" ).each(function() {
                                if ( $( this ).text().match( matcher ) ) {
                                    this.selected = valid = true;
                                    return false;
                                }
                            });
                            if ( !valid && $(this).val()!='') {
                                // remove invalid value, as it didn't match anything
                            	var new_z=$( this ).val();
                            	self.newValue.val(new_z);
                            	self.newValue.show().blur();
                            	self.hideSelect();
                                $( this ).val( "" );
                                select.val( "" );
                                input.data( "ui-autocomplete" ).term = "";
                                return false;
                            }else{
                            	var new_z=$( this ).val();
                            	self.newValue.val(new_z);
                            	//self.newValue.show().blur();
                            	//self.hideSelect();
                                $( this ).val( "" );
                                select.val( "" );
                                input.data( "ui-autocomplete" ).term = "";
                                return false;
                            }
                        }
                    }
                })
                .addClass( "ui-widget ui-widget-content ui-corner-left" ).on('focus',function(){$(this).val('');});
            
            

            input.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                return $( "<li></li>" )
                    .data( "item.autocomplete", item )
                    .append( "<a>" + item.label + "</a>" )
                    .appendTo( ul );
            };

           this.button = $( "<button type='button'>&nbsp;</button>" )
                .attr( "tabIndex", -1 )
                .attr( "title", "Show All Items" )
                .insertAfter( input )
                .button({
                    icons: {
                        primary: "ui-icon-triangle-1-s"
                    },
                    text: false
                })
                .removeClass( "ui-corner-all" )
                .addClass( "ui-corner-right ui-button-icon" )
                .click(function() {
                    // close if already visible
                    if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
                        input.autocomplete( "close" );
                        return;
                    }

                    // work around a bug (likely same cause as #5265)
                    $( this ).blur();

                    // pass empty string as value to search for, displaying all results
                    input.autocomplete( "search", "" );
                    input.focus();
                });
           
           this.newValue = $( "<input>" ).attr('name','_new_value['+select.attr('id')+']').insertBefore( select ).keyup(function(){
        	   if($(this).val()==''){
        		   self.showSelect();
        	   }
        	   
           })/*.on('focus',function(){$(this).val('');})*/.hide();
        },
        
        hideSelect: function(){
        	this.input.hide();
        	this.button.hide();
        },
        showSelect: function(){
        	this.newValue.hide();
        	this.input.show().focus();
        	this.button.show();
        },

        destroy: function() {
            this.input.remove();
            this.button.remove();
            this.element.show();
            $.Widget.prototype.destroy.call( this );
        }
    });
    
    
})( jQuery );
/*
function reload(context){
	if(context===null){
		return;
	}
	
	var _i=context.attr('id');
	var parent=$(context).parents('form').eq(0);
	
	
	
	
	if(_i=='country_id'){
		_empty('region_id', context);
		_empty('city_id', context);
		_empty('district_id', context);
		_empty('street_id', context);
		_reload('region_id', context, 'regions');
		_reload('city_id', context, 'cities');
		_reload('district_id', context, 'districts');
		_reload('street_id', context, 'streets');
	}else if(_i=='region_id'){
		_empty('city_id', context);
		_empty('district_id', context);
		_empty('street_id', context);
		_reload('city_id', context, 'cities');
		_reload('district_id', context, 'districts');
		_reload('street_id', context, 'streets');
	}else if(_i=='city_id'){
		_empty('district_id', context);
		_empty('street_id', context);
		_reload('district_id', context, 'districts');
		_reload('street_id', context, 'streets');
	}else if(_i=='district_id'){
		_empty('street_id', context);
		_reload('street_id', context, 'streets');
	}
	
	
}*/

function empty(id, context){
	var parent=$(context).parents('form').eq(0);
	parent.find('[id='+ id +']').html('');
	parent.find('[id='+ id+']').next('input').val('');
}


/*function _reload(id, context, what){
	if(context===undefined || context===null){
		return;
	}
	
	var parent=$(context).parents('form').eq(0);
	
	
	
	var country_id = parent.find('#country_id').val();
	var region_id = parent.find('#region_id').val();
	var city_id = parent.find('#city_id').val();
	var district_id = parent.find('#district_id').val();
	var metro_id = parent.find('#metro_id').val();
	var street_id = parent.find('#street_id').val();

	var url = estate_folder+'/apps/booking/js/ajax.php?action=get_data&what='+ what +'&country_id='+ country_id  +'&region_id='+ region_id +'&city_id='+ city_id +'&district_id='+ district_id +'&metro_id='+ metro_id +'&street_id='+ street_id + '&callback=?';

	//console.log(url);
	
	jQuery.ajax({
		url: url, 
		dataType: 'html', 
		type: "get",
		timeout: 2000,
		success: function(json){
			parent.find('[id='+ id +']').html(json); 
		},
		error: function(){alert("error");}
	});
	parent.find('[id='+ id+']').next('input').val('');
}*/

function update(what_to_update, context){
	if(context===undefined || context===null){
		return;
	}
	
	//var _i=context.attr('id');
	
	if(what_to_update=='region_id'){
		var what='regions';
	}else if(what_to_update=='city_id'){
		var what='cities';
	}else if(what_to_update=='district_id'){
		var what='districts';
	}else if(what_to_update=='street_id'){
		var what='streets';
	}else{
		var what='';
	}
	
	if(what==''){
		return;
	}
	
	var parent=$(context).parents('form').eq(0);
	
	//parent.find('[id='+ id +'_div]').html('<div id="select_box_loading"></div>');
	
	var country_id = parent.find('#country_id').val();
	var region_id = parent.find('#region_id').val();
	var city_id = parent.find('#city_id').val();
	var district_id = parent.find('#district_id').val();
	var metro_id = parent.find('#metro_id').val();
	var street_id = parent.find('#street_id').val();

	var url = estate_folder+'/apps/booking/js/ajax.php?action=get_data&what='+ what +'&country_id='+ country_id  +'&region_id='+ region_id +'&city_id='+ city_id +'&district_id='+ district_id +'&metro_id='+ metro_id +'&street_id='+ street_id + '&callback=?';

	//console.log(url);
	
	jQuery.ajax({
		url: url, 
		dataType: 'html', 
		type: "get",
		timeout: 2000,
		success: function(json){
			parent.find('[id='+ what_to_update +']').html(json); 
		},
		error: function(){alert("error");}
	});
	parent.find('[id='+ what_to_update+']').next('input').val('');
}