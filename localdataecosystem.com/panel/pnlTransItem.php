<?php

require_once(dirname(__FILE__).'/../class/clsProfile.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlTransItem( $SpecId, $TransId, $ItemId){
	
	global $Specs;
	if (!isset($Specs)){
		$Specs = new clsSpecs();
	}

	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Spec");
	}
	$objSpec = $Specs->Items[$SpecId];
	
	if (!isset($objSpec->Translations[$TransId])){
		throw new exception("Unknown Translation");
	}
	$objTrans = $objSpec->Translations[$TransId];

	if (!isset($objTrans->Items[$ItemId])){
		throw new exception("Unknown Item");
	}
	$objItem = $objTrans->Items[$ItemId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='transitem.php?specid=$SpecId&transid=$TransId&itemid=$ItemId'>".$objItem->Id."</a></td></tr>";
	$Content .= "<tr><th>From Value</th><td>".$objItem->FromValue."</td></tr>";
	$Content .= "<tr><th>To Value</th><td>".$objItem->ToValue."</td></tr>";
	
    $Content .= '</table>';
	    
    return $Content;
}


?>