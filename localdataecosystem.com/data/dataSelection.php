<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsShapes.php');


function dataSelUpdate($Mode, $Id = null, $ShapeId = null, $Fields = array()) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a selection');
	}
	
	
	global $Shapes;
	if (!isset($Shapes)){
		$Shapes = new clsShapes;
	}
	if (!$Shapes->canEdit){
		throw new exception("You cannot update shapes");
	}

	switch ($Mode) {
		case 'new':			
			$MaxId = 0;
			foreach ($Shapes->xpath->query("/shape:Shapes/shape:Shape[@id]") as $xmlExistingShape){
				$ExistingId = $xmlExistingShape->getAttribute("id");
				if ($ExistingId > $MaxId){
					$MaxId = $ExistingId;
				}
			}
			$Id = $MaxId + 1;

			$xmlShape = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"Shape");
			$Shapes->dom->documentElement->appendChild($xmlShape);
			$xmlShape->setAttribute("id",$Id);
			
			$xmlShape->setAttribute("ownerid",$System->User->Id);
			
			break;
		default:
			
			$xmlShape = $Shapes->xpath->query("/shape:Shapes/shape:Shape[@id='$Id']")->item(0);
			if (!is_object($xmlShape)){
				throw new exception("Shape does not exist");
			}
			
			break;
	}

	$xmlShape->removeAttribute("groupid");
	$xmlShape->setAttribute("groupid",$GroupId);
	
	xmlSetElement($xmlShape, "Name", $Name);
	xmlSetElement($xmlShape, "Description", $Description);
	
	
	switch ($Publish){
		case true;
			$xmlShape->setAttribute("published", 'true');
			break;
		default;
			$xmlShape->setAttribute("published", 'false');
			break;			
	}
	
	
	$Shapes->Save();

	return $Id;

}  	

function dataShapeDelete($Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a shape');
	}
	
	global $Shapes;
	if (!isset($Shapes)){
		$Shapes = new clsShapes;
	}
	if (!$Shapes->canEdit){
		throw new exception("You cannot update shapes");
	}
	
	$xmlShape = $Shapes->xpath->query("/shape:Shapes/shape:Shape[@id='$Id']")->item(0);
	if (!is_object($xmlShape)){
		throw new Exception("Shape does not exist");
	}

	$xmlShape->parentNode->removeChild($xmlShape);

	$Shapes->Save();
	
}


function dataShapeClass($Id = null, $objParent = null, $objClass){

	$objShape = $objParent->Shape;
	$Shapes = $objShape->Shapes;

	if (is_null($Id)){
				
		$Id = 0;
		foreach ($Shapes->xpath->query(".//shape:ShapeClass[@id]",$objShape->xml) as $xmlExistingClass){
			$ExistingId = $xmlExistingClass->getAttribute("id");
			if ($ExistingId > $Id){
				$Id = $ExistingId;
			}
		}
		$Id = $Id + 1;
						
		$xmlShapeClass = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeClass");
		$xmlShapeClass->setAttribute('id',$Id);
		$objParent->xml->appendChild($xmlShapeClass);
		
	}
	else
	{
		$xmlShapeClass = $Shapes->xpath->query("//shape:ShapeClass[@id='$Id']",$objShape->xml)->Item(0);
		if (!is_object($xmlShapeClass)){
			throw new Exception("Unknown Shape Class");
		}
	}
	
	$xmlShapeClass->setAttribute("classdictid",$objClass->DictId);
	$xmlShapeClass->setAttribute("classid",$objClass->Id);

	$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlShapeClass)->Item(0);
	if (is_object($xmlShapeProperties)){
		$xmlShapeProperties->parentNode->removeChild($xmlShapeProperties);
	}
	
	return $Id;

}




function dataShapeProperty($objParent, $objHasProp, $Selected){
	
	$objShape = $objParent->Shape;
	$Shapes = $objShape->Shapes;
	
	$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$objParent->xml)->item(0);
	if (!is_object($xmlShapeProperties)){
		$xmlShapeProperties = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperties");
		$objParent->xml->appendChild($xmlShapeProperties);
	}
	
	$xmlShapeProperty = null;
	foreach ($Shapes->xpath->query("shape:ShapeProperty", $xmlShapeProperties) as $optShapeProperty){
		if ($optShapeProperty->getAttribute("propdictid") == $objHasProp->PropDictId){
			if ($optShapeProperty->getAttribute("propid") == $objHasProp->PropId){
				$xmlShapeProperty = $optShapeProperty;
				continue;
			}
			
		}
	}
	if (is_null($xmlShapeProperty)){
		$xmlShapeProperty = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperty");
		$xmlShapeProperty->setAttribute("propdictid", $objHasProp->PropDictId);
		$xmlShapeProperty->setAttribute("propid", $objHasProp->PropId);
		$xmlShapeProperties->appendChild($xmlShapeProperty);
	}
		
	if ($Selected === true){
		$xmlShapeProperty->setAttribute('selected','true');
	}
	

}



function dataShapeLink($Mode, $objParentShapeClass=null, $Id = null, $objRel=null, $Inverse=false){
	
	$objShape = $objParentShapeClass->Shape;
	$Shapes = $objShape->Shapes;

	$xmlShapeLink = null;
	
	
	$xmlShapeLinks = $Shapes->xpath->query("shape:ShapeLinks",$objParentShapeClass->xml)->item(0);
	if (!is_object($xmlShapeLinks)){
		$xmlShapeLinks = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,'ShapeLinks');
		$objParentShapeClass->xml->appendChild($xmlShapeLinks);		
	}
	
	switch ($Mode){
		case 'new':
			
			$Id = 0;
			foreach ($Shapes->xpath->query(".//shape:ShapeLink[@id]",$objShape->xml) as $xmlExistingLink){
				$ExistingId = $xmlExistingLink->getAttribute("id");
				if ($ExistingId > $Id){
					$Id = $ExistingId;
				}
			}
			$Id = $Id + 1;
						
			$xmlShapeLink = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,'ShapeLink');
			$xmlShapeLink->setAttribute('id',$Id);
			$xmlShapeLinks->appendChild($xmlShapeLink);
			
			break;
			
		default:
			$xmlShapeLink = $Shapes->xpath->query("//shape:ShapeLink[@id='$Id']",$objShape->xml)->item(0);
			if (!is_object($xmlShapeLink)){
				throw new Exception("Invalid Shape Link Id");
			}
	}

	if (!is_null($xmlShapeLink)){
		
		$xmlShapeLink->setAttribute("reldictid", $objRel->DictId);
		$xmlShapeLink->setAttribute("relid", $objRel->Id);
		
		$xmlShapeLink->removeAttribute("inverse");
		if ($Inverse === true){
			$xmlShapeLink->setAttribute('inverse','true');
		}
		
		
		$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlShapeLink)->Item(0);
		if (is_object($xmlShapeProperties)){
			$xmlShapeProperties->parentNode->removeChild($xmlShapeProperties);
		}
				
	}

	return $Id;

}


function dataShapeSetFilter($objParent, $objHasProp, $FilterType, $FilterValue){

	$objShape = $objParent->Shape;
	$Shapes = $objShape->Shapes;
	
	$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$objParent->xml)->item(0);
	if (!is_object($xmlShapeProperties)){
		$xmlShapeProperties = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperties");
		$objParent->xml->appendChild($xmlShapeProperties);
	}
	
	$xmlShapeProperty = null;
	foreach ($Shapes->xpath->query("shape:ShapeProperty", $xmlShapeProperties) as $optShapeProperty){
		if ($optShapeProperty->getAttribute("propdictid") == $objHasProp->PropDictId){
			if ($optShapeProperty->getAttribute("propid") == $objHasProp->PropId){
				$xmlShapeProperty = $optShapeProperty;
				continue;
			}
			
		}
	}
	if (is_null($xmlShapeProperty)){
		$xmlShapeProperty = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperty");
		$xmlShapeProperty->setAttribute("propdictid", $objHasProp->PropDictId);
		$xmlShapeProperty->setAttribute("propid", $objHasProp->PropId);
		$xmlShapeProperties->appendChild($xmlShapeProperty);
	}
	
	
	$xmlFilters = $Shapes->xpath->query("shape:Filters",$xmlShapeProperty)->item(0);
	if (!is_object($xmlFilters)){
		$xmlFilters = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"Filters");
		$xmlShapeProperty->appendChild($xmlFilters);
	}
	$xmlFilter = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"Filter");
	$xmlFilters->appendChild($xmlFilter);

	if (!IsEmptyString($FilterType)){
		$xmlFilter->setAttribute('type',$FilterType);
	}
	if (!IsEmptyString($FilterValue)){	
		$xmlFilter->setAttribute('value',$FilterValue);
	}
	
	$Shapes->refreshXpath();
	
}



function dataShapeSetParent($Shape = null, $ParentShape=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	global $Shapes;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a shape');
	}
		
	$objShape->xml->setAttribute("parentid",$ParentShape->Id);
	
	$objShape->Shapes->Save();
	
}


function dataShapeRemoveParent($Shape){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}

	global $Shapes;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a shape');
	}
		
	$Shape->xml->removeAttribute("parentid");
	
	$objShape->Shapes->Save();
		
}





?>