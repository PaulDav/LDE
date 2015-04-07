<?php

require_once(dirname(__FILE__).'/../class/clsData.php');
require_once('pnlDef.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlSetPurpose( $SetId, $SetPurposeId){

	$Content = '';
	
	$objSet = new clsSet($SetId);
	if (isset($objSet->SetPurposes[$SetPurposeId])){
		$objSetPurpose = $objSet->SetPurposes[$SetPurposeId];
	
		$Content .= '<table class="sdgreybox">';
		$Content .= "<tr><th>Id</th><td><a href='setpurpose.php?setid=$SetId&setpurposeid=$SetPurposeId'>".$SetPurposeId."</a></td></tr>";
		$Content .= "<tr><th>Purpose</th><td>".pnlDef($objSetPurpose->PurposeId)."</td></tr>";	
	    $Content .= '</table>';
	}
	    
    return $Content;
}


?>