<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsShape.php');


function dataShapeUpdate($Mode, $Id = null, $GroupId = null, $Name = null, $Description = null, $Publish = false, $SelectionOf = null) {
	
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
			
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Shapes->xpath->query("/shape:Shapes/shape:Shape[@id]") as $xmlExistingShape){
					$ExistingId = $xmlExistingShape->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

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
	if (!is_null($GroupId)){
		$xmlShape->setAttribute("groupid",$GroupId);
	}
	
	xmlSetElement($xmlShape, "Name", $Name);
	xmlSetElement($xmlShape, "Description", $Description);
	
	
	switch ($Publish){
		case true;
			$xmlShape->setAttribute("publish", 'yes');
			break;
		default;
			$xmlShape->setAttribute("publish", 'no');
			break;			
	}
	
	if (!is_null($SelectionOf)){
		$xmlShape->setAttribute("selectionOf", $SelectionOf);
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


function dataShapeUpdateSelection($Id = null, $Selections){
		
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
	
	if (!isset($Shapes->Selections[$Id])){
		throw new Exception("Unknown Shape");
	}
	$objShape = $Shapes->Selections[$Id];
	if (is_null($objShape->SelectionOf)){
		throw new Exception("Shape is not a Selection");
	}

	
	$objSuperShape = $Shapes->getItems($objShape->SelectionOf);
	if (!is_object($objSuperShape)){
		throw new Exception("Unknown Super Shape");
	}
	
	$objSuperShapeClass = $objSuperShape->Selection->ShapeClass;
	
	if (isset($Selections['class'])){
		$SelectionKeys = array_keys($Selections['class']);
		if (count($SelectionKeys) > 0){
			$FirstClass = current($SelectionKeys);
			if (isset($objSuperShape->ShapeClasses[$FirstClass])){
				$objSuperShapeClass = $objSuperShape->ShapeClasses[$FirstClass];
			}
		}
	}
	
	$objParent = $objShape->Selection;
		
	$nextShapeClassId = 0;
	$nextShapeLinkId = 0;
	
	funUpdateSelectionClass($Selections, $objSuperShapeClass, $objParent->xml);
		
	$Shapes->Save();
	
}


function funUpdateSelectionClass($Selections, $objSuperShapeClass, $xmlParent) {
	
	global $Shapes;
	global $nextShapeClassId;
	global $nextShapeLinkId;
	
	if (isset($Selections['class'][$objSuperShapeClass->Id])){
				
		$nextShapeClassId = $nextShapeClassId + 1;
						
		$xmlShapeClass = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeClass");
		$xmlShapeClass->setAttribute('id',$nextShapeClassId);
		
		$xmlShapeClass->setAttribute('classdictid',$objSuperShapeClass->Class->DictId);
		$xmlShapeClass->setAttribute('classid',$objSuperShapeClass->Class->Id);
		
		
		$xmlParent->appendChild($xmlShapeClass);
		
		
		$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlShapeClass)->item(0);
		if (!is_object($xmlShapeProperties)){
			$xmlShapeProperties = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperties");
			$xmlShapeClass->appendChild($xmlShapeProperties);
		}
		
		
		$PropNum = 0;
		foreach ($objSuperShapeClass->ShapeProperties as $objSuperShapeProperty){
			if ($objSuperShapeProperty->Selected === true){
				$PropNum = $PropNum + 1;
				if (isset($Selections['class'][$objSuperShapeClass->Id]['properties'][$PropNum]['sel'])){
					if ($Selections['class'][$objSuperShapeClass->Id]['properties'][$PropNum]['sel'] === true){
									
						$xmlShapeProperty = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperty");
						$xmlShapeProperty->setAttribute("propdictid", $objSuperShapeProperty->Property->DictId);
						$xmlShapeProperty->setAttribute("propid", $objSuperShapeProperty->Property->Id);
						$xmlShapeProperty->setAttribute("cardinality", $objSuperShapeProperty->Cardinality);
						
						$xmlShapeProperties->appendChild($xmlShapeProperty);
		
						$xmlShapeProperty->setAttribute('selected','true');
						
						if (isset($Selections['class'][$objSuperShapeClass->Id]['properties'][$PropNum]['properties'])){
							$ComplexSelections = $Selections['class'][$objSuperShapeClass->Id]['properties'][$PropNum]['properties'];
							funUpdateSelectionComplexProperty($objSuperShapeProperty, $xmlShapeProperty, $ComplexSelections );
						}
												
					}
				}
			}
		}
		
		$xmlShapeLinks = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeLinks");
		$xmlShapeClass->appendChild($xmlShapeLinks);
		
		foreach ($objSuperShapeClass->ShapeLinks as $objSuperShapeLink){
			if (is_null($objSuperShapeLink->Relationship)){
				continue;
			}
			if (is_null($objSuperShapeLink->ShapeClass)){
				continue;
			}
			
			
			$createLink = false;
			if (isset($Selections['class'][$objSuperShapeLink->ShapeClass->Id]['properties'])){
				$createLink = true;
			}
			if (isset($Selections['link'][$objSuperShapeLink->Id]['properties'])){
				$createLink = true;
			}
			
			if ($createLink){
				$nextShapeLinkId = $nextShapeLinkId + 1;
				$xmlShapeLink = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeLink");
				$xmlShapeLink->setAttribute('id',$nextShapeLinkId);
				$xmlShapeLinks->appendChild($xmlShapeLink);
				$xmlShapeLink->setAttribute('reldictid', $objSuperShapeLink->xml->getAttribute('reldictid'));
				$xmlShapeLink->setAttribute('relid', $objSuperShapeLink->xml->getAttribute('relid'));
				if ($objSuperShapeLink->xml->getAttribute('inverse') == 'true'){
					$xmlShapeLink->setAttribute('inverse','true');
				}

				$Cardinality = 'many';
				if (!($objSuperShapeLink->xml->getAttribute('cardinality') == '')){
					$Cardinality = $objSuperShapeLink->xml->getAttribute('cardinality');
				}				
				$xmlShapeLink->setAttribute('cardinality', $Cardinality);

				$xmlShapeLink->removeAttribute('effdates');
				if ($objSuperShapeLink->xml->getAttribute('effdates') == 'true'){
					$xmlShapeLink->setAttribute('effdates', 'true');
				}
			
			
				if (isset($Selections['link'][$objSuperShapeLink->Id]['properties'])){
					
					$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlShapeLink)->item(0);
					if (!is_object($xmlShapeProperties)){
						$xmlShapeProperties = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperties");
						$xmlShapeLink->appendChild($xmlShapeProperties);
					}
					
					$PropNum = 0;
					foreach ($objSuperShapeLink->ShapeProperties as $objSuperShapeProperty){
						if ($objSuperShapeProperty->Selected === true){
							$PropNum = $PropNum + 1;
							if (isset($Selections['link'][$objSuperShapeLink->Id]['properties'][$PropNum]['sel'])){
								if ($Selections['link'][$objSuperShapeLink->Id]['properties'][$PropNum]['sel'] === true){
												
									$xmlShapeProperty = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperty");
									$xmlShapeProperty->setAttribute("propdictid", $objSuperShapeProperty->Property->DictId);
									$xmlShapeProperty->setAttribute("propid", $objSuperShapeProperty->Property->Id);
									$xmlShapeProperty->setAttribute("cardinality", $objSuperShapeProperty->Cardinality);
									
									$xmlShapeProperties->appendChild($xmlShapeProperty);
					
									$xmlShapeProperty->setAttribute('selected','true');
									
									$xmlShapeProperty->setAttribute('cardinality', $objSuperShapeProperty->Cardinality);
									
															
								}
							}
						}
					}
					
				}
			
			
				if (isset($Selections['class'][$objSuperShapeLink->ShapeClass->Id]['properties'])){
					
					funUpdateSelectionClass($Selections, $objSuperShapeLink->ShapeClass, $xmlShapeLink);				
					
				}
			}
			
		}
		
	}
	
	
}


function funUpdateSelectionComplexProperty($objSuperShapeProperty, $xmlParentShapeProperty, $Selections ){
	
	global $Shapes;
	
	$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlParentShapeProperty)->item(0);
	if (!is_object($xmlShapeProperties)){
		$xmlShapeProperties = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperties");
		$xmlParentShapeProperty->appendChild($xmlShapeProperties);
	}

	$PropNum = 0;
	foreach ($objSuperShapeProperty->ShapeProperties as $objSuperShapeComplexProperty){
		if ($objSuperShapeComplexProperty->Selected === true){
			$PropNum = $PropNum + 1;
			if (isset($Selections['properties'][$PropNum]['sel'])){
				if ($Selections['properties'][$PropNum]['sel'] === true){
								
					$xmlShapeProperty = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperty");
					$xmlShapeProperty->setAttribute("propdictid", $objSuperShapeComplexProperty->Property->DictId);
					$xmlShapeProperty->setAttribute("propid", $objSuperShapeComplexProperty->Property->Id);
					$xmlShapeProperty->setAttribute("cardinality", $objSuperShapeComplexProperty->Cardinality);
					
					$xmlShapeProperties->appendChild($xmlShapeProperty);
	
					$xmlShapeProperty->setAttribute('selected','true');
					
					$xmlShapeProperty->setAttribute('cardinality', $objSuperShapeComplexProperty->Cardinality);
					
					if (isset($Selections['properties'][$PropNum]['properties'])){
						$ComplexSelections = $Selections['properties'][$PropNum]['properties'];
						funUpdateSelectionComplexProperty($objSuperShapeComplexProperty, $xmlShapeProperty, $ComplexSelections );
					}
											
				}
			}
		}
	}
}



function dataShapeClass($Id = null, $objShape, $objClass, $Create = true, $Select = true, $Match = true){

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
		$objShape->Selection->xml->appendChild($xmlShapeClass);
		
	}
	else
	{
		$xmlShapeClass = $Shapes->xpath->query(".//shape:ShapeClass[@id='$Id']",$objShape->xml)->Item(0);
				
		if (!is_object($xmlShapeClass)){
			throw new Exception("Unknown Shape Class");
		}
	}
	
	$xmlShapeClass->setAttribute("classdictid",$objClass->DictId);
	$xmlShapeClass->setAttribute("classid",$objClass->Id);
	
	$xmlShapeClass->removeAttribute('create');
	if ($Create === true){
		$xmlShapeClass->setAttribute('create','true');
	}
	$xmlShapeClass->removeAttribute('select');
	if ($Select === true){
		$xmlShapeClass->setAttribute('select','true');
	}
	$xmlShapeClass->removeAttribute('match');
	if ($Match === true){
		$xmlShapeClass->setAttribute('match','true');
	}
	
	$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlShapeClass)->Item(0);
	if (is_object($xmlShapeProperties)){
		$xmlShapeProperties->parentNode->removeChild($xmlShapeProperties);		
	}
	
	return $Id;

}

function dataShapeClassDelete($objShape=null, $Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a shape');
	}
	
	$Shapes = $objShape->Shapes;
	
	if (!$Shapes->canEdit){
		throw new exception("You cannot update shapes");
	}
	
	$xmlShapeClass = $Shapes->xpath->query(".//shape:ShapeClass[@id='$Id']",$objShape->xml)->item(0);
	if (!is_object($xmlShapeClass)){
		throw new Exception("Shape Class does not exist");
	}

	$xmlShapeClass->parentNode->removeChild($xmlShapeClass);

	$Shapes->Save();
	
}


function dataShapeProperty($objShape, $xmlParent, $objProp, $Selected, $Cardinality = null){
	
	$Shapes = $objShape->Shapes;
	
	$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlParent)->item(0);
	if (!is_object($xmlShapeProperties)){
		$xmlShapeProperties = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperties");
		$xmlParent->appendChild($xmlShapeProperties);
	}
	
	$xmlShapeProperty = null;
	foreach ($Shapes->xpath->query("shape:ShapeProperty", $xmlShapeProperties) as $optShapeProperty){
		if ($optShapeProperty->getAttribute("propdictid") == $objProp->DictId){
			if ($optShapeProperty->getAttribute("propid") == $objProp->Id){
				$xmlShapeProperty = $optShapeProperty;
				continue;
			}
			
		}
	}
	if (is_null($xmlShapeProperty)){
		$xmlShapeProperty = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeProperty");
		$xmlShapeProperty->setAttribute("propdictid", $objProp->DictId);
		$xmlShapeProperty->setAttribute("propid", $objProp->Id);
		$xmlShapeProperties->appendChild($xmlShapeProperty);
	}
		
	if ($Selected === true){
		$xmlShapeProperty->setAttribute('selected','true');
	}		

	
	if (!is_null($Cardinality)){
		$xmlShapeProperty->setAttribute('cardinality', $Cardinality);
	}		
	
	
	return $xmlShapeProperty;
	
}


function dataShapeLink( $Id = null, $objShape, $objFromShapeClass, $objToShapeClass , $objRel, $Inverse=false, $Cardinality = 'many', $EffDates=true){
	
	$Shapes = $objShape->Shapes;
	
	$xmlShapeLink = null;
	
	
	if (is_null($Id)){
				
		$Id = 0;
		foreach ($Shapes->xpath->query(".//shape:ShapeLink[@id]",$objShape->xml) as $xmlExistingLink){
			$ExistingId = $xmlExistingLink->getAttribute("id");
			if ($ExistingId > $Id){
				$Id = $ExistingId;
			}
		}
		$Id = $Id + 1;
						
		$xmlShapeLink = $Shapes->dom->createElementNS($Shapes->ShapeNamespace,"ShapeLink");
		$xmlShapeLink->setAttribute('id',$Id);
		$objShape->Selection->xml->appendChild($xmlShapeLink);
		
	}
	else
	{
		$xmlShapeLink = $Shapes->xpath->query(".//shape:ShapeLink[@id='$Id']",$objShape->xml)->Item(0);
				
		if (!is_object($xmlShapeLink)){
			throw new Exception("Unknown Shape Link");
		}
	}
	

	if (!is_null($xmlShapeLink)){
		
		$xmlShapeLink->setAttribute("reldictid", $objRel->DictId);
		$xmlShapeLink->setAttribute("relid", $objRel->Id);

		$xmlShapeLink->setAttribute("fromshapeclassid", $objFromShapeClass->Id);
		$xmlShapeLink->setAttribute("toshapeclassid", $objToShapeClass->Id);
				
		$xmlShapeLink->removeAttribute("inverse");
		if ($Inverse === true){
			$xmlShapeLink->setAttribute('inverse','true');
		}

		$xmlShapeLink->setAttribute('cardinality',$Cardinality);		
		
		$xmlShapeLink->removeAttribute("effdates");
		if ($EffDates === true){
			$xmlShapeLink->setAttribute('effdates','true');
		}
		
		$xmlShapeProperties = $Shapes->xpath->query("shape:ShapeProperties",$xmlShapeLink)->Item(0);
		if (is_object($xmlShapeProperties)){
			$xmlShapeProperties->parentNode->removeChild($xmlShapeProperties);
		}
				
	}
	
	return $Id;

}



function dataShapeLinkDelete($objShape=null, $Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a shape');
	}
	
	$Shapes = $objShape->Shapes;
	
	if (!$Shapes->canEdit){
		throw new exception("You cannot update shapes");
	}
	
	$xmlShapeLink = $Shapes->xpath->query(".//shape:ShapeLink[@id='$Id']",$objShape->xml)->item(0);
	if (!is_object($xmlShapeLink)){
		throw new Exception("Shape Link does not exist");
	}

	$xmlShapeLink->parentNode->removeChild($xmlShapeLink);

	$Shapes->Save();
	
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



function dataShapeSetParent($ShapeId = null, $ParentId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	global $Shapes;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a shape');
	}
	
	$objShape = $Shapes->getItems($ShapeId);
	if (!is_object($objShape)){
		throw new Exception("Unknown Shape");
	}
		
	$objShape->xml->setAttribute("parentid",$ParentId);
		
	$objShape->Shapes->Save();
	
}


function dataShapeRemoveParent($ShapeId=null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}

	global $Shapes;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a shape');
	}

	$objShape = $Shapes->getItems($ShapeId);
	if (!is_object($objShape)){
		throw new Exception("Unknown Shape");
	}
	
	$objShape->xml->removeAttribute("parentid");
	
	$objShape->Shapes->Save();
		
}





?>