$(document).ready(function() 
	    {
	
	$('#apply_changes').click(function(){
		var form=$(this).parents('form').eq(0);
		$.ajax({
			url: estate_folder+'/js/ajax.php',
			data: '_action=save_changes&'+form.serialize(),
			type: 'post',
			success: function(){
				
			}
		});
		return false;
	});
	
	/*	$("table").tablesorter({ 
			// pass the headers argument and assing a object 
			headers: { 
            	// assign the secound column (we start counting zero) 
            	0: { 
                	// disable it by setting the property sorter to false 
                	sorter: false 
            	},
            	1: { 
                	// disable it by setting the property sorter to false 
                	sorter: false 
            	},
            	6: {
            		sorter: 'text'
            	},
            	7: {
            		sorter: 'text'
            	},
            	9: {
            		sorter: 'text'
            	},
            	// assign the third column (we start counting zero) 
            	10: { 
            		// disable it by setting the property sorter to false 
            		sorter: false 
            	} 
        	} 
		}); 	*/
	    $("#anim").change(function() { 
	        $('#date').datepicker('option', {showAnim: $(this).val()});
	    });
	    
	    $('div#tabs a').click(function(){
	    	
	    	var tid=$(this).attr('alt');
	    	$('div.config_tab_block').hide();
	    	$('div#tab'+tid).show();
	    });
	    
	    $(function(){
	    	  $("#config_tabs").tabs();
	    });
	    
	    $(function(){
	    	  $("#tabs-left").tabs();
	    });
	    
	    $(document).on('click','a.show_contact',function(){
    	//$('a.show_contact').on('click',function(){
    		var id=$(this).attr('alt');
    		var this_el=$(this);
    		$.getJSON(estate_folder+'/js/ajax.php?action=show_contact&id='+id,{},function(data){
    			if(data.response.body!=''){
    				alert('Состояние контактов изменено на Открытые');
    				this_el.attr('class','hide_contact');
    				this_el.children('img').attr('alt','Скрыть контакты').attr('title','Скрыть контакты');
    			}
    		});
    	});
	    
	    $(document).on('click','a.hide_contact',function(){
    	//$('a.hide_contact').on('click',function(){
    		var id=$(this).attr('alt');
    		var this_el=$(this);
    		$.getJSON(estate_folder+'/js/ajax.php?action=hide_contact&id='+id,{},function(data){
    			if(data.response.body!=''){
    				alert('Состояние контактов изменено на Скрытые');
    				this_el.attr('class','show_contact');
    				this_el.children('img').attr('alt','Открыть контакты').attr('title','Открыть контакты');
    			}
    		});
    	});
});
