var reqLicences = getRequest();

function getLicences(){

	req = reqLicences;
	
/*	
	var filterorg = document.getElementById('filterorg');
	var filterorgvalue = filterorg.options[filterorg.selectedIndex].value;	

	var filtershape = document.getElementById('filtershape');
	var filtershapevalue = filtershape.options[filtershape.selectedIndex].value;	
*/	
	rand = parseInt(Math.random()*999999999999999);
	url = "apiLicences.php" + "?rand=" + rand;
/*	
	if (filterorgvalue){
		url += "&orgid=" + filterorgvalue;
	}

	if (filtershapevalue){
		url += "&shapeid=" + filtershapevalue;
	}
*/
	req.open("GET",url,true);
	req.onreadystatechange = presentLicences;
	req.send(null);
	
}

function presentLicences(){

	req = reqLicences;
	
	if (req.readyState == 4){

		rand = parseInt(Math.random()*999999999999999);
		url = "xslt/licences.xslt" + "?rand=" + rand;

		xslt = loadXMLDoc(url);

		result = xsltTransform(req.responseXML,xslt);

		document.getElementById('licences').innerHTML = result;
				
	} else {
		document.getElementById('licences').innerHTML = '<img src="images/ajax-loader.gif"/>';
	}
	
}
