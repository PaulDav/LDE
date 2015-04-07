<?php

require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../panel/pnlList.php');


require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlTranslation.php');

Function pnlTranslation( $SpecId, $TransId){
	
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
		
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='translation.php?specid=$SpecId&transid=$TransId'>".$objTrans->Id."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objTrans->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objTrans->Description)."</td></tr>";
	
	if (!is_null($objTrans->List)){
		$Content .= "<tr><th>Uses List</th><td>".pnlList($objTrans->List->DictId,$objTrans->List->Id )."</td></tr>";
	}
	
    $Content .= '</table>';
	    
    return $Content;
}


?>