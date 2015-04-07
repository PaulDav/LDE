<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsView.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');


function dataViewUpdate($Mode, $Id = null,  $GroupId = null, $Name = null, $Description = null, $Publish = false) {

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a view');
	}
	
	$UserId = $System->User->Id;

	if (is_null($Id)){
		throw new Exception("View Id not specified");
	}
	$objView = new clsView($Id);
	
	
	switch ($Mode) {
		case 'new':
			if ($objView->Exists === true){
				throw new exception("View already exists");
			}
			break;
		default:
			if ($objView->Exists === false){
				throw new exception("View does not exist");
			}
			if (!($objView->canEdit)){
				throw new exception("You cannot update this View");
			}
			if (is_null($GroupId)){
				$GroupId = $objView->GroupId;
			}						
			break;
	}
						
	
	if (is_null($GroupId)){
		throw new exception("GroupId not specified");
	}
	$Group = new clsGroup($GroupId);
	if ($Group === false){
		throw new exception("Group does not exist");
	}	
	
	switch ($Mode) {
		case 'edit':
			break;
		case 'new':
			if (!($Group->canEdit)){
				throw new exception("You cannot update this Group");
			}			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	$objView->GroupId = $GroupId;
	$objView->Name  = $db->real_escape_string($Name);		
	$objView->Description  = $db->real_escape_string($Description);
	$objView->Publish = $Publish;

	$objView->Save();
	
	return $Id;
	
}  	


function dataViewClassRemove ($objViewClass){
		
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a view');
	}
	
	$UserId = $System->User->Id;

	$objView = $objViewClass->View;
	
	$Group = new clsGroup($objView->GroupId);
	if (!($Group->canEdit)){
		throw new exception("You cannot update this Group");
	}			

	$objViewClass->xml->parentNode->removeChild($objViewClass->xml);
	
	$objView->refreshXpath();
			
	$objView->Save();

	return true;
}

function dataViewClassRemoveProperties ($objViewClass){
		
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a view');
	}
	
	$UserId = $System->User->Id;

	$objView = $objViewClass->View;
	
	$Group = new clsGroup($objView->GroupId);
	if (!($Group->canEdit)){
		throw new exception("You cannot update this Group");
	}			

	$xmlProperties = $objViewClass->View->xpath->query("view:Properties",$objViewClass->xml)->item(0);
	if (is_object($xmlProperties)){	
		$objViewClass->xml->removeChild($xmlProperties);
	}
	
	$objView->refreshXpath();
			
	$objView->Save();

	return true;
}


function dataViewAddSelection ($ViewId = null , $ClassDictId = null, $ClassId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a view');
	}
	
	$UserId = $System->User->Id;

	if (is_null($ViewId)){
		throw new Exception("View Id not specified");
	}
	$objView = new clsView($ViewId);
	
	$Group = new clsGroup($objView->GroupId);
	if (!($Group->canEdit)){
		throw new exception("You cannot update this Group");
	}			

	
	$xmlSelections = $objView->xpath->query("/view:View/view:Selections")->item(0);
	if (!is_object($xmlSelections)){
		$xmlSelections = $objView->dom->createElementNS($objView->ViewNamespace, "Selections");
		$objView->dom->documentElement->appendChild($xmlSelections);
	}
	
	$xmlSelection = $objView->dom->createElementNS($objView->ViewNamespace, "Selection");
	$xmlSelections->appendChild($xmlSelection);
	
	dataViewAddSelectionClass ($objView, $xmlSelection, $ClassDictId, $ClassId);
			
	$objView->refreshXpath();
			
	$objView->Save();

	return true;
}

function dataViewAddSelectionClass ($objView, $xmlParent, $ClassDictId, $ClassId){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a view');
	}
	
	$UserId = $System->User->Id;

	
	$Group = new clsGroup($objView->GroupId);
	if (!($Group->canEdit)){
		throw new exception("You cannot update this Group");
	}

	$xmlClass = $objView->dom->createElementNS($objView->ViewNamespace, "Class");
	$xmlClass->setAttribute("classdictid", $ClassDictId);
	$xmlClass->setAttribute("classid", $ClassId);
	
	$xmlParent->appendChild($xmlClass);
	$objView->Save();
	
	return true;

}



function dataViewClassSetProperty($objViewClass, $objClassProp, $Selected){
	
	$xmlProperties = $objViewClass->View->xpath->query("view:Properties",$objViewClass->xml)->item(0);
	if (!is_object($xmlProperties)){
		$xmlProperties = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Properties");
		$objViewClass->xml->appendChild($xmlProperties);
	}
	
	$xmlProperty = null;
	foreach ($objViewClass->View->xpath->query("view:Property", $xmlProperties) as $optProperty){
		if ($optProperty->getAttribute("propdictid") == $objClassProp->PropDictId){
			if ($optProperty->getAttribute("propid") == $objClassProp->PropId){
				$xmlProperty = $optProperty;
				continue;
			}
			
		}
	}
	if (is_null($xmlProperty)){
		$xmlProperty = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Property");
		$xmlProperty->setAttribute("propdictid", $objClassProp->PropDictId);
		$xmlProperty->setAttribute("propid", $objClassProp->PropId);
		$xmlProperties->appendChild($xmlProperty);
	}
		
	if ($Selected === true){
		$xmlProperty->setAttribute('selected','true');
	}		
		
	$objViewClass->View->refreshXpath();
			
}



function dataViewClassSetFilter($objViewClass, $objClassProp, $FilterType, $FilterValue){

	if (IsEmptyString($FilterType)){
		if (!IsEmptyString($FilterValue)){
			$FilterType = 'is';
		}
	}

	if (IsEmptyString($FilterType)){
		return;
	}

	$xmlProperties = $objViewClass->View->xpath->query("view:Properties",$objViewClass->xml)->item(0);
	if (!is_object($xmlProperties)){
		$xmlProperties = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Properties");
		$objViewClass->xml->appendChild($xmlProperties);
	}
	
	$xmlProperty = null;
	foreach ($objViewClass->View->xpath->query("view:Property", $xmlProperties) as $optProperty){
		if ($optProperty->getAttribute("propdictid") == $objClassProp->PropDictId){
			if ($optProperty->getAttribute("propid") == $objClassProp->PropId){
				$xmlProperty = $optProperty;
				continue;
			}
			
		}
	}
	if (is_null($xmlProperty)){
		$xmlProperty = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Property");
		$xmlProperty->setAttribute("propdictid", $objClassProp->PropDictId);
		$xmlProperty->setAttribute("propid", $objClassProp->PropId);
		$xmlProperties->appendChild($xmlProperty);
	}
	
	$xmlFilters = $objViewClass->View->xpath->query("view:Filters",$xmlProperty)->item(0);
	if (!is_object($xmlFilters)){
		$xmlFilters = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Filters");
		$xmlProperty->appendChild($xmlFilters);
	}
	$xmlFilter = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Filter");
	$xmlFilters->appendChild($xmlFilter);

	$xmlFilter->setAttribute('type',$FilterType);
	$xmlFilter->setAttribute('value',$FilterValue);
	
	$objViewClass->View->refreshXpath();
	
}


function dataViewClassSetLink($objViewClass, $objLinkRel, $objLinkObject){

	$RelDictId = $objLinkRel->DictId;
	$RelId = $objLinkRel->Id;
	$ObjectDictId = $objLinkObject->DictId;
	$ObjectId = $objLinkObject->Id;
	
	
	$xmlLinks = null;
	$xmlLinks = $objViewClass->View->xpath->query("view:Links",$objViewClass->xml)->item(0);
	if (!is_object($xmlLinks)){
		$xmlLinks = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Links");
		$objViewClass->xml->appendChild($xmlLinks);
	}
	
	
	$xmlLink = null;
	
	$xpath = "view:Link[@reldictid='$RelDictId' and @relid='$RelId' and view:Class[@classdictid='$ObjectDictId' and @classid='$ObjectId']]";
	$xmlLink = $objViewClass->View->xpath->query($xpath,$xmlLinks)->item(0);
	if (!is_object($xmlLink)){
		$xmlLink = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Link");
		$xmlLink->setAttribute("reldictid",$RelDictId);
		$xmlLink->setAttribute("relid",$RelId);

		$xmlObjectClass = $objViewClass->View->dom->createElementNS($objViewClass->View->ViewNamespace,"Class");
		$xmlObjectClass->setAttribute("classdictid",$ObjectDictId);
		$xmlObjectClass->setAttribute("classid",$ObjectId);
		$xmlLink->appendChild($xmlObjectClass);

		$xmlLinks->appendChild($xmlLink);
		
		$objViewClass->View->refreshXpath();
		
	}

	$objViewClass->refresh();
	
	$objViewLink = null;
	foreach ($objViewClass->ViewLinks as $optViewLink){
		if ($optViewLink->Relationship == $objLinkRel){
			if ($optViewLink->ViewObject->Class == $objLinkObject){
				$objViewLink = $optViewLink;
				continue;
			}
		}
		
	}
	
	return $objViewLink;
	
}



?>