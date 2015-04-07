<?php

require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlProfile.php');

Function pnlSpec( $SpecId){
	
	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}
	
	$objSpec = $Specs->Items[$SpecId];
	
	$User = new clsUser($objSpec->OwnerId);
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='spec.php?specid=".$objSpec->Id."'>".$objSpec->Id."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objSpec->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objSpec->Description)."</td></tr>";
	$Content .= "<tr><th>File Type</th><td>".$objSpec->FileType."</td></tr>";
	
	$Content .= "<tr><th>Profile</th><td>";
	if (!IsEmptyString($objSpec->ProfileId)){
		$Content .= pnlProfile($objSpec->ProfileId);
	}
	$Content .= "</td></tr>";
	
	
	$Content .= "<tr><th>Owned by</th><td>";
	if (!is_null($User->PictureOf)) {
		$Content .= '<img height = "30" src="image.php?Id='.$User->PictureOf.'" /><br/>';
	}
	$Content .= $User->Name."</td></tr>";

	$Content .= "<tr><th>Publish?</th><td>";
	switch ($objSpec->Publish){
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