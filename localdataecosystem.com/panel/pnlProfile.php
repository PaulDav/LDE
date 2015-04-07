<?php

require_once('pnlShape.php');

require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlProfile( $ProfileId){
	
	global $Profiles;
	if (!isset($Profiles)){
		$Profiles = new clsProfiles;
	}
	if (!isset($Profiles->Items[$ProfileId])){
		throw new exception("Unknown Profile");
	}
	$Profile = $Profiles->Items[$ProfileId];
	
	$User = new clsUser($Profile->OwnerId);
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='profile.php?profileid=".$Profile->Id."'>".$Profile->Id."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$Profile->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($Profile->Description)."</td></tr>";
	
	
	if (!is_null($Profile->ShapeId)){
		$Content .= "<tr><th>Shape</th><td>";
		$Content .= pnlShape($Profile->ShapeId);		
		$Content .= "</td></tr>";		
	}	
	
	$Content .= "<tr><th>Owned by</th><td>";
	if (!is_null($User->PictureOf)) {
		$Content .= '<img height = "30" src="image.php?Id='.$User->PictureOf.'" /><br/>';
	}
	$Content .= $User->Name."</td></tr>";

	$Content .= "<tr><th>Publish?</th><td>";
	switch ($Profile->Publish){
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