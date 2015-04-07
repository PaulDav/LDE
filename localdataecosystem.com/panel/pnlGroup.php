<?php

require_once(dirname(__FILE__).'/../class/clsGroup.php');
require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlGroup( $GroupId){
	
	$objGroup = new clsGroup($GroupId);
	$objUser = new clsUser($objGroup->OwnerId);
	
	$Content = '';

	if (!is_null($objGroup->Picture)) {
		$Content .= "<a href='group.php?groupid=".$objGroup->Id."'>";
		$Content .= '<img class="byimage" src="image.php?Id='.$objGroup->Picture.'" /><br/>';
		$Content .= "</a>";
	}
	
	
	$Content .= '<table class="sdgreybox">';
	if ($objGroup->Name == ""){
		$Content .= "<tr><th>Id</th><td><a href='group.php?groupid=".$objGroup->Id."'>".$objGroup->Id."</a></td></tr>";
	}
	$Content .= "<tr><th>Name</th><td><a href='group.php?groupid=".$objGroup->Id."'>".$objGroup->Name."</a></td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objGroup->Description)."</td></tr>";
	
	$Content .= "<tr><th>Owned by</th><td>";
	if (!is_null($objUser->PictureOf)) {
		$Content .= '<img height = "30" src="image.php?Id='.$objUser->PictureOf.'" /><br/>';
	}
	$Content .= $objUser->Name."</td></tr>";

	$Content .= "<tr><th>Publish?</th><td>";
	switch ($objGroup->Publish){
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