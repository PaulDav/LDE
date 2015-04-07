<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');


function dataDictUpdate($Mode, $Id = null,  $GroupId = null, $Name = null, $Description = null, $Publish = false) {

	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	$UserId = $System->User->Id;

	if (is_null($Id)){
		throw new Exception("Dictionary Id not specified");
	}
	$objDict = new clsDict($Id);
	
	
	switch ($Mode) {
		case 'new':
			if ($objDict->Exists === true){
				throw new exception("Dictionary already exists");
			}
			break;
		default:
			if ($objDict->Exists === false){
				throw new exception("Dictionary does not exist");
			}
			if (!($objDict->canEdit)){
				throw new exception("You cannot update this Dictionary");
			}
			if (is_null($GroupId)){
				$GroupId = $objDict->GroupId;
			}						
			break;
	}
						
	
	if (is_null($GroupId)){
		throw new exception("GroupId not specified");
	}
	$objGroup = new clsGroup($GroupId);
	if ($objGroup === false){
		throw new exception("Group does not exist");
	}	
	
	switch ($Mode) {
		case 'edit':
			break;
		case 'new':
			if (!($objGroup->canEdit)){
				throw new exception("You cannot update this Group");
			}			
			break;
		
		default:
			throw new exception("Invalid Mode");
			break;
	}
	$objDict->GroupId = $GroupId;
	$objDict->Name  = $db->real_escape_string($Name);		
	$objDict->Description  = $db->real_escape_string($Description);
	$objDict->Publish = $Publish;

	$objDict->Save();
	
	return $Id;
	
}  	


function dataDictDelete($Id = null){
	
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
		
	$UserId = $System->User->Id;

	if (is_null($Id)){
		throw new Exception("Dictionary Id not specified");
	}
	
	if (!isset($Dicts->Dictionaries[$DictId])){
		throw new Exception("Unknown Dictionary");
	}

	
	$objDict = $Dicts->Dictionaries[$DictId];
		
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}						

    unlink($objDict->FilePath);
	
	return true;
	
}  	

function dataClassUpdate($Mode, $Id = null,  $DictId = null, $Concept = null, $Label = null, $Description = null, $Heading = false, $Source=null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlClasses = $objDict->xpath->query("/dict:Dictionary/dict:Classes")->item(0);
	if (!is_object($xmlClasses)){
		$xmlClasses = $objDict->dom->createElementNS($objDict->DictNamespace,"Classes");
		$objDict->dom->documentElement->appendChild($xmlClasses);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("//dict:Classes/dict:Class[@id]") as $xmlExistingClass){
					$ExistingId = $xmlExistingClass->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlClass = $objDict->dom->createElementNS($objDict->DictNamespace,"Class");
			$xmlClasses->appendChild($xmlClass);
			$xmlClass->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlClass = $objDict->xpath->query("/dict:Dictionary/dict:Classes/dict:Class[@id='$Id']")->item(0);
			if (!is_object($xmlClass)){
				throw new exception("Class does not exist");
			}
			
			break;
	}
						
	$xmlClass->setAttribute("concept",$Concept);
	xmlSetElement($xmlClass, "Label", $Label);
	xmlSetElement($xmlClass, "Heading", $Heading);			
	xmlSetElement($xmlClass, "Description", $Description);
	xmlSetElement($xmlClass, "Source", $Source);
	
	$objDict->Save();
	
	return $Id;
	
}  	

function dataClassDelete($Id = null,  $DictId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlClass = $objDict->xpath->query("/dict:Dictionary/dict:Classes/dict:Class[@id='$Id']")->item(0);
	if (!is_object($xmlClass)){
		throw new Exception("Class does not exist");
	}

	$xmlClass->parentNode->removeChild($xmlClass);

	$objDict->Save();
	
}


function dataClassVizUpdate($DictId = null, $ClassId = null,  $VizTypeId = null, $Params = array()) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (!($objClass = $Dicts->getClass($DictId, $ClassId))){
		throw new exception("Unknown Class");
	}
		
	$objDict = $Dicts->Dictionaries[$objClass->DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (!($xmlViz = $objDict->xpath->query("dict:Visualizer",$objClass->xml)->item(0))){
		$xmlViz = $objDict->dom->createElementNS($objDict->DictNamespace,"Visualizer");
		$objClass->xml->appendChild($xmlViz);			
	}	
	$xmlViz->setAttribute('typeid', $VizTypeId);
	if ($xmlParams = $objDict->xpath->query("dict:Params",$xmlViz)->item(0)){
		$xmlViz->removeChild($xmlParams);
	}	
	$xmlParams = $objDict->dom->createElementNS($objDict->DictNamespace,"Params");
	$xmlViz->appendChild($xmlParams);

	foreach ($Params as $ParamNum=>$Param){
		$xmlParam = $objDict->dom->createElementNS($objDict->DictNamespace,"Param");
		$xmlParams->appendChild($xmlParam);
		$xmlParam->setAttribute('num',$ParamNum);
		$xmlParam->setAttribute('propdictid',$Param['propdictid']);
		$xmlParam->setAttribute('propid',$Param['propid']);
	}
	
	$objDict->Save();
	
	return true;
	
}  	

function dataClassVizRemove($DictId = null, $ClassId = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (!($objClass = $Dicts->getClass($DictId, $ClassId))){
		throw new exception("Unknown Class");
	}
		
	$objDict = $Dicts->Dictionaries[$objClass->DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if ($xmlViz = $objDict->xpath->query("dict:Visualizer",$objClass->xml)->item(0)){
		$objClass->xml->removeChild($xmlViz);
	}		
	
	$objDict->Save();
	
	return true;
	
}  	


function dataClassSetSuperClass($Mode="edit", $DictId=null, $ClassId=null, $SuperDictId=null, $SuperClassId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	if (is_null($ClassId)){
		throw new Exception("Class Id not specified");
	}
	if (is_null($SuperDictId)){
		throw new Exception("Super Dictionary Id not specified");
	}
	if (is_null($SuperClassId)){
		throw new Exception("Super Class Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlClass = $objDict->xpath->query("/dict:Dictionary/dict:Classes/dict:Class[@id='$ClassId']")->item(0);
	if (!is_object($xmlClass)){
		throw new exeption("Unknown Class");
	}

	$xmlClass->setAttribute("subClassOf",$SuperClassId);

	$xmlClass->removeAttribute("subDictOf");
	if (!($SuperDictId == $DictId)){
		$xmlClass->setAttribute("subDictOf",$SuperDictId);
	}
		
	$objDict->Save();
	
}


function dataClassRemoveSuperClass($DictId=null, $ClassId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	if (is_null($ClassId)){
		throw new Exception("Class Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlClass = $objDict->xpath->query("/dict:Dictionary/dict:Classes/dict:Class[@id='$ClassId']")->item(0);
	if (!is_object($xmlClass)){
		throw new exeption("Unknown Class");
	}

	$xmlClass->removeAttribute("subClassOf");
	$xmlClass->removeAttribute("subDictOf");
	
	$objDict->Save();
	
}




function dataPropUpdate($Mode, $Id = null,  $DictId = null, $Label = null, $Description = null, $PropType = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	$xmlProperties = $objDict->xpath->query("/dict:Dictionary/dict:Properties")->item(0);
	if (!is_object($xmlProperties)){
		$xmlProperties = $objDict->dom->createElementNS($objDict->DictNamespace,"Properties");
		$objDict->dom->documentElement->appendChild($xmlProperties);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("/dict:Dictionary/dict:Properties/dict:Property[@id]") as $xmlExistingProp){
					$ExistingId = $xmlExistingProp->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlProp = $objDict->dom->createElementNS($objDict->DictNamespace,"Property");
			$xmlProperties->appendChild($xmlProp);
			$xmlProp->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlProp = $objDict->xpath->query("/dict:Dictionary/dict:Properties/dict:Property[@id='$Id']")->item(0);
			if (!is_object($xmlProp)){
				throw new exception("Property does not exist");
			}			
			break;
	}
						
	xmlSetElement($xmlProp, "Label", $Label);
	xmlSetElement($xmlProp, "Description", $Description);
	
	$xmlProp->removeAttribute("type");
	if (!is_null($PropType)){
		$xmlProp->setAttribute("type",$PropType);
	}
	
	$objDict->Save();
	
	return $Id;
	
}  	

function dataPropDelete($Id = null,  $DictId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlProp = $objDict->xpath->query("/dict:Dictionary/dict:Properties/dict:Property[@id='$Id']")->item(0);
	if (!is_object($xmlProp)){
		throw new Exception("Property does not exist");
	}

	$xmlProp->parentNode->removeChild($xmlProp);

	$objDict->Save();
	
}

function dataPropGroupAdd($DictId = null, $PropId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}

	
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

			
	$xmlProp = $objDict->xpath->query("/dict:Dictionary/dict:Properties/dict:Property[@id='$PropId']")->item(0);
	if (!is_object($xmlProp)){
		throw new exception("Property does not exist");
	}
	
	$xmlElementGroups = $objDict->xpath->query("dict:ElementGroups",$xmlProp)->item(0);
	if (!is_object($xmlElementGroups)){
		$xmlElementGroups = $objDict->dom->createElementNS($objDict->DictNamespace,"ElementGroups");
		$xmlProp->appendChild($xmlElementGroups);
	}	

	$xmlElementGroup = $objDict->dom->createElementNS($objDict->DictNamespace,"ElementGroup");
	$xmlElementGroups->appendChild($xmlElementGroup);
	
	$objDict->Save();
	
	return;
	
}  


function dataPropGroupDelete($DictId = null, $PropId = null, $GroupSeq = 1) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];
		if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

			
	$xmlProp = $objDict->xpath->query("/dict:Dictionary/dict:Properties/dict:Property[@id='$PropId']")->item(0);
	if (!is_object($xmlProp)){
		throw new exception("Property does not exist");
	}
	
	$xmlElementGroup = $objDict->xpath->query("dict:ElementGroups/dict:ElementGroup[$GroupSeq]",$xmlProp)->item(0);
	if (is_object($xmlElementGroup)){
		$xmlElementGroup->parentNode->removeChild($xmlElementGroup);		
	}
	
	$objDict->Save();
	
	return;
	
}  


function dataPropElementUpdate($Mode=null, $DictId = null, $PropId = null, $GroupSeq=null, $ElementDictId=null, $ElementPropId=null, $Cardinality=null){
	
	if (is_null($Cardinality)){
		$Cardinality = 'one';
	}
	
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	$objProp = $Dicts->getProperty($DictId, $PropId);
	if (!is_object($objProp)){
		throw new Exception('Unknown Property');
	}
	if (!isset($objProp->ElementGroups[$GroupSeq])){
		throw new Exception('Unknown Element Group');
	}
	$objElementGroup = $objProp->ElementGroups[$GroupSeq];
	
	$objElementProperty = $Dicts->getProperty($ElementDictId, $ElementPropId);
	if (!is_object($objElementProperty)){
		throw new Exception('Unknown Element Property');
	}
	
	
	$objElement = $objElementGroup->getElement($ElementDictId, $ElementPropId);
	
	switch ($Mode){
		case 'new':
			if (is_object($objElement)){
				throw new Exception('Element already exists');
			}
						
			$xmlElement = $objDict->dom->createElementNS($objDict->DictNamespace,"Element");
			$xmlElement->setAttribute('dictid',$ElementDictId);
			$xmlElement->setAttribute('propid',$ElementPropId);
			$objElementGroup->xml->appendChild($xmlElement);
			
			break;
		default:
			if (!is_object($objElement)){
				throw new Exception('Element does not exist');
			}
			
			$xmlElement = $objElement->xml;
			
			break;			
	}
	
	$xmlElement->setAttribute('cardinality',$Cardinality);
		
	$objDict->Save();
	
	return;
	
}  


function dataPropElementDelete( $DictId = null, $PropId = null, $GroupSeq=null, $ElementDictId=null, $ElementPropId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	$objProp = $Dicts->getProperty($DictId, $PropId);
	if (!is_object($objProp)){
		throw new Exception('Unknown Property');
	}
	if (!isset($objProp->ElementGroups[$GroupSeq])){
		throw new Exception('Unknown Element Group');
	}
	$objElementGroup = $objProp->ElementGroups[$GroupSeq];
		
	$objElement = $objElementGroup->getElement($ElementDictId, $ElementPropId);
		
	$objElement->xml->parentNode->removeChild($objElement->xml);
		
	$objDict->Save();
	
	return;
	
} 


function dataPartUpdate($Mode, $Id = null,  $DictId = null, $PropId = null, $Label = null, $Description = null, $DataType = null, $Cardinality = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];
		if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	if (is_null($PropId)){
		throw new Exception("Property Id not specified");
	}

	if (!isset($objDict->Properties[$PropId])){	
		throw new exception("Unknown Property");
	}
	$objProp = $objDict->Properties[$PropId];

	$xmlElements = $objDict->xpath->query("dict:Elements", $objProp->xml)->item(0);
	if (!is_object($xmlElements)){
		$xmlElements = $objDict->dom->createElementNS($objDict->DictNamespace,"Elements");
		$objProp->xml->appendChild($xmlElements);
	}
	
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("//dict:Part[@id]",$objProp->xml) as $xmlExistingPart){
					$ExistingId = $xmlExistingPart->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlPart = $objDict->dom->createElementNS($objDict->DictNamespace,"Part");
			$xmlElements->appendChild($xmlPart);
			$xmlPart->setAttribute("id",$Id);

			break;
		default:
			
			$xmlPart = $objDict->xpath->query("//dict:Part[@id='$Id']")->item(0);
			if (!is_object($xmlPart)){
				throw new exception("Part does not exist");
			}
			break;
	}
						
	xmlSetElement($xmlPart, "Label", $Label);
	xmlSetElement($xmlPart, "Description", $Description);
	
	$xmlPart->removeAttribute("cardinality");
	if (!empty($Cardinality)){
		$xmlPart->setAttribute("cardinality",$Cardinality);
	}

	
	$xmlField = $objDict->xpath->query("dict:Field", $xmlPart)->item(0);
	if (!is_object($xmlField)){
		$xmlField = $objDict->dom->createElementNS($objDict->DictNamespace,"Field");
		$xmlPart->appendChild($xmlField);
	}
		
	xmlSetElement($xmlField, "DataType", $DataType);
		
	$objDict->Save();
	
	return $Id;
	
}  	

function dataPartDelete($Id = null, $PropId = null,  $DictId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	if (is_null($PropId)){
		throw new Exception("Dictionary Id not specified");
	}
	if (!isset($objDict->Properties[$PropId])){
		throw new exception("Unknown Property");
	}
		
	$xmlPart = $objDict->xpath->query("//dict:Part[@id='$Id']",$objProp->xml)->item(0);
	if (!is_object($xmlPart)){
		throw new Exception("Part does not exist");
	}

	$xmlPart->parentNode->removeChild($xmlPart);

	$objDict->Save();
	
}


function dataPropSetSuperProperty($Mode="edit", $DictId=null, $PropId=null, $SuperDictId=null, $SuperPropId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	if (is_null($PropId)){
		throw new Exception("PropId not specified");
	}
	if (is_null($SuperDictId)){
		throw new Exception("Super Dictionary Id not specified");
	}
	if (is_null($SuperPropId)){
		throw new Exception("Super Prop Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlProp = $objDict->xpath->query("/dict:Dictionary/dict:Properties/dict:Property[@id='$PropId']")->item(0);
	if (!is_object($xmlProp)){
		throw new exeption("Unknown Property");
	}

	$xmlProp->setAttribute("subPropertyOf",$SuperPropId);
	$xmlProp->removeAttribute("subDictOf");
	if (!($SuperDictId == $DictId)){
		$xmlProp->setAttribute("subDictOf",$SuperDictId);
	}
		
	$objDict->Save();
	
}


function dataPropRemoveSuperProperty($DictId=null, $PropId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	if (is_null($PropId)){
		throw new Exception("Prop Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlProp = $objDict->xpath->query("/dict:Dictionary/dict:Properties/dict:Property[@id='$PropId']")->item(0);
	if (!is_object($xmlProp)){
		throw new exeption("Unknown Property");
	}

	$xmlProp->removeAttribute("subPropertyOf");
	$xmlProp->removeAttribute("subDictOf");
	
	$objDict->Save();
	
}


function dataFieldUpdate($DictId = null, $PropId = null, $PartId = null, $DataType = null, $Length = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;

	$objPart = null;
	$xmlParent = null;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	if (is_null($PropId)){
		throw new Exception("Property Id not specified");
	}

	if (!isset($objDict->Properties[$PropId])){	
		throw new exception("Unknown Property");
	}
	$objProp = $objDict->Properties[$PropId];
	$xmlParent = $objProp->xml;

	if (!is_null($PartId)){
		if (!isset($objDict->Properties[$PropId])){	
			throw new exception("Unknown Property");
		}
		$objPart = $objProp->Parts[$PartId];
		$xmlParent = $objPart->xml;
	}
	
	
	$xmlField = $objDict->xpath->query("dict:Field", $xmlParent)->item(0);
	if (!is_object($xmlField)){
		$xmlField = $objDict->dom->createElementNS($objDict->DictNamespace,"Field");
		$xmlParent->appendChild($xmlField);
	}
		
	xmlSetElement($xmlField, "DataType", $DataType);
	
	$xmlLength = $objDict->xpath->query("dict:Length", $xmlField)->item(0);
	if (is_object($xmlLength)){
		$xmlField->removeChild($xmlLength);
	}	
	if (!is_null($Length)){
		xmlSetElement($xmlField, "Length", $Length);		
	}
		
	$objDict->Save();
		
}  	


function dataRelUpdate($Mode, $Id , $DictId, $SubjectDictId, $SubjectId, $ObjectDictId, $ObjectId, $ConRel, $Label, $Description, $InverseLabel, $Cardinality=null, $Extending=false, $InverseExtending=false){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlRelationships = $objDict->xpath->query("/dict:Dictionary/dict:Relationships")->item(0);
	if (!is_object($xmlRelationships)){
		$xmlRelationships = $objDict->dom->createElementNS($objDict->DictNamespace,"Relationships");
		$objDict->dom->documentElement->appendChild($xmlRelationships);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("dict:Relationship[@id]",$xmlRelationships) as $xmlExistingRel){
					$ExistingId = $xmlExistingRel->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlRel = $objDict->dom->createElementNS($objDict->DictNamespace,"Relationship");
			$xmlRelationships->appendChild($xmlRel);
			$xmlRel->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlRel = $objDict->xpath->query("dict:Relationship[@id='$Id']",$xmlRelationships)->item(0);
			if (!is_object($xmlRel)){
				throw new exception("Relationship does not exist");
			}

			break;
	}

	$xmlRel->removeAttribute("conceptRelationship");
	if (!empty($ConRel)){
		$xmlRel->setAttribute("conceptRelationship",$ConRel);
	}

	xmlSetElement($xmlRel, "Label", $Label);
	xmlSetElement($xmlRel, "Description", $Description);
	
	$xmlInverse = $objDict->xpath->query("dict:Inverse",$xmlRel)->item(0);
	if (!is_object($xmlInverse)){
		$xmlInverse = $objDict->dom->createElementNS($objDict->DictNamespace,"Inverse");
		$xmlRel->appendChild($xmlInverse);
	}
	xmlSetElement($xmlInverse, "Label", $InverseLabel);

	$xmlRel->removeAttribute("cardinality");
	if (!is_null($Cardinality)){
		$xmlRel->setAttribute("cardinality",$Cardinality);
	}

	$xmlRel->removeAttribute('extending');
	if ($Extending === true){		
		$xmlRel->setAttribute('extending','true');
	}

	$xmlRel->removeAttribute('inverseextending');
	if ($InverseExtending === true){		
		$xmlRel->setAttribute('inverseextending','true');
	}
	
	
	$xmlSubject = $objDict->xpath->query("dict:Subject",$xmlRel)->item(0);
	if (!is_object($xmlSubject)){
		$xmlSubject = $objDict->dom->createElementNS($objDict->DictNamespace,"Subject");
		$xmlRel->appendChild($xmlSubject);
	}
	$xmlSubject->removeAttribute("class");	
	$xmlSubject->removeAttribute("dict");	

	if (!empty($SubjectDictId)){
		if (!($SubjectDictId == $DictId)){
			$xmlSubject->setAttribute("dict",$SubjectDictId);
		}
	}
	$xmlSubject->setAttribute("class",$SubjectId);

	
	$xmlObject = $objDict->xpath->query("dict:Object",$xmlRel)->item(0);
	if (!is_object($xmlObject)){
		$xmlObject = $objDict->dom->createElementNS($objDict->DictNamespace,"Object");
		$xmlRel->appendChild($xmlObject);
	}
	$xmlObject->removeAttribute("class");	
	$xmlObject->removeAttribute("dict");	

	if (!empty($ObjectDictId)){
		if (!($ObjectDictId == $DictId)){
			$xmlObject->setAttribute("dict",$ObjectDictId);
		}
	}
	$xmlObject->setAttribute("class",$ObjectId);
	
	$objDict->Save();
	
	return $Id;
	
}  	
	
function dataRelDelete($Id = null,  $DictId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlRel = $objDict->xpath->query("/dict:Dictionary/dict:Relationships/dict:Relationship[@id='$Id']")->item(0);
	if (!is_object($xmlRel)){
		throw new Exception("Relationship does not exist");
	}

	$xmlRel->parentNode->removeChild($xmlRel);

	$objDict->Save();
	
}


function dataClassPropUpdate($Mode, $Id=null, $DictId=null, $ClassId=null, $PropDictId=null, $PropId=null, $Cardinality=null, $UseAsName = false, $UseAsIdentifier = false, $UseInLists = true, $Sequence = null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ClassId)){
		throw new Exception("Class Id not specified");
	}
	
	$objClass = $objDict->Classes[$ClassId];

	
	if (is_null($PropId)){
		throw new Exception("Prop Id not specified");
	}
	
	switch ($UseAsName){
		case true:
			$UseAsName = 'true';
			break;
		default:
			$UseAsName = 'false';
			break;			
	}

	switch ($UseAsIdentifier){
		case true:
			$UseAsIdentifier = 'true';
			break;
		default:
			$UseAsIdentifier = 'false';
			break;			
	}
	
	switch ($UseInLists){
		case true:
			$UseInLists = 'true';
			break;
		default:
			$UseInLists = 'false';
			break;			
	}
	
	
	$objPropDict = $objDict;
	if (!empty($PropDictId)){
		$objPropDict = $Dicts->Dictionaries[$PropDictId];
	}
	$objProp = $objPropDict->Properties[$PropId];
		
	$xmlProperties = $objDict->xpath->query("dict:ClassProperties",$objClass->xml)->item(0);
	if (!is_object($xmlProperties)){
		$xmlProperties = $objDict->dom->createElementNS($objDict->DictNamespace,"ClassProperties");
		$objClass->xml->appendChild($xmlProperties);
	}


	
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("dict:ClassProperties/dict:ClassProperty[@id]",$objClass->xml) as $xmlExistingProp){
					$ExistingId = $xmlExistingProp->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlProp = $objDict->dom->createElementNS($objDict->DictNamespace,"ClassProperty");
			$xmlProp->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlProp = $objDict->xpath->query("dict:ClassProperty[@id='$Id']",$xmlProperties)->item(0);
			if (!is_object($xmlProp)){
				throw new exception("Class Property does not exist");
			}
			
			$xmlProperties->removeChild($xmlProp);
			
			break;
	}

	$xmlNextNode = null;
	
	if (!is_null($Sequence)){
		$optSeq = 0;
		foreach ($objDict->xpath->query("dict:ClassProperty",$xmlProperties) as $optProperty){
			$optSeq = $optSeq + 1;
			if ($optSeq == $Sequence){
				$xmlNextNode = $optProperty;
				break;
			}
		}
	}	
	
	$xmlProperties->insertBefore($xmlProp, $xmlNextNode);
	
	
	if (!is_null($PropDictId)){
		if (!($PropDictId == $DictId)){
			$xmlProp->setAttribute("propdictid",$PropDictId);					
		}
	}			
	$xmlProp->setAttribute("propid",$PropId);
		
	$xmlProp->removeAttribute("cardinality");
	if (!is_null($Cardinality)){
		$xmlProp->setAttribute("cardinality",$Cardinality);
	}

	$xmlProp->setAttribute("useAsName",$UseAsName);
	$xmlProp->setAttribute("useAsIdentifier",$UseAsIdentifier);
	$xmlProp->setAttribute("useInLists",$UseInLists);	
	
	$objDict->Save();
	
	return $Id;
	
}  	

function dataClassPropDelete($DictId=null, $ClassId=null, $ClassPropId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ClassId)){
		throw new Exception("Class Id not specified");
	}
	if (!isset($objDict->Classes[$ClassId])){
		throw new exception("Invalid Class");
	}
	$objClass = $objDict->Classes[$ClassId];
	
	if (is_null($ClassPropId)){
		throw new Exception("Class Prop Id not specified");
	}
	if (!isset($objClass->Properties[$ClassPropId])){
		throw new exception("Invalid Class Property");
	}
	$objClassProp = $objClass->Properties[$ClassPropId];
	
	$objClassProp->xml->parentNode->removeChild($objClassProp->xml);
	
	$objDict->Save();
	
}



function dataSameAsClassUpdate($Mode, $DictId, $ClassId=null, $SameAsDictId=null, $SameAsClassId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ClassId)){
		throw new Exception("Class Id not specified");
	}	
	$objClass = $objDict->Classes[$ClassId];

	if (is_null($SameAsClassId)){
		throw new Exception("Same as Class Id not specified");
	}	
	$objSameAsClass = $Dicts->getClass($SameAsDictId, $SameAsClassId);
	if (!is_Object($objSameAsClass)){
		throw new Exception("Unknown Same As Class");
	}
	
		
	$xmlSameAsClasses = $objDict->xpath->query("dict:SameAsClasses",$objClass->xml)->item(0);
	if (!is_object($xmlSameAsClasses)){
		$xmlSameAsClasses = $objDict->dom->createElementNS($objDict->DictNamespace,"SameAsClasses");
		$objClass->xml->appendChild($xmlSameAsClasses);
	}

		
	switch ($Mode) {
		case 'new':			

			$xmlSameAsClass = $objDict->dom->createElementNS($objDict->DictNamespace,"SameAsClass");
			$xmlSameAsClass->setAttribute("classid",$SameAsClassId);
			if (!is_null($SameAsDictId)){
				if (!($SameAsDictId == $DictId)){
					$xmlSameAsClass->setAttribute("dictid",$SameAsDictId);
				}
			}
			
			$xmlSameAsClasses->appendChild($xmlSameAsClass);
			
			break;
	}
	
	$objDict->Save();
	
	return true;
	
}  	

function dataSameAsClassDelete($DictId, $ClassId=null, $SameAsDictId=null, $SameAsClassId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ClassId)){
		throw new Exception("Class Id not specified");
	}
	if (!isset($objDict->Classes[$ClassId])){
		throw new exception("Invalid Class");
	}
	$objClass = $objDict->Classes[$ClassId];

	foreach ($objClass->SameAsClasses as $objSameAsClass){
		if ($objSameAsClass->DictId == $SameAsDictId){
			if ($objSameAsClass->ClassId == $SameAsClassId){
				$objSameAsClass->xml->parentNode->removeChild($objSameAsClass->xml);
			}
		}
	}
	$objDict->Save();
	
}


function dataHasPropUpdate($Mode, $Id=null, $ParentType = null, $DictId=null, $ParentId=null, $PropDictId=null, $PropId=null, $Cardinality=null, $UseAsName = false, $UseAsIdentifier = false){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ParentId)){
		throw new Exception("Parent Id not specified");
	}
		
	switch ($ParentType){
		case 'class':
			$objParent = $objDict->Classes[$ParentId];
			break;
		case 'relationship':
			$objParent = $objDict->Relationships[$ParentId];
			break;
		default:
			throw new Exception("Invalid Parent Type");
			break;			
	}
	

	if (is_null($PropId)){
		throw new Exception("Prop Id not specified");
	}
	
	switch ($UseAsName){
		case true:
			$UseAsName = 'true';
			break;
		default:
			$UseAsName = 'false';
			break;			
	}

	switch ($UseAsIdentifier){
		case true:
			$UseAsIdentifier = 'true';
			break;
		default:
			$UseAsIdentifier = 'false';
			break;			
	}
	
	
	
	$objPropDict = $objDict;
	if (!empty($PropDictId)){
		$objPropDict = $Dicts->Dictionaries[$PropDictId];
	}
	$objProp = $objPropDict->Properties[$PropId];
	
	$xmlProperties = $objDict->xpath->query("dict:HasProperties",$objParent->xml)->item(0);
	if (!is_object($xmlProperties)){
		$xmlProperties = $objDict->dom->createElementNS($objDict->DictNamespace,"HasProperties");
		$objParent->xml->appendChild($xmlProperties);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("dict:HasProperties/dict:HasProperty[@id]",$objParent->xml) as $xmlExistingProp){
					$ExistingId = $xmlExistingProp->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlProp = $objDict->dom->createElementNS($objDict->DictNamespace,"HasProperty");
			$xmlProperties->appendChild($xmlProp);
			$xmlProp->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlProp = $objDict->xpath->query("dict:HasProperty[@id='$Id']",$xmlProperties)->item(0);
			if (!is_object($xmlProp)){
				throw new exception("HasProperty does not exist");
			}			
			break;
	}

	if (!is_null($PropDictId)){
		if (!($PropDictId == $DictId)){
			$xmlProp->setAttribute("propdictid",$PropDictId);					
		}
	}			
	$xmlProp->setAttribute("propid",$PropId);
	
	
	$xmlProp->removeAttribute("cardinality");
	if (!is_null($Cardinality)){
		$xmlProp->setAttribute("cardinality",$Cardinality);
	}

	$xmlProp->setAttribute("useAsName",$UseAsName);
	$xmlProp->setAttribute("useAsIdentifier",$UseAsIdentifier);
	
	
	$objDict->Save();
	
	return $Id;
	
}  	

function dataHasPropDelete($ParentType = null, $DictId=null, $ParentId=null, $HasPropId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ParentId)){
		throw new Exception("Parent Id not specified");
	}
		
	switch ($ParentType){
		case 'class':
			$objParent = $objDict->Classes[$ParentId];
			break;
		case 'relationship':
			$objParent = $objDict->Relationships[$ParentId];
			break;
		default:
			throw new Exception("Invalid Parent Type");
			break;			
	}
	
		
	if (is_null($HasPropId)){
		throw new Exception("Has Prop Id not specified");
	}
	if (!isset($objParent->Properties[$HasPropId])){
		throw new exception("Invalid Has Property");
	}
	$objHasProp = $objParent->Properties[$HasPropId];
	
	$objHasProp->xml->parentNode->removeChild($objHasProp->xml);
	
	$objDict->Save();
	
}



function dataListUpdate($Mode, $Id = null,  $DictId = null, $Label = null, $Description = null, $Source = null, $DescribedAt = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlLists = $objDict->xpath->query("/dict:Dictionary/dict:Lists")->item(0);
	if (!is_object($xmlLists)){
		$xmlLists = $objDict->dom->createElementNS($objDict->DictNamespace,"Lists");
		$objDict->dom->documentElement->appendChild($xmlLists);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("//dict:Lists/dict:List[@id]") as $xmlExistingList){
					$ExistingId = $xmlExistingList->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlList = $objDict->dom->createElementNS($objDict->DictNamespace,"List");
			$xmlLists->appendChild($xmlList);
			$xmlList->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlList = $objDict->xpath->query("/dict:Dictionary/dict:Lists/dict:List[@id='$Id']")->item(0);
			if (!is_object($xmlList)){
				throw new exception("List does not exist");
			}
			
			break;
	}
						
	xmlSetElement($xmlList, "Label", $Label);
	xmlSetElement($xmlList, "Description", $Description);
	xmlSetElement($xmlList, "Source", $Source);			
	xmlSetElement($xmlList, "DescribedAt", $DescribedAt);			
	
	$objDict->Save();
	
	return $Id;
	
}  	

function dataListDelete($Id = null,  $DictId = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}
	
	$xmlList = $objDict->xpath->query("/dict:Dictionary/dict:Lists/dict:List[@id='$Id']")->item(0);
	if (!is_object($xmlList)){
		throw new Exception("List does not exist");
	}

	$xmlList->parentNode->removeChild($xmlList);

	$objDict->Save();
	
}



function dataValueUpdate($Mode, $Id = null,  $DictId = null, $Label = null, $Description = null, $Code = null, $URI = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	$xmlValues = $objDict->xpath->query("/dict:Dictionary/dict:Values")->item(0);
	if (!is_object($xmlValues)){
		$xmlValues = $objDict->dom->createElementNS($objDict->DictNamespace,"Values");
		$objDict->dom->documentElement->appendChild($xmlValues);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("/dict:Dictionary/dict:Values/dict:Value[@id]") as $xmlExistingValue){
					$ExistingId = $xmlExistingValue->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlValue = $objDict->dom->createElementNS($objDict->DictNamespace,"Value");
			$xmlValues->appendChild($xmlValue);
			$xmlValue->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlValue = $objDict->xpath->query("/dict:Dictionary/dict:Values/dict:Value[@id='$Id']")->item(0);
			if (!is_object($xmlValue)){
				throw new exception("Value does not exist");
			}			
			break;
	}
						
	xmlSetElement($xmlValue, "Label", $Label);
	xmlSetElement($xmlValue, "Description", $Description);
	xmlSetElement($xmlValue, "Code", $Code);
	xmlSetElement($xmlValue, "URI", $URI);
		
	$objDict->Save();
	
	return $Id;
	
}  	

function dataListValueUpdate($Mode, $Id=null, $DictId=null, $ListId=null, $ValueDictId=null, $ValueId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ListId)){
		throw new Exception("List Id not specified");
	}
	
	$objList = $objDict->Lists[$ListId];

	
	if (is_null($ValueId)){
		throw new Exception("Value Id not specified");
	}
	
	
	$objValuePropDict = $objDict;
	if (!empty($ValueDictId)){
		$objValueDict = new $Dicts->Dictionaries[$ValueDictId];
	}
//	$objValue = $objValueDict->Values[$ValueId];
		
	$xmlValues = $objDict->xpath->query("dict:ListValues",$objList->xml)->item(0);
	if (!is_object($xmlValues)){
		$xmlValues = $objDict->dom->createElementNS($objDict->DictNamespace,"ListValues");
		$objList->xml->appendChild($xmlValues);
	}
		
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($objDict->xpath->query("dict:ListValues/dict:ListValue[@id]",$objList->xml) as $xmlExistingValue){
					$ExistingId = $xmlExistingValue->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlListValue = $objDict->dom->createElementNS($objDict->DictNamespace,"ListValue");
			$xmlValues->appendChild($xmlListValue);
			$xmlListValue->setAttribute("id",$Id);
			
			break;
		default:
			
			$xmlListValue = $objDict->xpath->query("dict:ListValue[@id='$Id']",$xmlValues)->item(0);
			if (!is_object($xmlListValue)){
				throw new exception("List Value does not exist");
			}			
			break;
	}

	if (!is_null($ValueDictId)){
		if (!($ValueDictId == $DictId)){
			$xmlListValue->setAttribute("valuedictid,$ValueDictId");					
		}
	}			
	$xmlListValue->setAttribute("valueid",$ValueId);
	
						
	$objDict->Save();
	
	return $Id;
	
}  	

function dataListValueDelete($DictId=null, $ListId=null, $ListValueId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}	
	$objDict = $Dicts->Dictionaries[$DictId];

	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($ListId)){
		throw new Exception("List Id not specified");
	}
	if (!isset($objDict->Lists[$ListId])){
		throw new exception("Invalid List");
	}
	$objList = $objDict->Lists[$ListId];
	
	if (is_null($ListValueId)){
		throw new Exception("List Value Id not specified");
	}
	if (!isset($objList->Values[$ListValueId])){
		throw new exception("Invalid List Value");
	}
	$objListValue = $objList->Values[$ListValueId];
	
	$objListValue->xml->parentNode->removeChild($objListValue->xml);
	
	$objDict->Save();
	
}

function dataPropListUpdate($Mode, $DictId=null, $PropId=null, $ListDictId=null, $ListId=null){
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	$db = $System->db;
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a dictionary');
	}
	
	if (is_null($DictId)){
		throw new Exception("Dictionary Id not specified");
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!($objDict->canEdit)){
		throw new exception("You cannot update this Dictionary");
	}

	if (is_null($PropId)){
		throw new Exception("Property Id not specified");
	}
	
	if (is_null($ListId)){
		throw new Exception("List Id not specified");
	}
	
	$objProp = $objDict->Properties[$PropId];
	
	$xmlLists = $objDict->xpath->query("dict:PropertyLists",$objProp->xml)->item(0);
	if (!is_object($xmlLists)){
		$xmlLists = $objDict->dom->createElementNS($objDict->DictNamespace,"PropertyLists");
		$objProp->xml->appendChild($xmlLists);
	}
		
	switch ($Mode) {
		case 'new':

			$xmlPropertyList = $objDict->dom->createElementNS($objDict->DictNamespace,"PropertyList");
			$xmlLists->appendChild($xmlPropertyList);
			$xmlPropertyList->setAttribute("listid",$ListId);
			if (!empty($ListDictId)){
				if (!($ListDictId == $DictId)){
					$xmlPropertyList->setAttribute("listdictid",$ListDictId);
				}
			}
			
			break;
		case 'delete':

			if (empty($ListDictId) || ($ListDictId == $DictId)){
				$xmlPropertyList = $objDict->xpath->query("dict:PropertyList[@listid='$ListId']",$xmlLists)->item(0);
			}
			else
			{
				$xmlPropertyList = $objDict->xpath->query("dict:PropertyList[@listdictid = '$ListDictId' and @listid='$ListId']",$xmlLists)->item(0);
			}
			
			if (is_object($xmlPropertyList)){
				$xmlLists->removeChild($xmlPropertyList);				
			}			
			break;
	}
						
	$objDict->Save();
	
}  	


?>