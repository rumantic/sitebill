$(document).ready(function(){
	/*$('tr.message td.message_list_title').click(function(){
		var parent=$(this).parents('tr.message').eq(0);
		if($(this).hasClass('unreaded')){
			parent.removeClass('unreaded');
		}
		parent.next().fadeToggle('slow');
	});*/
	$(document).on('click', 'tr.message a.showmessage', function(e){
		e.preventDefault();
		var parent=$(this).parents('tr.message').eq(0);
		
		if(parent.hasClass('unreaded')){
			var id=parent.attr('alt');
			$.ajax({
				type: 'post',
				url: estate_folder+'/apps/mailbox/js/ajax.php',
				data: {action:'read_message',id:id},
				dataType: 'json',
				success: function(json){
					
				}
			});
			parent.removeClass('unreaded');
		}
		$(this).text('Свернуть').removeClass('showmessage').addClass('hidemessage');
		parent.next().fadeToggle('slow');
	});
	$(document).on('click', 'tr.message a.hidemessage', function(e){
		e.preventDefault();
		var parent=$(this).parents('tr.message').eq(0);
		
		$(this).text('Развернуть').removeClass('hidemessage').addClass('showmessage');
		parent.next().fadeToggle('slow');
	});
});  