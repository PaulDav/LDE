<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');


Function pnlValue( $DictId, $ValueId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($objDict->Values[$ValueId])){
		throw new Exception("Unknown Value");
	}
	$objValue = $objDict->Values[$ValueId];

	$Content = '';

	$Content .= '<table class="sdgreybox">';
//	$Content .= "<tr><th>Id</th><td><a href='listvalue.php?dictid=".$objDict->Id."&valueid=$ValueId'>".$ValueId."</a></td></tr>";
	$Content .= "<tr><th>Label</th><td>".$objValue->Label."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objValue->Description)."</td></tr>";
	$Content .= "<tr><th>Code</th><td>".$objValue->Code."</td></tr>";
	
	$Content .= "<tr><th>URI</th><td>";
	if (filter_var($objValue->URI, FILTER_VALIDATE_URL)){
		$Content .= "<a href='".$objValue->URI."'>".$objValue->URI."</a>";
	}
	else
	{
		$Content .= $objValue->URI;
	}
	
	
    $Content .= '</table>';

    
    
    return $Content;
}


?>