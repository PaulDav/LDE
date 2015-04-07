<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlClassProperty( $DictId, $ClassId, $ClassPropId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	$objClass = $objDict->Classes[$ClassId];
	
	$objClassProperty = $objClass->Properties[$ClassPropId];
	
	$PropDictId = $DictId;
	if (!is_null($objClassProperty->PropDictId)){
		if (!($objClassProperty->PropDictId == $DictId)){
			$PropDictId = $objClassProperty->PropDictId;			
		}
	}
	$PropId = $objClassProperty->PropId;
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';

	$Content .= "<tr><th>Property</th><td>".pnlProperty($PropDictId, $PropId)."</td></tr>";

	$Content .= "<tr><th>Sequence</th><td>".$objClassProperty->Sequence."</td></tr>";
	
	$Content .= "<tr><th>Cardinality</th><td>".$objClassProperty->Cardinality."</td></tr>";
	
	$Content .= "<tr><th>Use as Name?</th><td>";
	switch ($objClassProperty->UseAsName){
		case true:
			$Content .= "yes";
			break;
		default:
			$Content .= "no";
			break;
			
	}
	$Content .= "</td></tr>";
	
	$Content .= "<tr><th>Use as Identifier?</th><td>";
	switch ($objClassProperty->UseAsIdentifier){
		case true:
			$Content .= "yes";
			break;
		default:
			$Content .= "no";
			break;
			
	}
	$Content .= "</td></tr>";
	
	$Content .= "<tr><th>Use in Lists?</th><td>";
	switch ($objClassProperty->UseInLists){
		case true:
			$Content .= "yes";
			break;
		default:
			$Content .= "no";
			break;
			
	}
	$Content .= "</td></tr>";
	
	
    $Content .= '</table>';
	    
    return $Content;
}


?>