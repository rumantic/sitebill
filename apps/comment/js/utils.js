$(document).ready(function(){
	$('tr.message').click(function(){
		
		if($(this).hasClass('unreaded')){
			var id=$(this).attr('alt');
			$.ajax({
				type: 'post',
				url: estate_folder+'/apps/mailbox/js/ajax.php',
				data: {action:'read_message',id:id},
				dataType: 'json',
				success: function(json){
					
				}
			});
			$(this).removeClass('unreaded');
		}
		
		$(this).next().fadeToggle('slow');
	});
});  