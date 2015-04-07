<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlList( $DictId, $ListId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	
	if (!isset($objDict->Lists[$ListId])){
		throw new Exception("Unknown List");
	}
	$objList = $objDict->Lists[$ListId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='list.php?dictid=".$objDict->Id."&listid=$ListId'>".$ListId."</a></td></tr>";
	$Content .= "<tr><th>Label</th><td>".$objList->Label."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objList->Description)."</td></tr>";
	$Content .= "<tr><th>Source</th><td>".$objList->Source."</td></tr>";

	$Content .= "<tr><th>Described At</th><td>";
	
	if (filter_var($objList->DescribedAt, FILTER_VALIDATE_URL)){
		$Content .= "<a href='".$objList->DescribedAt."'>".$objList->DescribedAt."</a>";
	}
	else
	{
		$Content .= $objList->DescribedAt;
	}
	
	$Content .= "</td></tr>";
	
    $Content .= '</table>';
	    
    return $Content;
}


?>