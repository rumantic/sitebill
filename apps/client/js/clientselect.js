(function($){
	jQuery.fn.ClientSelect = function(options){
		var GDC=$(this);
		var isContractorExists = false;
	    var isPhoneExists = false;
	    var isValidateError = false;
	    var oldValue = '';
	    var minLength = 1;
	    var existingContractor = GDC.find('.existing-contractor');
	    var $searchContractorDiv = GDC.find('.contractor');
	    var $newContractorDiv = GDC.find('.new-contractor');
	    var $input = $searchContractorDiv.find('.search-contractor');
	    var $id = GDC.find('#id-contractor');
	    var $div = GDC.find('.found-contractors');
	    var $newContractorButton = GDC.find('.new-contractor-button');
	    var $newContractorButtonSave = GDC.find('.new-contractor-button-save');
	    var $searchContractorButton = GDC.find('.search-contractor-button');
	    $newContractorDiv.hide();
	    $newContractorButton.hide();
	    $searchContractorButton.hide();
	    if(options.selected_contractor==0){
	    	
	    }else{
	    	isContractorExists=true;
	    }
	    
	    //existingContractor.hide();
	    $div.hide();
	    
	    /*if(existingContractor.html!==''){
	    	isContractorExists=true;
	    }*/
	    
	    if (isContractorExists) {
	        $searchContractorDiv.hide();
	        $searchContractorButton.show();
	        existingContractor.show();
	    }
	    
		var _defaults = {};
		var minLength = 1;
		var options = $.extend(true, _defaults, options);
		
	
		
		
		$input.keyup(function (e) {
	        changeFindField(this);
	    });
		
		$div.on('click', '.choose-contractor', function (event) {
	        event.preventDefault();
	        var $clickedLink = $(this);
	        $id.val($clickedLink.data('id'));
        	var fioVal = $clickedLink.html();
	        var phoneVal = $clickedLink.parent().find('.phone').html();
	        existingContractor.html(fioVal+'<br>'+phoneVal);
        	$searchContractorDiv.hide();
	        existingContractor.show();
	        $newContractorButton.hide();
	        $searchContractorButton.show();
	    });
		
		$newContractorButtonSave.click(function (event) {
	        event.preventDefault();
	        
	        var fioVal=$newContractorDiv.find('[alt=fio]').val();
	        var phoneVal=$newContractorDiv.find('[alt=phone]').val();
	        
	        $.ajax({
	        	url: estate_folder+'/apps/client/js/ajax.php',
	        	data: {action: 'add_client', fio: fioVal, phone: phoneVal},
	        	dataType: 'json',
	        	type: 'post',
	        	success: function(json){
	        		if(json.status==1){
	        			$id.val(json.id);
	        			existingContractor.html(json.fio+'<br>'+json.phone);
	        			$searchContractorDiv.hide();
	        	        existingContractor.show();
	        	        $newContractorButton.hide();
	        	        $searchContractorButton.show();
	        	        $newContractorDiv.find('[alt=fio]').val('');
	        	        $newContractorDiv.find('[alt=phone]').val('');
	        	        $newContractorButtonSave.hide();
	        	        $newContractorDiv.hide();
	        		}
	        	}
	        });

	        console.log(fioVal);
	        console.log(phoneVal);
	        
	    });
		
		$newContractorButton.click(function (event) {
	        event.preventDefault();
	        $id.val('');
	        $searchContractorDiv.hide();
	        $newContractorDiv.show();
	        $newContractorButton.hide();
	        $searchContractorButton.show();
	        existingContractor.hide();
	        $newContractorDiv.find('[alt=phone]').val($input.val());
	    });
	    $searchContractorButton.click(function (event) {
	        event.preventDefault();
	        $searchContractorDiv.show();
	        $newContractorDiv.hide();
	        $newContractorButton.show();
	        $searchContractorButton.hide();
	        existingContractor.hide();
	    });
		
		function changeFindField(elem) {
	        var value = $(elem).val();
	        if (value.length >= minLength) {
	            oldValue = value;
	            $.get(
	                estate_folder+'/apps/client/js/ajax.php',
	                {
	                    action: 'get_client',
	                	'phone': value
	                },
	                function (retrieveData) {
	                	$div.empty();
	                    $div.append('Выберите клиента:<br/>');
	                    for(var i=0, l=retrieveData.length; i<l; i++){
	                    	$fioSpan = $('<a href="" class="">').addClass('choose-contractor').text(retrieveData[i].n).data(
	                                'id',
	                                retrieveData[i].i
	                            ),
	                            $phoneSpan = $('<span class="phone">').text(retrieveData[i].p);
	                    	 $buttons = $('<span class="pull-right">')
                             .append($('<a href="'+estate_folder+'/admin/?client_id='+retrieveData[i].i+ '" class="btn-link" target="_blank"> Объекты (' + retrieveData[i].ob + ')</a>'));
	                    	var $contractorDiv = $('<div>').append($fioSpan, $buttons, $phoneSpan);
	                        $div.append($contractorDiv);
	                    }
	                    $div.show();
	                    $newContractorButton.show();
	                    $('#phone, .phone').mask('8 (000) 000-00-00');
	                },
	                'json'
	            );
	        }
	    }
		
	};
})(jQuery);