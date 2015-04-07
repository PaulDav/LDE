<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlDict( $DictId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	$objUser = new clsUser($objDict->OwnerId);
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	
	if (!is_null($objDict->EcoSystem)){
		$Content .= "<tr><th>EcoSystem</th><td>".$objDict->EcoSystem."</td></tr>";
	}
	
	$Content .= "<tr><th>Id</th><td><a href='dict.php?dictid=".$objDict->Id."'>".$objDict->Id."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objDict->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objDict->Description)."</td></tr>";
	
	$Content .= "<tr><th>Owned by</th><td>";
	if (!is_null($objUser->PictureOf)) {
		$Content .= '<img height = "30" src="image.php?Id='.$objUser->PictureOf.'" /><br/>';
	}
	$Content .= $objUser->Name."</td></tr>";

	$Content .= "<tr><th>Publish?</th><td>";
	switch ($objDict->Publish){
		case true:
			$Content .= "Yes";
			break;
		default:
			$Content .= "No";
			break;			
	}
	$Content .= "</td></tr>";
	
    $Content .= '</table>';
	    
    return $Content;
}


?>