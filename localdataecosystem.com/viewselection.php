<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlView.php");	
	require_once("panel/pnlGroup.php");
	require_once("panel/pnlClass.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsView.php");
	
	require_once("form/frmSelectClass.php");
	
	
	define('PAGE_NAME', 'viewselection');

	session_start();
		
	$System = new clsSystem();
	
		
	$Page = new clsPage();
	
	if ($js = file_get_contents('java/jquery.js')){
		$Page->Script .= $js;
	}
		

	try {

		$Dicts = new clsDicts();
				
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
				
		$ViewId = null;
		$SelSeq = null;
		$GroupId = null;
		
		$Action = null;
		$ClassDictId = null;
		$ClassId = null;
		
		if (isset($_REQUEST['viewid'])){
			$ViewId = $_REQUEST['viewid'];
		}

		if (isset($_REQUEST['selseq'])){
			$SelSeq = $_REQUEST['selseq'];
		}
		
		
		if (isset($_REQUEST['action'])){
			$Action = $_REQUEST['action'];
		}

		if (isset($_REQUEST['classdictid'])){
			$ClassDictId = $_REQUEST['classdictid'];
		}
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
		}
		
		
		if (is_null($ViewId)) {
			throw new exception("ViewId not specified");
		}
		if (is_null($SelSeq)) {
			throw new exception("Selection Seq not specified");
		}
		
		$objView = new clsView($ViewId);
		if (!isset($objView->Selections[$SelSeq])){
			throw new Exception("Unknown Selection");
		}
		
		$objSel = $objView->Selections[$SelSeq];
		
		$GroupId = $objView->GroupId;


		if ($System->Session->Error){			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		
		$selFields = null;
		
		$usePrevFields = false;
		$PrevFields = getuserinput(PAGE_NAME);
		if (isset($PrevFields['viewid']) && isset($PrevFields['selseq'])){
			if (($PrevFields['viewid'] == $ViewId) && ($PrevFields['selseq'] == $SelSeq)){
				$usePrevFields = true;
			}			
		}
		if ($usePrevFields){
			$selFields = $PrevFields;
		}
		else
		{
			$selFields = MakeFields($objSel);
		}
		
		
		$Group = new clsGroup($GroupId);
		if ($Group->canView === false){
			throw new exception("You cannot view this Group");
		}
		
		$Page->Title = $Mode." view selection";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'edit':
				if ($objView->canEdit){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}

		$PanelB .= pnlView($ViewId);
		
		switch ($Mode){
			case 'edit':
				
				$PanelB .= '<form method="post" action="doViewSelection.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='viewid' value='$ViewId'/>";
				$PanelB .= "<input type='hidden' name='selseq' value='$SelSeq'/>";
				
				$PanelB .= "<div class='sdbluebox'>";				
				$PanelB .= frmViewSelection($selFields);
				$PanelB .= "<input type='submit' value='Update Selection'/>";
				$PanelB .= "</div>";
				
								
				$PanelB .= '</form>';

				break;
				
		}
		
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();
		
function frmViewSelection($Fields, $FieldNamePrefix = ""){

	global $System;
	global $Dicts;
	
	$Content = '';

	$ClassDictId = null;
	$ClassId = null;
	$objClass = null;
	
	$RelDictId = null;
	$RelId = null;
	$objRel = null;

	
	if (isset($Fields[$FieldNamePrefix.'classdictid'])){
		$ClassDictId = $Fields[$FieldNamePrefix.'classdictid'];		
	}
	if (isset($Fields[$FieldNamePrefix.'classid'])){
		$ClassId = $Fields[$FieldNamePrefix.'classid'];		
	}

	
	if (isset($Fields[$FieldNamePrefix.'reldictid'])){
		$RelDictId = $Fields[$FieldNamePrefix.'reldictid'];		
	}
	if (isset($Fields[$FieldNamePrefix.'relid'])){
		$RelId = $Fields[$FieldNamePrefix.'relid'];
	}
	
	
	if (is_null($ClassDictId)){
		return;
	}
	if (is_null($ClassId)){
		return;
	}

	if (!is_null($RelDictId)){
		if (!is_null($RelId)){
			$objRel = $Dicts->Dictionaries[$RelDictId]->Relationships[$RelId];			
			$Content .= "<input type='hidden' name='".$FieldNamePrefix."reldictid' value='$objRel->DictId'/>";
			$Content .= "<input type='hidden' name='".$FieldNamePrefix."relid' value='$objRel->Id'/>";
			
			$Content .= "<table>";
			$Content .= "<tr><th>Relationship</th><td>".$objRel->Label."</td></tr>";	
			$Content .= "</table>";
			
			$Content .= "<div class='tab'>";
			
		}
	}
	
	
	
	$objClass = $Dicts->Dictionaries[$ClassDictId]->Classes[$ClassId];

	$Content .= "<input type='hidden' name='".$FieldNamePrefix."classdictid' value='$objClass->DictId'/>";
	$Content .= "<input type='hidden' name='".$FieldNamePrefix."classid' value='$objClass->Id'/>";
	
	$Content .= "<table>";
	$Content .= "<tr><th>Class</th><td>".$objClass->Label."</td></tr>";	
	
	$Content .= "</table>";
	
	$Content .= "<div class='tab'>";
	$Content .= "<table>";
	$Content .= "<tr><th>Properties</th><td>";	
	
	$Content .= "<table>";
	
	$Content .= "<tr><th/><th>Selected?</th><th>Filter</th></tr>";
	
	
	
	$PropNum = 0;
	foreach ($Dicts->ClassProperties($objClass->DictId, $objClass->Id) as $objClassProperty){

		$PropNum = $PropNum + 1;
		$objProp = $Dicts->Dictionaries[$objClassProperty->PropDictId]->Properties[$objClassProperty->PropId];
		$Content .= "<tr>";
		$Content .= "<td>".$objProp->Label."</td>";
		$Content .= "<td>";
		$FieldName = $FieldNamePrefix."prop_".$PropNum."_sel";
		$Content .= "<input type='checkbox' name='$FieldName' value='selected' ";

		$FieldSelected = false;		
		
		if (isset($Fields[$FieldName])){
			if ($Fields[$FieldName] == 'selected'){
				$FieldSelected = true;
			}
		}
			
		if ($FieldSelected){
			$Content .= " checked='checked' ";
		}
			
		
		$Content .= "/>";
		$Content .= "</td>";
		$Content .= "<td>";

		
		$arrFilters = array();
		foreach ($Fields as $key=>$val){
			if (!IsEmptyString($FieldNamePrefix)){
				if (!(0 === strpos($key, $FieldNamePrefix))){
					continue;
				}
				$key = str_replace($FieldNamePrefix,'',$key);
			}
			
			$keyparts = explode('_',$key);
			
			switch ($keyparts[0]){
				case 'prop':			
					if (isset($keyparts[1])){
						if ($keyparts[1] == $PropNum){
							if (isset($keyparts[2])){
								if ($keyparts[2] == 'filter'){
									if (isset($keyparts[4])){
										$FilterNum = $keyparts[3];
										switch ($keyparts[4]){
											
											case 'type':
												$arrFilters[$FilterNum]['type']=$val;
												break;
											case 'value':
												$arrFilters[$FilterNum]['value']=$val;
												break;
												
										}
									}
								}
							}
						}
					}
					break;
					
			}
		}
				
		if (isset($Fields[$FieldNamePrefix."addfilter_".$PropNum])){
			$arrFilters[] = array();		
		}

		$FilterNum = 0;
		foreach ($arrFilters as $arrFilter){
			$FilterNum = $FilterNum + 1;

			$FieldName = $FieldNamePrefix."prop_".$PropNum."_filter_".$FilterNum;
			
			$FilterType = "";
			$FilterValue = "";
			if (isset($arrFilter['type'])){
				$FilterType = $arrFilter['type'];
			}
			if (isset($arrFilter['value'])){
				$FilterValue = $arrFilter['value'];
			}
			
			$Content .= "<input type='hidden' name='$FieldName' value='filter'/>";
			
			$Content .= "<select name='".$FieldName."_type'>";
			$Content .= "<option/>";
			foreach ($System->Config->FilterTypes as $optFilterType){
				$Content .= "<option";
				if ($FilterType == $optFilterType){
					$Content .= " selected='true' ";
				}
				$Content .= ">$optFilterType</option>";
			}				
			$Content .= "</select>";


			
			
			
			switch ($objProp->Field->DataType){
				case "date":
					$Content .= "<input type='date'  name='".$FieldName."_value' class='datepicker' id='".$FieldName."' size='10'";
					$Content .= " value='$FilterValue'>";
					$Content .= "</input>";								
					
					break;
			
				case "value":
					$Content .= "<select name='".$FieldName."_value' >";
					$Content .= "<option/>";
					foreach ($objClassProperty->Lists as $objClassPropList){
						$objList = $Dicts->Dictionaries[$objClassPropList->ListDictId]->Lists[$objClassPropList->ListId];
						foreach ($objList->Values as $objListValue){
							$optValue = $Dicts->Dictionaries[$objListValue->ValueDictId]->Values[$objListValue->ValueId];
							$Content .= "<option";
							
							if ( $optValue->Label  == $FilterValue ){
								$Content .= " selected='true' ";
							}						
							$Content .= ">".$optValue->Label."</option>";
						}
					}
					$Content .= "</select>";
					
					break;
											
				default: //line							
					$Content .= "<input  name='".$FieldName."_value' type='text'";
					if (!is_null($objProp->Field->Length)){
						$Content .= " size='".$objProp->Field->Length."' ";
					}
					$Content .= " maxlength='254' ";
					
					$Content .= " value='$FilterValue' >";
					$Content .= "</input>";								
					break;								
			}
			$Content .= "<br/>";
		}
		
		$Content .= "<input type='submit' value='add new filter' name='".$FieldNamePrefix."addfilter_".$PropNum."'/>";
		$Content .= "</td>";
		$Content .= "</tr>";		
	}
	

	$Content .= "</table>";

	$Content .= "</td></tr></table>";
	
	
	
	
	
	$Content .= "<h3>Links</h3>";
	
	
	
	$arrLinks = array();
	$NextLinkNum = 0;
	
	foreach ($Fields as $key=>$val){

		if (!IsEmptyString($FieldNamePrefix)){
			if (!(0 === strpos($key, $FieldNamePrefix))){
				continue;
			}
			$key = str_replace($FieldNamePrefix,'',$key);
		}
			
		$keyparts = explode('_',$key);
			
		switch ($keyparts[0]){
			case 'link':
				if (isset($keyparts[1])){
					if (is_numeric($keyparts[1])){
						$LinkNum = $keyparts[1];
						$arrLinks[$LinkNum] = $LinkNum;
						if ($LinkNum > $NextLinkNum){
							$NextLinkNum = $LinkNum;
						}
					}
				}
				break;
					
		}
		$NextLinkNum = $NextLinkNum + 1;
	}
	
	
	$AddLinkContent = "";

	$arrRels = $Dicts->RelationshipsFor($objClass->DictId, $objClass->Id, null, null);	
	$LinkNum = 0;	
	if (count($arrRels) > 0){
		$FieldName = $FieldNamePrefix."newlink";
		$AddLinkContent .= "<select name='$FieldName'>";	
		$AddLinkContent .= "<option/>";
		foreach ($arrRels as $optRel){
			$arrObjects = array();
			$arrObjects[] = $Dicts->Dictionaries[$optRel->ObjectDictId]->Classes[$optRel->ObjectId];
			foreach ($Dicts->SubClasses($optRel->ObjectDictId, $optRel->ObjectId) as $optObject){
				$arrObjects[] = $optObject;
			}
			foreach ($arrObjects as $optObject){
				$LinkNum = $LinkNum + 1;		
				
				if (isset($Fields[$FieldName])){
					if ($Fields[$FieldName] == $LinkNum){
					
						$NextFieldName = $FieldNamePrefix.'link_'.$NextLinkNum.'_';
						
						$Fields[$NextFieldName.'reldictid'] = $optRel->DictId;
						$Fields[$NextFieldName.'relid'] = $optRel->Id;
						
						$Fields[$NextFieldName.'classdictid'] = $optObject->DictId;
						$Fields[$NextFieldName.'classid'] = $optObject->Id;
						
						$arrLinks[$NextLinkNum] = $NextLinkNum;
					}
				}				
				
				$AddLinkContent .= "<option value=$LinkNum'>".$optRel->Label.' '.$optObject->Label."</option>";
			}
		}
		$AddLinkContent .= "</select>";
		$AddLinkContent .= "<input type='submit' name='".$FieldNamePrefix."addlink' value='add a new link'/>";
	}
	
	
	foreach ($arrLinks as $LinkNum){					
		$NextFieldName = $FieldNamePrefix.'link_'.$LinkNum.'_';
		$Content .= frmViewSelection($Fields, $NextFieldName);
	}
	
	
	$Content .= $AddLinkContent;

	$Content .= "</div>";
	
	
	if (!is_null($RelDictId)){
		if (!is_null($RelId)){
			$Content .= "</div>";			
		}
	}
	
		
	return $Content;
}


function MakeFields($objSel){

	global $System;
	global $Dicts;
	
	$Fields = array();
	$FieldNamePrefix = "";

	$objViewClass = $objSel->ViewClass;
	
	$Fields = MakeClassFields($objViewClass);
	
	return $Fields;
	
}

function MakeClassFields($objViewClass, $FieldNamePrefix = ""){

	global $System;
	global $Dicts;
	
	
	$Fields = array();
	
	if (!is_object($objViewClass)){
		return $Fields;
	}
	
	$objClass = $objViewClass->Class;

	if (!is_object($objClass)){
		return $Fields;
	}
	
	
	$Fields[$FieldNamePrefix.'classdictid'] = $objClass->DictId;
	$Fields[$FieldNamePrefix.'classid'] = $objClass->Id;
	
	$PropNum = 0;
	foreach ($Dicts->ClassProperties($objClass->DictId, $objClass->Id) as $objClassProperty){
		$PropNum = $PropNum + 1;		
		$objViewProp = null;
		foreach ($objViewClass->ViewProperties as $optViewProperty){
			if ($optViewProperty->Property->DictId == $objClassProperty->PropDictId){
				if ($optViewProperty->Property->Id == $objClassProperty->PropId){
					$objViewProp = $optViewProperty;
					continue;
				}
				
			}
		}

		if (!is_null($objViewProp)){		
			$FieldName = $FieldNamePrefix."prop_".$PropNum."_sel";		
			if ($objViewProp->Selected === true){
				$Fields[$FieldName] = 'selected';
			}

			$FilterNum = 0;
			foreach ($objViewProp->Filters as $objFilter){
				$FilterNum = $FilterNum + 1;
	
				$FieldName = $FieldNamePrefix."prop_".$PropNum."_filter_".$FilterNum;
				$Fields[$FieldName] = 'filter';
				$Fields[$FieldName.'_type'] = $objFilter->Type;
				$Fields[$FieldName.'_value'] = $objFilter->Value;
			}
		}
		
	}

	$LinkNum = 0;
	foreach ($objViewClass->ViewLinks as $objViewLink){
		$LinkNum = $LinkNum + 1;
		
		$NextFieldName = $FieldNamePrefix.'link_'.$LinkNum.'_';

		$Fields[$NextFieldName.'reldictid'] = $objViewLink->Relationship->DictId;
		$Fields[$NextFieldName.'relid'] = $objViewLink->Relationship->Id;

		if (!is_null($objViewLink->ViewObject)){			
			$Fields = array_merge($Fields,MakeClassFields($objViewLink->ViewObject, $NextFieldName));
		}
		
	}
	
	
	
	return $Fields;
}



?>