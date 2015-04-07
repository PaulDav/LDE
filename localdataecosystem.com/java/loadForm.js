var NextFormFieldId = 0;
var domForm;
var domLinkForm;
var divForm;
var divFormXml;
var arrXmlFields;

var nsForm = 'http://schema.legsb.gov.uk/lde/form/';


function makeXMLDom(text){
		
	var xmlDocument = null;

    // Internet Explorer
    try
    {
        xmlDocument = new ActiveXObject("Microsoft.XMLDOM");
        xmlDocument.async = false;
        xmlDocument.loadXML(text);
    }
    // Standards-compliant method.
    catch (exception)
    {
        parser      = new DOMParser();
        xmlDocument = parser.parseFromString(text, "text/xml");
    }

    return xmlDocument;
	
}


function loadForm(){
		
	if (!(typeof(xmlForm) == 'undefined')){
		domForm = makeXMLDom(xmlForm);		
		loadFormFromXml('form');
	}

	if (!(typeof(xmlLinkForm) == 'undefined')){
		domLinkForm = makeXMLDom(xmlLinkForm);
		loadLinkFormFromXml('link');
	}
		
}


function loadFormFromXml(FormType){
	switch (FormType){
		case 'link':
			loadLinkFormFromXml();
			break;
		default:
			loadSubjectFormFromXml();
			break;
	}
	return;
}
	

function loadSubjectFormFromXml(){
	var FormType = 'form';
	var dom = getDomFromFormType(FormType);
	
	divForm = document.getElementById("divForm");
	divForm.innerHTML = "";

	
	xmlForm = dom.documentElement;
	
	var xmlProfile = null;
	for(var iProfile = 0;iProfile < xmlForm.childNodes.length; iProfile++) {
	    var xmlNode = xmlForm.childNodes[iProfile];
		if (xmlNode.nodeName == "Profile"){
			xmlProfile = xmlNode;
			for(var iClass = 0;iClass < xmlProfile.childNodes.length; iClass++) {
			    var xmlNode = xmlProfile.childNodes[iClass];
				if (xmlNode.nodeName == "Class"){
					xmlClass = xmlNode;
					loadClass(xmlClass, null, divForm, FormType);				
				}
			}
		}
	}
		

	divFormXml = document.createElement('div');
	setXml(FormType);	
	divForm.appendChild(divFormXml);
	
	setDatePicker();
	
}



function loadLinkFormFromXml(){
	
	var FormType = 'link';
	var dom = getDomFromFormType(FormType);
	
	divForm = document.getElementById("divLinkForm");
	divForm.innerHTML = "";

	
	
	xmlForm = dom.documentElement;

	for(var iProfile = 0;iProfile < xmlForm.childNodes.length; iProfile++) {
	    var xmlNode = xmlForm.childNodes[iProfile];
		if (xmlNode.nodeName == "Profile"){
			xmlProfile = xmlNode;
			for(var iRel = 0;iRel < xmlProfile.childNodes.length; iRel++) {
			    var xmlNode = xmlForm.childNodes[iProfile];
				if (xmlNode.nodeName == "Relationship"){
					xmlRelationship = xmlNode;

					xmlLink = null;			
					for(var iLink = 0;iLink < xmlForm.childNodes.length; iLink++) {
					    var xmlNode = xmlForm.childNodes[iLink];
						if (xmlNode.nodeName == "Link"){
							xmlLink = xmlNode;
						}
					}
			

					xmlAttributes = null;			
					for(var iAtts = 0;iAtts < xmlLink.childNodes.length; iAtts++) {
					    var xmlNode = xmlLink.childNodes[iAtts];
						if (xmlNode.nodeName == "Attributes"){
							xmlAttributes = xmlNode;
						}
					}
								
					var htmlTable = document.createElement('table');
					var attr=document.createAttribute("class");
					attr.nodeValue="sdbluebox";
					htmlTable.attributes.setNamedItem(attr);

					if (!(xmlAttributes == null)){	
						
						for(var iProps = 0;iProps < xmlRelationship.childNodes.length; iProps++) {
						    var xmlNode = xmlLink.childNodes[iProps];
							if (xmlNode.nodeName == "Properties"){
								xmlProperties = xmlNode;
								loadProperties(xmlProperties, xmlAttributes, htmlTable, FormType);								
							}
						}

						
						if (xmlRelationship.getAttribute("extending") == 'true'){
							
							var xmlClass = null
		
							
							for(var iRelClass = 0;iRelClass < xmlRelationship.childNodes.length; iRelClass++) {
							    var xmlNode = xmlRelationship.childNodes[iRelClass];
								if (xmlNode.nodeName == "Class"){
									xmlClass = xmlNode;

									var profileClassSeq = xmlClass.getAttribute("seq");
									
									var xmlSubject = null;
									for(var iSubject = 0;iSubject < xmlLink.childNodes.length; iSubject++) {
									    var xmlNode = xmlLink.childNodes[iSubject];
										if (xmlNode.nodeName == "Subject"){
											xmlSubject = xmlNode;
										}
									}

									
									if (xmlSubject == null){
										xmlSubject = dom.createElementNS(nsForm,'Subject');
										
										var attr=dom.createAttribute("seq");					
										NextSeq = getNextSubjectSeq(FormType);					
										attr.nodeValue=NextSeq;
										xmlSubject.attributes.setNamedItem(attr);
			
										var attr=dom.createAttribute("profileseq");					
										attr.nodeValue=profileClassSeq;
										xmlSubject.attributes.setNamedItem(attr);
			
										xmlLink.appendChild(xmlSubject);
																				
									}
			
									loadClass(xmlClass, xmlSubject, divForm, FormType);
								}
							}
						}
						
						divForm.appendChild(htmlTable);
		
					}
				}
			}

		}
		
	}
	
	divFormXml = document.createElement('div');
	setXml(FormType);	
	divForm.appendChild(divFormXml);
	
}


function loadClass(xmlClass, xmlSubject, htmlElement, FormType){

	var dom = getDomFromFormType(FormType);

	if (xmlSubject == null){
		for(var iSubject = 0;iSubject < dom.documentElement.childNodes.length; iSubject++) {
		    var xmlNode = dom.documentElement.childNodes[iSubject];
			if (xmlNode.nodeName == "Subject"){
				xmlSubject = xmlNode;
			}
		}
	}


	xmlAttributes = null;
	for(var iAtts = 0;iAtts < xmlSubject.childNodes.length; iAtts++) {
	    var xmlNode = xmlSubject.childNodes[iAtts];
		if (xmlNode.nodeName == "Attributes"){
			xmlAttributes = xmlNode;
		}
	}

	if (xmlAttributes == null){
		
		xmlAttributes = dom.createElementNS(nsForm,'Attributes');
		
		var attr=dom.createAttribute("seq");					
		NextSeq = getNextSubjectSeq(FormType);					
		attr.nodeValue=NextSeq;
		xmlAttributes.attributes.setNamedItem(attr);

		xmlSubject.appendChild(xmlAttributes);
		
	}

	xmlLinks = null;
	for(var iLinks = 0;iLinks < xmlSubject.childNodes.length; iLinks++) {
	    var xmlNode = xmlSubject.childNodes[iLinks];
		if (xmlNode.nodeName == "Links"){
			xmlLinks = xmlNode;
		}
	}

		
	var htmlTable = document.createElement('table');
	var attr=document.createAttribute("class");
	attr.nodeValue="sdbluebox";
	htmlTable.attributes.setNamedItem(attr);
	
	if (!(xmlAttributes == null)){
		for(var iProps = 0;iProps < xmlClass.childNodes.length; iProps++) {
		    var xmlNode = xmlClass.childNodes[iProps];
			if (xmlNode.nodeName == "Properties"){
				var xmlProperties = xmlNode;
				loadProperties(xmlProperties, xmlAttributes, htmlTable, FormType);
			}
		}
	}

	if (!(xmlLinks == null)){	
		for(var iRels = 0;iRels < xmlClass.childNodes.length; iRels++) {
			var xmlNode = xmlClass.childNodes[iRels];
			if (xmlNode.nodeName == "Relationships"){
				var xmlRelationships = xmlNode;
				loadRelationships(xmlRelationships, xmlLinks, htmlTable, FormType);
			}
		}
	}
	
	htmlElement.appendChild(htmlTable);

}


function loadProperties(xmlProperties, xmlAttributes, htmlElement, FormType){
	
	var dom = getDomFromFormType(FormType);
	
	if (xmlAttributes == null){
		
		for(var iSubject = 0;iSubject < dom.documentElement.childNodes.length; iSubject++) {
			var xmlNode = dom.documentElement.childNodes[iSubject];
			if (xmlNode.nodeName == "Subject"){
				var xmlSubject = xmlNode;
				for(var iAtts = 0;iAtts < xmlSubject.childNodes.length; iAtts++) {
					var xmlNode1 = xmlSubject.childNodes[iAtts];
					if (xmlNode1.nodeName == "Attributes"){
						xmlAttributes = xmlNodes1;
					}
				}
			}
		}
	}

	
	for(var iProp = 0;iProp < xmlProperties.childNodes.length; iProp++) {
		var xmlNode = xmlProperties.childNodes[iProp];
		if (xmlNode.nodeName == "Property"){
			var xmlProperty = xmlNode;
			loadAttributes(xmlAttributes, xmlProperty, htmlElement, FormType );
		}
	}
	
}


function loadRelationships(xmlRelationships, xmlLinks, htmlTable, FormType){

	var dom = getDomFromFormType(FormType);

	for(var iRel = 0;iRel < xmlRelationships.childNodes.length; iRel++) {
	    var xmlNode = xmlRelationships.childNodes[iRel];
		if (xmlNode.nodeName == "Relationship"){
			var xmlRelationship = xmlNode;			
			loadRelationship( xmlRelationship, xmlLinks, htmlTable, FormType);
		}
	}
}

function loadRelationship(xmlRelationship, xmlLinks, htmlTable, FormType){
	
	var dom = getDomFromFormType(FormType);
	
	var profileSeq = xmlRelationship.getAttribute("seq");
	
	var xmlLink = null;	
	for(var iLink = 0;iLink < xmlLinks.childNodes.length; iLink++) {
	    var xmlNode = xmlLinks.childNodes[iLink];
		if (xmlNode.nodeName == "Link"){
			xmlLink = xmlNode;
		}
	}

	

/*
	switch (xmlRelationship.attr("extending")){
		case 'true':
			var row = htmlTable.insertRow(-1);
			var cellLabel = document.createElement('th');
			cellLabel.innerHTML = xmlRelationship.attr("label");
			row.appendChild(cellLabel);
			
			var cellClass = document.createElement('td');
			
			xmlRelationship.children("Class").each(function(){
				xmlClass = $(this);
				var profileClassSeq = xmlClass.attr("seq");
				
				if (!(xmlLink == null)){				
					xmlLink.children("Subject").each(function(){
						loadClass( xmlClass, $(this), cellClass, FormType);
					});
				}
				else
				{
					xmlLink = dom.createElementNS(nsForm,'Link');
					
					var attr=dom.createAttribute("seq");					
					NextSeq = getNextSubjectSeq(FormType);					
					attr.nodeValue=NextSeq;
					xmlLink.attributes.setNamedItem(attr);

					var attr=dom.createAttribute("profileseq");					
					attr.nodeValue=profileSeq;
					xmlLink.attributes.setNamedItem(attr);

					xmlLinks.append(xmlLink);

					xmlObject = dom.createElementNS(nsForm,'Subject');
					
					var attr=dom.createAttribute("seq");
					NextSeq = getNextSubjectSeq(FormType);					
					attr.nodeValue=NextSeq;
					xmlObject.attributes.setNamedItem(attr);

					var attr=dom.createAttribute("profileseq");					
					attr.nodeValue=profileClassSeq;
					xmlObject.attributes.setNamedItem(attr);

					xmlLink.appendChild(xmlObject);
										
					loadClass( xmlClass, $(xmlObject), cellClass, FormType);
				}
			});
			
			row.appendChild(cellClass);

			break;
	}
	*/
	
}



function loadAttributes(xmlAttributes, xmlProperty, htmlTable, FormType){	
		
	window.onerror = function(msg, url, linenumber) {
	    alert('Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber);
	    return true;
	}
	
	
	var dom = getDomFromFormType(FormType);
	
	var profileSeq = xmlProperty.getAttribute("seq");

	var xmlAttribute = null;	
	for(var iAtt = 0;iAtt < xmlAttributes.childNodes.length; iAtt++) {
		var xmlNode = xmlAttributes.childNodes[iAtt];
		if (xmlNode.nodeName == "Attribute"){
			if (xmlNode.getAttribute("profileseq") == profileSeq ){		
				xmlAttribute = xmlNode;
			}
		}
	}

	if (xmlAttribute == null){

		xmlAttribute = dom.createElementNS(nsForm,'Attribute');
		
		var attr=dom.createAttribute("seq");					
		NextSeq = getNextSubjectSeq(FormType);					
		attr.nodeValue=NextSeq;
		xmlAttribute.attributes.setNamedItem(attr);

		var attr=dom.createAttribute("profileseq");					
		attr.nodeValue=profileSeq;
		xmlAttribute.attributes.setNamedItem(attr);
		
		xmlAttributes.appendChild(xmlAttribute);
		
	}

	
	var xmlAttribute = null;	
	for(var iAtt = 0;iAtt < xmlAttributes.childNodes.length; iAtt++) {
		
		var xmlNode = xmlAttributes.childNodes[iAtt];
		
		if (xmlNode.nodeName == "Attribute"){
						
			if (xmlNode.getAttribute("profileseq") == profileSeq ){		
				xmlAttribute = xmlNode;
		
				var seqAttribute = xmlAttribute.getAttribute('seq');
			
				var cellLabel = document.createElement('th');
				
				switch (xmlProperty.getAttribute("complex")){
					case 'true':
						
						var xmlComplexAttributes = null;
						for (iComplexAtts = 0;iComplexAtts < xmlAttribute.childNodes.length; iComplexAtts++) {
							var xmlNode = xmlAttribute.childNodes[iComplexAtts];
							if (xmlNode.nodeName == "Attributes"){
								xmlComplexAttributes = xmlNode;
							}
						}
		
						if (xmlComplexAttributes == null){
							xmlComplexAttributes = dom.createElementNS(nsForm,'Attributes');
							
							var attr=dom.createAttribute("seq");					
							NextSeq = getNextSubjectSeq(FormType);					
							attr.nodeValue=NextSeq;
							xmlComplexAttributes.attributes.setNamedItem(attr);
							
							xmlAttribute.appendChild(xmlComplexAttributes);
							
						}
		
							
						var row = htmlTable.insertRow(-1);
							
						var profileSeq = xmlProperty.getAttribute("seq");
							
						cellLabel.innerHTML = xmlProperty.getAttribute("label");
							
						cellLabel.innerHTML += '<br/>';
		
							
							
						row.appendChild(cellLabel);
			
						var cellValues = document.createElement('td');
						row.appendChild(cellValues);
							
							
						var tableComplex = document.createElement('table');
						var attr=document.createAttribute("class");
						attr.nodeValue="sdbluebox";
						tableComplex.attributes.setNamedItem(attr);
						cellValues.appendChild(tableComplex);
			
							//var xmlComplexAttributes = $(this);
						
						for (iProps = 0;iProps < xmlProperty.childNodes.length; iProps++) {
							var xmlNode = xmlProperty.childNodes[iProps];
							if (xmlNode.nodeName == "Properties"){
								xmlProperties = xmlNode;
								loadProperties(xmlProperties, xmlComplexAttributes, tableComplex, FormType);
							}
						}
								
						break;
					default:
			
						var row = htmlTable.insertRow(-1);
					
						cellLabel.innerHTML = xmlProperty.getAttribute("label");
						
						row.appendChild(cellLabel);
				
						var cellValues = document.createElement('td');
						row.appendChild(cellValues);
				
						
						var xmlValue = null;
						for (iVal = 0;iVal < xmlAttribute.childNodes.length; iVal++) {
							var xmlNode = xmlAttribute.childNodes[iVal];
							if (xmlNode.nodeName == "Value"){
								xmlValue = xmlNode;
							}
						}
						
						if (xmlValue == null){
		
							xmlValue = dom.createElementNS(nsForm,'Value');
							
							var attr=dom.createAttribute("seq");					
							NextSeq = getNextSubjectSeq(FormType);					
							attr.nodeValue=NextSeq;
							xmlValue.attributes.setNamedItem(attr);
							
							xmlAttribute.appendChild(xmlValue);
						}
		
						if (!(xmlValue == null)){
							loadValue(xmlValue, xmlProperty, cellValues, FormType);
						}
						break;				
				}
				
				
				if (xmlProperty.getAttribute("cardinality") == 'many'){
					
					var anchorDel = document.createElement('a');
					anchorDel.innerHTML = '	delete';
		
					var attr=document.createAttribute("href");
					attr.nodeValue="javascript:;";
					anchorDel.attributes.setNamedItem(attr);
					
					var attr=document.createAttribute("onclick");
					attr.nodeValue="delAttribute("+seqAttribute+",'"+FormType+"');   loadFormFromXml("+FormType+");";
		
					anchorDel.attributes.setNamedItem(attr);
		
					cellLabel.innerHTML += '<br/>';
					cellLabel.appendChild(anchorDel);
		
				}
			}
			
		}
	}

	if (xmlProperty.getAttribute("cardinality") == 'many'){
		
		var row = htmlTable.insertRow(-1);
		var cellLabel = document.createElement('td');
		
		var anchorAdd = document.createElement('a');
		anchorAdd.innerHTML = 'add';

		var attr=document.createAttribute("href");
		attr.nodeValue="javascript:;";
		anchorAdd.attributes.setNamedItem(attr);
		
		var attr=document.createAttribute("onclick");
		var AttributesSeq = xmlAttributes.getAttribute("seq");		
		var PropertySeq = xmlProperty.getAttribute("seq");
		
		attr.nodeValue="addAttribute("+AttributesSeq+", "+PropertySeq+",'"+FormType+"' ); loadFormFromXml("+FormType+");";

		anchorAdd.attributes.setNamedItem(attr);

		cellLabel.appendChild(anchorAdd);
		
		row.appendChild(cellLabel);
	}
	
}


function loadValue(xmlValue, xmlProperty, htmlCell, FormType){	

	var Value = '';
	if (!(xmlValue === null)){
		Value = xmlValue.textContent;
	}
	
	seqValue = xmlValue.getAttribute('seq');
	
	xmlProperty.getAttribute("datatype");
	
	var Field;
		
	switch (xmlProperty.getAttribute("datatype")) {
		case 'text':
			
			Field = document.createElement('textarea');
				
			var attr=document.createAttribute("rows");
			attr.nodeValue=10;
			Field.attributes.setNamedItem(attr);
	
			if (!(xmlProperty.getAttribute("length") == null)) {
				var attr=document.createAttribute("cols");
				attr.nodeValue=xmlProperty.getAttribute("length");
				Field.attributes.setNamedItem(attr);
			}
			else
			{
				var attr=document.createAttribute("cols");
				attr.nodeValue=80;
				Field.attributes.setNamedItem(attr);					
			}
			
			
		    Field.innerHTML = Value;

			htmlCell.appendChild(Field);			
		    
		    break;
		    
		case 'date':
			
			Field = document.createElement('input');
				
			var attr=document.createAttribute("type");
			attr.nodeValue='date';
			Field.attributes.setNamedItem(attr);
	
			var attr=document.createAttribute("class");
			attr.nodeValue='datepicker';
			Field.attributes.setNamedItem(attr);
			
			var attr=document.createAttribute("id");
			attr.nodeValue='value'+seqValue;
			Field.attributes.setNamedItem(attr);
	
			var attr=document.createAttribute("size");
			attr.nodeValue=10;
			Field.attributes.setNamedItem(attr);
			
			var attr=document.createAttribute("value");
			attr.nodeValue=Value;
			Field.attributes.setNamedItem(attr);
			
	
			htmlCell.appendChild(Field);			
	
			break;
	
		case 'value':
			
			Field = document.createElement('select');
				
			var option = document.createElement('option');
			Field.appendChild(option);

			
			for(var iOpts = 0;iOpts < xmlProperty.childNodes.length; iOpts++) {
			    var xmlNode = xmlProperty.childNodes[iOpts];
				if (xmlNode.nodeName == "Options"){
					var xmlOptions = xmlNode;

					for(var iOpt = 0;iOpt < xmlOptions.childNodes.length; iOpt++) {
					    var xmlNode = xmlOptions.childNodes[iOpt];
						if (xmlNode.nodeName == "Option"){
							var xmlOption = xmlNode;
					
					
							var option = document.createElement('option');
							option.innerHTML = String(xmlOption.textContent).trim();
	
							Field.appendChild(option);
							
							if ( String(xmlOption.textContent).trim() == Value ){
								var attr=document.createAttribute("selected");
								attr.nodeValue='true';
								option.attributes.setNamedItem(attr);						
							}
							htmlCell.appendChild(Field);
						}
					}					
				}
			}

			
			
			
			break;
						
		default:
			
			Field = document.createElement('input');
			
			var attr=document.createAttribute("type");
			attr.nodeValue='text';
			Field.attributes.setNamedItem(attr);
	
			var LineLength = 30;
			if (!(xmlProperty.getAttribute("length") == null)) {
				LineLength = xmlProperty.getAttribute("length");
			}
				
			var attr=document.createAttribute("size");
			attr.nodeValue=LineLength;
			Field.attributes.setNamedItem(attr);
			
	
			var attr=document.createAttribute("maxlength");
			attr.nodeValue=254;
			Field.attributes.setNamedItem(attr);
			
			var attr=document.createAttribute("value");
			attr.nodeValue=Value;
			Field.attributes.setNamedItem(attr);
	
			htmlCell.appendChild(Field);			
	
			break;	
	}
	
	
	var attr=document.createAttribute("id");
	attr.nodeValue = 'value'+seqValue;
	Field.attributes.setNamedItem(attr);

	var attr=document.createAttribute("onchange");
	attr.nodeValue = "updateValue("+seqValue+",'"+FormType+"');";
	Field.attributes.setNamedItem(attr);
	
	htmlCell.innerHTML += '<br/>';

	
}


function addAttribute(AttributesSeq, PropertySeq, FormType){
	
	window.onerror = function(msg, url, linenumber) {
	    alert('Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber);
	    return true;
	}

	var dom = getDomFromFormType(FormType);
	
	var xmlSubject = null;	
	for(var iSubject = 0;iSubject < dom.documentElement.childNodes.length; iSubject++) {
	    var xmlNode = dom.documentElement.childNodes[iSubject];
		if (xmlNode.nodeName == "Subject"){
			xmlSubject = xmlNode;
		}
	}

	var xmlProfile = null;	
	for(var iProfile = 0;iProfile < dom.documentElement.childNodes.length; iProfile++) {
	    var xmlNode = dom.documentElement.childNodes[iProfile];
		if (xmlNode.nodeName == "Profile"){
			xmlProfile = xmlNode;
		}
	}


	var xmlAttributes = null;
	for(var iAtts = 0;iAtts < 	xmlSubject.getElementsByTagName("Attributes").length; iAtts++) {
		xmlElement = xmlSubject.getElementsByTagName("Attributes")[iAtts];
		if (xmlElement.getAttribute("seq") == AttributesSeq){
			xmlAttributes = xmlElement;
		}
	}
		

	var xmlProperty = null;
	for(var iProp = 0;iProp < xmlProfile.getElementsByTagName("Property").length; iProp++) {
		xmlElement = xmlProfile.getElementsByTagName("Property")[iProp];
		if (xmlElement.getAttribute("seq") == PropertySeq){
			xmlProperty = xmlElement;
		}
	}


	switch ( xmlProperty.getAttribute('complex')){
		case 'true':
		
			var xmlAttribute = dom.createElementNS(nsForm,'Attribute');
			var attr=dom.createAttribute("seq");
			attr.nodeValue=getNextSubjectSeq(FormType);
			xmlAttribute.attributes.setNamedItem(attr);
			
			var attr=dom.createAttribute("profileseq");
			attr.nodeValue=PropertySeq;
			xmlAttribute.attributes.setNamedItem(attr);

			xmlAttributes.appendChild(xmlAttribute);
			
			var xmlComplexAttributes = dom.createElementNS(nsForm,'Attributes');
			var attr=dom.createAttribute("seq");
			var ComplexAttributesSeq = getNextSubjectSeq(FormType);
			attr.nodeValue=ComplexAttributesSeq;
			xmlComplexAttributes.attributes.setNamedItem(attr);
			xmlAttribute.appendChild(xmlComplexAttributes);

			for(var iProperties = 0;iProperties < xmlProperty.childNodes.length; iProperties++) {
			    var xmlNode = xmlProperty.childNodes[iProperties];
				if (xmlNode.nodeName == "Properties"){
					var xmlComplexProperties = xmlNode;

					for(var iComplex = 0;iComplex < xmlComplexProperties.childNodes.length; iComplex++) {
					    var xmlNode = xmlComplexProperties.childNodes[iComplex];
						if (xmlNode.nodeName == "Property"){
							var xmlComplexProperty = xmlNode;
							var ComplexPropertySeq = xmlComplexProperty.getAttribute("seq");
							addAttribute(ComplexAttributesSeq, ComplexPropertySeq, FormType);
						}
					}
				}
			}
		
			break;
		default:
			
			var xmlAttribute
		
			xmlAttribute = dom.createElementNS(nsForm,'Attribute');
			
			var attr=dom.createAttribute("seq");
			attr.nodeValue=getNextSubjectSeq(FormType);
			xmlAttribute.attributes.setNamedItem(attr);
			
			var attr=dom.createAttribute("profileseq");
			attr.nodeValue=PropertySeq;
			xmlAttribute.attributes.setNamedItem(attr);
			
			xmlAttributes.appendChild(xmlAttribute);
			
			var xmlValue;
			xmlValue = dom.createElementNS(nsForm,'Value');
			var attr=dom.createAttribute("seq");
			attr.nodeValue=getNextSubjectSeq(FormType);
			xmlValue.attributes.setNamedItem(attr);
			xmlAttribute.appendChild(xmlValue);
			
			break;
	}
		 
	return;
}


function delAttribute(AttributeSeq, FormType){
	
	window.onerror = function(msg, url, linenumber) {
	    alert('Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber);
	    return true;
	}
	
	var dom = getDomFromFormType(FormType);

	var xmlAttribute;
	for(var iAtt = 0;iAtt < dom.documentElement.getElementsByTagName("Attribute").length; iAtt++) {
		var xmlElement = dom.documentElement.getElementsByTagName("Attribute")[iAtt];
		if (xmlElement.getAttribute("seq") == AttributeSeq){
			xmlAttribute = xmlElement;
			xmlAttribute.parentNode.removeChild(xmlAttribute)
		}
	}
	
	setXml(FormType);

}


function updateValue(Seq, FormType){
	
	
	window.onerror = function(msg, url, linenumber) {
	    alert('Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber);
	    return true;
	}
	var dom = getDomFromFormType(FormType);
	
	var xmlValue = null;
	for(var iVal = 0;iVal < dom.documentElement.getElementsByTagName("Value").length; iVal++) {
		xmlElement = dom.documentElement.getElementsByTagName("Value")[iVal];
		if (xmlElement.getAttribute("seq") == Seq){
			xmlValue = xmlElement;
		}
	}


	
	var htmlField = document.getElementById("value"+Seq);			

	
	xmlValue.textContent = htmlField.value;

	setXml(FormType);
	
    return;
    
}



function setXml(FormType){
	
	var dom = getDomFromFormType(FormType);
	
	switch (FormType){
		case 'form':
			divFormXml.innerHTML = "<input type='hidden' name='xmlForm' value='" + XmlToString(dom) + "'/>";
			break;
		case 'link':
			divFormXml.innerHTML = "<input type='hidden' name='xmlLinkForm' value='" + XmlToString(dom) + "'/>";
			break;
	}
	
	return;
}



function getNextSubjectSeq(FormType){

	var dom = getDomFromFormType(FormType);
	
	var MaxSeq = 0;

	var xmlParent = null;
	
	switch (FormType){
		case 'form':
			for(var iSubject = 0;iSubject < dom.documentElement.childNodes.length; iSubject++) {
			    var xmlNode = dom.documentElement.childNodes[iSubject];
				if (xmlNode.nodeName == "Subject"){
					xmlParent = xmlNode;
				}
			}
			break;
		case 'link':
			for(var iLink = 0;iLink < dom.documentElement.childNodes.length; iLink++) {
			    var xmlNode = dom.documentElement.childNodes[iSubject];
				if (xmlNode.nodeName == "Link"){
					xmlParent = xmlNode;
				}
			}
			break;
	}


	for(var iSeq = 0;iSeq < xmlParent.getElementsByTagName("*").length; iSeq++) {
	    var xmlNode = xmlParent.getElementsByTagName("*")[iSeq];
		var Seq = Number(xmlNode.getAttribute("seq"));
		if (Seq > MaxSeq ){
			MaxSeq = Seq;
		}
	}

		
	MaxSeq = MaxSeq + 1;
	
	return MaxSeq;
		
}

function getDomFromFormType(FormType){
	
	switch ( FormType){
		case 'form':
			return domForm;
			break;
		case 'link':
			return domLinkForm;
			break;
	}
	
}


function XmlToString(xml){
	
	var xmlstr = String(xml.xml ? xml.xml : (new XMLSerializer()).serializeToString(xml));
	
	xmlstr = xmlstr.replace("'", "&apos;");

	return xmlstr;
	
}
