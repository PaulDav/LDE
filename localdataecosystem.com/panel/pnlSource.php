<?php

require_once(dirname(__FILE__).'/../class/clsLibrary.php');
require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlSource( $SourceId){
	
	global $Sources;
	if (!isset($Sources)){
		$Sources = new clsSources();
	}
	
	if (!isset($Sources->Items[$SourceId])){
		throw new Exception("Unknown Source");
	}
	$objSource = $Sources->Items[$SourceId];
	
	
	$objUser = null;
	if (!is_null($objSource->UserId)){
		$objUser = new clsUser($objSource->UserId);
	}
	
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='source.php?sourceid=".$objSource->Id."'>".$SourceId."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objSource->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objSource->Description)."</td></tr>";
	$Content .= "<tr><th>URL</th><td>".make_links($objSource->URL)."</td></tr>";
	
	if (is_object($objUser)){
		$Content .= "<tr><th>By</th><td>";
		if (!is_null($objUser->PictureOf)) {
			$Content .= '<img height = "30" src="image.php?Id='.$objUser->PictureOf.'" /><br/>';
		}
		$Content .= $objUser->Name."</td></tr>";
	}
	
	
    $Content .= '</table>';
	    
    return $Content;
}


?>