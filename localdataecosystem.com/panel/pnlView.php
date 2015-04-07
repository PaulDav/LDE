<?php

require_once(dirname(__FILE__).'/../class/clsView.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlView( $ViewId){
	
	global $Profiles;
	if (isset($Views)){
		$objView = $Views->Items[$ViewId];
	}
	else
	{
		$objView = new clsView($ViewId);
	}
	
	$User = new clsUser($objView->OwnerId);
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='view.php?viewid=".$objView->Id."'>".$objView->Id."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objView->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objView->Description)."</td></tr>";
	
	$Content .= "<tr><th>Owned by</th><td>";
	if (!is_null($User->PictureOf)) {
		$Content .= '<img height = "30" src="image.php?Id='.$User->PictureOf.'" /><br/>';
	}
	$Content .= $User->Name."</td></tr>";

	$Content .= "<tr><th>Publish?</th><td>";
	switch ($objView->Publish){
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