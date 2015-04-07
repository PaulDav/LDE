<?php

require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once('pnlRel.php');


require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlProfileRel( $ProfileId, $ProfileRelId){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objProfile = new clsProfile($ProfileId);
	
	$objProfileRel = $objProfile->Relationships[$ProfileRelId];
		
	$Content = '';
	
	$Content .= '<table class="sdgreybox">';
	
	$Content .= "<tr><th>Subject Class</th><td>";
	if (!is_null($objProfileRel->SubjectProfileClassId)){
		$Content .= pnlProfileClass($ProfileId, $objProfileRel->SubjectProfileClassId);
	}
	$Content .= "</td></tr>";
	
	$objRel = $Dicts->Dictionaries[$objProfileRel->DictId]->Relationships[$objProfileRel->RelId];
	$Content .= "<tr><th>Relationship</th><td>";
	switch ($objProfileRel->Inverse){
		case false:
			$Content .= $objRel->Label;
			break;
		default:
			$Content .= $objRel->InverseLabel;
			break;
	}
		
	$Content .= "</td></tr>";

	$Content .= "<tr><th>Object Class</th><td>";	
	if (!is_null($objProfileRel->ObjectProfileClassId)){
		$Content .= pnlProfileClass($ProfileId, $objProfileRel->ObjectProfileClassId);
	}
	$Content .= "</td></tr>";
	
	
    $Content .= '</table>';
	    
    return $Content;
}


?>