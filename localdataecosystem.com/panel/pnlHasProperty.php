<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlHasProperty( $ParentType, $DictId, $ParentId, $HasPropId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	

	switch ($ParentType){
		case 'class':
			$objParent = $objDict->Classes[$ParentId];
			break;
		case 'relationship':
			$objParent = $objDict->Relationships[$ParentId];
			break;			
	}
	
	$objHasProperty = $objParent->Properties[$HasPropId];
	
	$PropDictId = $DictId;
	if (!is_null($objHasProperty->PropDictId)){
		if (!($objHasProperty->PropDictId == $DictId)){
			$PropDictId = $objHasProperty->PropDictId;			
		}
	}
	$PropId = $objHasProperty->PropId;
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Cardinality</th><td>".$objHasProperty->Cardinality."</td></tr>";
	
	$Content .= "<tr><th>Use as Name?</th><td>";
	switch ($objHasProperty->UseAsName){
		case true:
			$Content .= "yes";
			break;
		default:
			$Content .= "no";
			break;
			
	}
	$Content .= "</td></tr>";
	
	$Content .= "<tr><th>Use as Identifier?</th><td>";
	switch ($objHasProperty->UseAsIdentifier){
		case true:
			$Content .= "yes";
			break;
		default:
			$Content .= "no";
			break;
			
	}
	$Content .= "</td></tr>";
	
	
	
	$Content .= "<tr><th>Property</th><td>".pnlProperty($PropDictId, $PropId)."</td></tr>";

    $Content .= '</table>';
	    
    return $Content;
}


?>