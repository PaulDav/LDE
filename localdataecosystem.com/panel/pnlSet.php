<?php

require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlSet( $objSet){
	
	if (is_null($objSet)){
		return;
	}
	
	$Content = '';
	
	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='set.php?setid=".$objSet->Id."'>".$objSet->Id."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objSet->Name."</td></tr>";
	$Content .= "<tr><th>Source</th><td>".$objSet->Source."</td></tr>";
	
	$Content .= "<tr><th>Status</th><td>".$objSet->StatusText."</td></tr>";
	$Content .= "<tr><th>Context</th><td>".$objSet->Context->Name."</td></tr>";
	$Content .= "<tr><th>Licence Type</th><td>".$objSet->LicenceTypeText."</td></tr>";
	
    $Content .= '</table>';
	    
    return $Content;
}


?>