var streetlist = [];
function init_streetlist()
{
	streetlist = [];
	$("form select[name=street_id]").children().eq(0).nextAll().each(function(){
		streetlist.push($(this).html());
	});
	
	$(document).ready(function(){
	
	$("#istreet").autocompleteArray(streetlist,
		{
			delay:10,
			minChars:1,
			matchSubset:1,
			autoFill:true,
			maxItemsToShow:10
		}
	);

	});

}

function upd_streetlist()
{
//alert('dgs');
}