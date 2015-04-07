

function getDataClasses(){

	
	var filtercontext = document.getElementById('filtercontext');
	var filtercontextvalue = filtercontext.options[filtercontext.selectedIndex].value;	

	var filterlicencetype = document.getElementById('filterlicencetype');
	var filterlicencetypevalue = filterlicencetype.options[filterlicencetype.selectedIndex].value;	
	
	var filterorg = document.getElementById('filterorg');
	var filterorgvalue = filterorg.options[filterorg.selectedIndex].value;	

	var filtershape = document.getElementById('filtershape');
	var filtershapevalue = filtershape.options[filtershape.selectedIndex].value;	
	
	rand = parseInt(Math.random()*999999999999999);
	url = "apiDataClasses.php" + "?rand=" + rand;

	
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

	
	if (DivId in AjaxRequests){
		AjaxRequests[DivId].abort();
		AjaxRequests[DivId] = null;		
	}	
	var req = getRequest();

	var DivId = "dataclasses";
	
	AjaxRequests[DivId] = req;

	
	
	req.open("GET",url,true);
	
	req.onreadystatechange = function(){
		
		if (req.readyState == 4){
			
			rand = parseInt(Math.random()*999999999999999);
			url = "xslt/dataclasses.xslt" + "?rand=" + rand;

			xslt = loadXMLDoc(url);

			result = xsltTransform(req.responseXML,xslt);
			
			document.getElementById(DivId).innerHTML = result;
		}
	}

			
	req.send(null);
	
}

function presentDataClasses(){

	req = reqDataClasses;
	
	if (req.readyState == 4){

		rand = parseInt(Math.random()*999999999999999);
		url = "xslt/dataclasses.xslt" + "?rand=" + rand;

		xslt = loadXMLDoc(url);

		result = xsltTransform(req.responseXML,xslt);

		document.getElementById('dataclasses').innerHTML = result;
				
	} else {
		document.getElementById('dataclasses').innerHTML = '<img src="images/ajax-loader.gif"/>';
	}
	
}