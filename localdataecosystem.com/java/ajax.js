 var AjaxRequests = Array();

function loadXMLDoc(filename){
	
	if (window.ActiveXObject) {
		xhttp = new ActiveXObject("Msxml2.XMLHTTP");
	}
	else {
		xhttp = new XMLHttpRequest();
	}
	
	xhttp.open("GET", filename, false);
	
	try {xhttp.responseType = "msxml-document"} catch(err) {} // Helping IE11
	
	xhttp.send("");
	return xhttp.responseXML;
}


function makeXsltProc(xslDoc){
	
	if (window.ActiveXObject) {
		var xslt = new ActiveXObject("Msxml2.XSLTemplate.6.0");
		var xslProc;

		xslt.stylesheet = xslDoc;
	    xslproc = xslt.createProcessor();
		return xslProc;

	}
	else {
		xslProc=new XSLTProcessor();
		xslProc.importStylesheet(xslDoc);
		return xslProc;
	}
		
	return;
}

function setXslParam(xslProc,Name,Value){

	if (window.ActiveXObject) {
		xslProc.addParameter(Name, Value);
	}
	else
	{
		xslProc.setParameter(null,Name,Value);
	}
		
}


function transformXml(xslProc, xmlDoc){

	if (window.ActiveXObject) {
		  xslProc.input = xmlDoc;
	      xslProc.transform();
	      return xslProc.output;		
	}
	else
	{
		  result = xslProc.transformToFragment(xmlDoc,document);
		  return XmlToString(result);

	}
		
}





function getRequest(){
	
    var req = false;

    try {
        req = new XMLHttpRequest();
    }
    catch(err) {	
    	try {
            req = new ActiveXObject("Msxml2.XMLHTTP");
    	}
    	catch (err) {
    		try {
    			req = new ActiveXObject("Microsoft.XMLHTTP");
    		}
    		catch (err) {
    			req = false;
    		}
    	}
    }
    	
	return req;
}


function xsltTransform(xml,xslt){

	var proc;
	var result;
	
	if (window.ActiveXObject) {
	 result = new ActiveXObject("MSXML2.DOMDocument");
	 result = xml.transformNode(xslt);	

	} else {
	 proc = new XSLTProcessor();
	 proc.importStylesheet(xslt);
//	 result = proc.transformToDocument(xml);
	 	 
	 result = proc.transformToFragment(xml, document);
	}
	
	return XmlToString(result);
		
}


function setTBodyInnerHTML(tbody, html) {
	var temp = tbody.ownerDocument.createElement('div');
	temp.innerHTML = '<table>' + html + '</table>';

	tbody.parentNode.replaceChild(temp.firstChild.firstChild, tbody);
}

function XmlToString(xml){
	var xmlstr = xml.xml ? xml.xml : (new XMLSerializer()).serializeToString(xml);
	
	return xmlstr;
	
}
