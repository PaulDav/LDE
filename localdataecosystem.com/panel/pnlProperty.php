<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once(dirname(__FILE__).'/../panel/pnlField.php');


Function pnlProperty( $DictId, $PropId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($objDict->Properties[$PropId])){
		throw new Exception("Unknown Property");
	}
	$objProp = $objDict->Properties[$PropId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	
	if (!is_null($objDict->EcoSystem)){
		$Content .= "<tr><th>EcoSystem</th><td>".$objDict->EcoSystem."</td></tr>";
	}
	
	$Content .= "<tr><th>Id</th><td><a href='property.php?dictid=".$objDict->Id."&propid=$PropId'>".$PropId."</a></td></tr>";
	$Content .= "<tr><th>Label</th><td>".$objProp->Label."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br(make_links($objProp->Description))."</td></tr>";
	$Content .= "<tr><th>Type</th><td>".$objProp->Type."</td></tr>";
	
	if (!is_null($objProp->SubPropertyOf)){
		$objSuperDict = $objDict;
		if (!(is_null($objProp->SubDictOf))){
			if (!($objProp->SubDictOf == $DictId)){
				$objSuperDict = $Dicts->Dictionaries[$objProp->SubDictOf];
			}
		}
		if (isset($objSuperDict->Properties[$objProp->SubPropertyOf])){
			$objSuperProp = $objSuperDict->Properties[$objProp->SubPropertyOf];
			$Content .= "<tr><th>Sub Property of</th><td>".pnlProperty($objSuperProp->DictId, $objSuperProp->Id)."</td></tr>";
		}
	}	

	
	if ($objProp->Type == 'simple'){
		$Content .= "<tr><th>Field</th><td>".pnlField($objProp->Field)."</td></tr>";
    }
	
	
    $Content .= '</table>';

    
    
    return $Content;
}


Function pnlPropertyElement( $DictId, $PropId, $GroupSeq, $ElementDictId, $ElementPropId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($objDict->Properties[$PropId])){
		throw new Exception("Unknown Property");
	}
	$objProp = $objDict->Properties[$PropId];
	
	if (!isset($objProp->ElementGroups[$GroupSeq])){
		throw new exception("Unknown Element Group");
	}
	$objElementGroup = $objProp->ElementGroups[$GroupSeq];
	
	$objElement = $objElementGroup->getElement($ElementDictId, $ElementPropId);
	if (!is_object($objElement)){
		throw new exception('Unknown Element');
	}
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Property</th><td>".pnlProperty($ElementDictId, $ElementPropId)."</td></tr>";
	$Content .= "<tr><th>Cardinality</th><td>".$objElement->Cardinality."</td></tr>";
    $Content .= '</table>';
    
    return $Content;
}


?>