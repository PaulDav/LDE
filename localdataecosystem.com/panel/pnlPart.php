<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once(dirname(__FILE__).'/../panel/pnlField.php');

Function pnlPart( $DictId, $PropId, $PartId ){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	$objProp = $objDict->Properties[$PropId];
	
	$objPart = $objProp->Parts[$PartId];
		
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	
	$Content .= "<tr><th>Id</th><td><a href='part.php?dictid=$DictId&propid=$PropId&partid=$PartId'>".$PartId."</a></td></tr>";
	$Content .= "<tr><th>Label</th><td>".$objPart->Label."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objPart->Description)."</td></tr>";	
	
	$Content .= "<tr><th>Cardinality</th><td>".$objPart->Cardinality."</a></td></tr>";

	if ($objPart->Type == 'simple'){
		$Content .= "<tr><th>Field</th><td>".pnlField($objPart->Field)."</td></tr>";
    }
		
    $Content .= '</table>';
	    
    return $Content;
}


?>