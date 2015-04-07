<?php

require_once(dirname(__FILE__).'/../class/clsLibrary.php');
require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlDef( $DefId){
	
	global $Sources;
	if (!isset($Sources)){
		$Sources = new clsSources();
	}

	global $Defs;
	if (!isset($Defs)){
		$Defs = new clsDefinitions();
	}
	
	
	if (!isset($Defs->Items[$DefId])){
		throw new Exception("Unknown Definition");
	}
	$objDef = $Defs->Items[$DefId];
		
	$objUser = null;
	if (!is_null($objDef->UserId)){
		$objUser = new clsUser($objDef->UserId);
	}
	
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='definition.php?defid=".$objDef->Id."'>".$DefId."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objDef->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objDef->Description)."</td></tr>";
	$Content .= "<tr><th>URL</th><td>".make_links($objDef->URL)."</td></tr>";
	
	if (is_object($objUser)){
		$Content .= "<tr><th>By</th><td>";
		if (!is_null($objUser->PictureOf)) {
			$Content .= '<img height = "30" src="image.php?Id='.$objUser->PictureOf.'" /><br/>';
		}
		$Content .= $objUser->Name."</td></tr>";
	}
	
	$Content .= "<tr><th>Source</th><td>";
	if (isset($Sources->Items[$objDef->SourceId])){
		$objSource = $Sources->Items[$objDef->SourceId];
		$Content .= "<a href='source.php?sourceid=".$objSource->Id."'>".$objSource->Name."</a>";
	}
	$Content .= "</td></tr>";
	
    $Content .= '</table>';
    
    return $Content;
}


?>