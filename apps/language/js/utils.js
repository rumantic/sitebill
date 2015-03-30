$(document).ready(function(){
	$('#add_new_word').click(function(){
		var t=$('.template_row').clone().removeClass('template_row');
		var current_i=$('input#terms_counter').val();
		current_i++;
		$('#terms_counter').val(current_i);
		//t.replace('~',current_i);
		t.find('input, textarea').each(function(){
			var name=$(this).attr('name');
			//name.replace('~',current_i);
			$(this).attr('name',name.replace('~',current_i));
			//console.log(name);
		});
		$('table.dictionary').append(t);
		//console.log($('.template_row'));
		return false;
	});
	
	$('.delete_word').click(function(){
		if(confirm('Действительно хотите удалить?')){
			$(this).parents('tr').eq(0).remove();
		}
		
		return false;
	});
});  