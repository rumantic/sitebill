$(document).on('ready', function () {
    /*$( "#myButton" ).click(function() {
        var site_slogan_edit = window.parent.document.getElementById("site_slogan_edit");
        var button = document.getElementById("myButton");
        var content_div = document.getElementById("ListContent");
        var textarea = document.getElementById("myTextarea");

        if (site_slogan_edit.contentEditable == "true")
        {
            site_slogan_edit.contentEditable = "false";
            content_div.style.display = "inline";
            textarea.innerHTML = site_slogan_edit.innerHTML;
            console.log('edit');
            button.value = "Редактировать";
            $.ajax({
                type: 'post',
                url: estate_folder + '/apps/dashboard/js/ajax.php?action=editor',
                data: 'edit_content=' + site_slogan_edit.innerHTML,
                success: function (text) {
                    console.log(text);
                }
            });
            
        } else
        {
            site_slogan_edit.contentEditable = "true";
            content_div.style.display = "none";
            button.value = "Сохранить";
        }
    });*/
	
	$('editable', window.parent.document).each(function(){
		var el=$(this);
		var id=el.attr('id');
		el.prop('contenteditable', true);
		el.focus(function(){
			el.data('prev_html', el.html());
		});
		el.blur(function(){
			if(el.data('prev_html')!=el.html()){
				console.log(el.html());
				var data={};
				data.action='editor';
				data.edit_content=el.html();
				data.elid=id;
				data.file=el.data('file');
				$.ajax({
					type: 'post',
					dataType: 'json',
					url: estate_folder + '/apps/dashboard/js/ajax.php?action=editor',
					data: data,
					success: function (json) {
						if(json.status==0){
							el.html(el.data('prev_html'))
						}
					}
				});
			}
		});
		//el.prop('contentEditable', true);
	});
});
