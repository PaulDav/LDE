
function getClassSubjects(DictId, ClassId, oArgs, returnUrl, DivId){

	var FieldName = 'subjectid';
	var ShapeId = null;
	var SetId = null;
	var Context = null;
	
	oArgs = (oArgs === undefined) ? null : oArgs;

	if (oArgs !== null){
		FieldName = 'FieldName' in oArgs? oArgs.FieldName : null;
		ShapeId = 'ShapeId' in oArgs? oArgs.ShapeId : null;
		SetId = 'SetId' in oArgs? oArgs.SetId : null;
		Context = 'Context' in oArgs? oArgs.Context : null;
	}

	returnUrl = (returnUrl === undefined) ? null : returnUrl;
	DivId = (DivId === undefined) ? 'classsubjects' : DivId;

	if (DivId in AjaxRequests){
		AjaxRequests[DivId].abort();
		AjaxRequests[DivId] = null;		
	}
	
	var req = getRequest();
	
	AjaxRequests[DivId] = req;
	
	document.getElementById(DivId).innerHTML = '<img src="images/ajax-loader.gif"/>';
	
	if (document.getElementById('count'+DivId) !== null){
		document.getElementById('count'+DivId).innerHTML = '<img src="images/ajax-loader.gif"/>';
	}
		
	rand = parseInt(Math.random()*999999999999999);
	url = "apiSubjects.php" + "?rand=" + rand;
	
	url += "&divid=" + DivId;
	
	url += "&dictid=" + DictId;
	url += "&classid=" + ClassId;
	
	if (ShapeId !== null){
		url += "&shapeid=" + ShapeId;
	}
	if (SetId !== null){
		url += "&setid=" + SetId;
	}

	if (Context !== null){
		url += "&context=" + Context;
	}
	
	var all = document.getElementsByTagName("*");
	for (var i=0, max=all.length; i < max; i++) {
		
		if (all[i].value){
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
				url += "&"+FilterName+"="+all[i].value;			
			}
		}
	}
	
	req.open("GET",url,true);
	req.onreadystatechange = function(){
		
		if (req.readyState == 4){

			rand = parseInt(Math.random()*999999999999999);
			url = "xslt/classsubjects.xslt" + "?rand=" + rand;
			xsltDoc = loadXMLDoc(url);
			
			xslProc = makeXsltProc(xsltDoc);
			
			if (FieldName !== null){
				setXslParam(xslProc,"FieldName",FieldName);
			}			
			
			if (returnUrl !== null){
				setXslParam(xslProc,"returnUrl",returnUrl);
			}
			if (ShapeId !== null){
				setXslParam(xslProc,"ShapeId",ShapeId);
			}

			
			
			result = transformXml(xslProc, req.responseXML);
				
			document.getElementById(DivId).innerHTML = result;

			if (document.getElementById('count'+DivId) !== null){
				document.getElementById('count'+DivId).innerHTML = '';
			}

			
		}	
		
	}
		
	req.send(null);
	
}
