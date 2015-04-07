var reqSet = getRequest();

function getSet(SetId){
	
	req = reqSet;
	
	document.getElementById('setdocuments').innerHTML = '<img src="images/ajax-loader.gif"/>';
	document.getElementById('setdocumentscount').innerHTML = '<img src="images/ajax-loader.gif"/>';

	document.getElementById('setstatements').innerHTML = '<img src="images/ajax-loader.gif"/>';
	document.getElementById('setstatementscount').innerHTML = '<img src="images/ajax-loader.gif"/>';
	
	rand = parseInt(Math.random()*999999999999999);
	url = "apiSet.php" + "?rand=" + rand;

	url += "&setid=" + SetId;

	req.open("GET",url,true);
	req.onreadystatechange = presentSet;
	req.send(null);
	
}

function presentSet(){

	req = reqSet;
	
	if (req.readyState == 4){

		rand = parseInt(Math.random()*999999999999999);
		url = "xslt/documents.xslt" + "?rand=" + rand;
		xslt = loadXMLDoc(url);
		result = xsltTransform(req.responseXML,xslt);
		document.getElementById('setdocuments').innerHTML = result;
		
		var nodes = req.responseXML.getElementsByTagName('Document');
	    var countNodes = nodes.length;
		document.getElementById('setdocumentscount').innerHTML = '('+countNodes+')';

		
		rand = parseInt(Math.random()*999999999999999);
		url = "xslt/statements.xslt" + "?rand=" + rand;
		xslt = loadXMLDoc(url);
		result = xsltTransform(req.responseXML,xslt);
		document.getElementById('setstatements').innerHTML = result;
		
		var nodes = req.responseXML.getElementsByTagName('Statement');
	    var countNodes = nodes.length;
		document.getElementById('setstatementscount').innerHTML = '('+countNodes+')';	
		
	}	
}
