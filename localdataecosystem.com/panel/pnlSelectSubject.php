<?php


function pnlSelectSubject($objClass, $Selection = 'this', $FieldName='subjectid'){
	
	global $AjaxDivNum;
	global $InitScript;
	global $SetId;
		
	$AjaxDivNum = $AjaxDivNum + 1;
	
	$Content = "";
	
	$Content .= "\n";
	
	$DictId = $objClass->DictId;
	$ClassId = $objClass->Id;

	$Content .= "<h2>".$objClass->Heading."</h2>";

	$DivId = "classsubjects$AjaxDivNum";
//	$Content .=	"<span id='count$DivId'></span>";
	
	$Params = "";
	switch ($Selection){
		case "this":
			if (!empty($Params)){
				$Params .= " , ";
			}
			$Params .= "SetId : '$SetId'";
			break;
		case "reference":
			if (!empty($Params)){
				$Params .= " , ";
			}
			$Params .= "Context : '$Selection'";
			break;
	}	

	if (!empty($Params)){
		$Params .= " , ";
	}
	$Params .= "FieldName : '$FieldName'";
	
	
	$Params = "{ $Params }";
	
	$UrlParams=array("$FieldName"=>null);
	$ReturnUrl = UpdateUrl($UrlParams);
	
	$AjaxCall = "getClassSubjects('$DictId', '$ClassId', $Params, '$ReturnUrl', '$DivId');";
	$InitScript .= $AjaxCall;
	
	$AjaxCall = str_replace("'","&apos;",$AjaxCall);
							
	$Content .= "<h3>Filters</h3>";
	$Content .= "\n";
	
	$Content .= pnlClassFilters( $DictId, $ClassId, $DivId."_filter");
	$Content .= "\n";
	$Content .= "<input type='submit' value='Apply'  onClick='$AjaxCall'/>";
	
	$Content .= "\n";						
	$Content .= "<input type='submit' value='Clear'  onClick='clearFilters(&quot;$DivId&quot;); $AjaxCall'/>";					
	$Content .= "\n";
	
	
	$Content .= "<div class='sdgreybox' id='$DivId'>";
	$Content .= "</div>";
	
	return $Content;
	
}