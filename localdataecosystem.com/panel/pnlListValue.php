<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../panel/pnlValue.php');

Function pnlListValue( $DictId, $ListId, $ListValueId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	
	$objList = $objDict->Lists[$ListId];
	
	$objListValue = $objList->Values[$ListValueId];
	
	$ValueDictId = $DictId;
	if (!is_null($objListValue->ValueDictId)){
		if (!($objListValue->ValueDictId == $DictId)){
			$ValueDictId = $objListValue->ValueDictId;			
		}
	}
	$ValueId = $objListValue->ValueId;
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Value</th><td>".pnlValue($ValueDictId, $ValueId)."</td></tr>";

    $Content .= '</table>';
	    
    return $Content;
}


?>