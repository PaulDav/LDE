var reqSets = getRequest();

function getSets(){

	req = reqSets;
	
	var filtercontext = document.getElementById('filtercontext');
	var filtercontextvalue = filtercontext.options[filtercontext.selectedIndex].value;	

	var filterlicencetype = document.getElementById('filterlicencetype');
	var filterlicencetypevalue = filterlicencetype.options[filterlicencetype.selectedIndex].value;	
	
	var filterorg = document.getElementById('filterorg');
	var filterorgvalue = filterorg.options[filterorg.selectedIndex].value;	

	var filtershape = document.getElementById('filtershape');
	var filtershapevalue = filtershape.options[filtershape.selectedIndex].value;	
	
	rand = parseInt(Math.random()*999999999999999);
	url = "apiSets.php" + "?rand=" + rand;

	
	if (filtercontextvalue){
		url += "&contextid=" + filtercontextvalue;
	}

	if (filterlicencetypevalue){
		url += "&licencetypeid=" + filterlicencetypevalue;
	}
	
	if (filterorgvalue){
		url += "&orgid=" + filterorgvalue;
	}

	if (filtershapevalue){
		url += "&shapeid=" + filtershapevalue;
	}

	req.open("GET",url,true);
	req.onreadystatechange = presentSets;
	req.send(null);
	
}

function presentSets(){
	
	req = reqSets;
	
	if (req.readyState == 4){

		rand = parseInt(Math.random()*999999999999999);
		url = "xslt/sets.xslt" + "?rand=" + rand;

		xslt = loadXMLDoc(url);

		result = xsltTransform(req.responseXML,xslt);

		document.getElementById('sets').innerHTML = result;
				
	} else {
		document.getElementById('sets').innerHTML = '<img src="images/ajax-loader.gif"/>';
	}
	
}
