function validateDate(inDate){

    var date = inDate.value;
    var pattern =/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/;
    
    if (date == null || date == ""){
    	return true;
    }
    if (!pattern.test(date)){
    	alert ('invalid date format');
    	inDate.focus();
    	return false;
    }
}

function clearFilters(DivId){
	
	DivId = (DivId === undefined) ? '' : DivId;


	var all = document.getElementsByTagName("*");
	for (var i=0, max=all.length; i < max; i++) {
		var boolFilter = false;
		var FilterName = '';
		if (all[i].id.substring(0, 7) == "filter_"){
			boolFilter = true;
			FilterName = all[i].id;
		}
		var DivFilter = DivId+'_filter_';
		if (all[i].id.substring(0, DivFilter.length) === DivFilter){
			boolFilter = true;
			FilterName = all[i].id.replace(DivFilter+'_','');
		}
		
		if (boolFilter === true){
			all[i].value = '';
		}
	}

}