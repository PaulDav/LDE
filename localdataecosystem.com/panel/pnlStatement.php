<?php

require_once(dirname(__FILE__).'/../class/clsData.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');


Function pnlStatement( $StatId){
	
	$objStat = new clsStatement($StatId);
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='statement.php?statid=".$objStat->Id."'>".$objStat->Id."</a></td></tr>";
	$Content .= "<tr><th>Subject Id</th><td>".pnlStatementSubject($objStat->SubjectId)."</td></tr>";
	
	if (!is_null($objStat->AboutId)){
		$Content .= "<tr><th>About Statement</th><td><a href='statement.php?statid=".$objStat->AboutId."'>".$objStat->AboutId."</a></td></tr>";
	}
	
	$Content .= "<tr><th>".$objStat->TypeLabel."</th><td>".pnlStatementLink($objStat->LinkDictId, $objStat->LinkId, $objStat->TypeId)."</td></tr>";

	if (!is_null($objStat->ObjectId)){
		$Content .= "<tr><th>Object Id</th><td>".pnlStatementSubject($objStat->ObjectId)."</td></tr>";
	}

	if (!is_null($objStat->Value)){
		$Content .= "<tr><th>Value</th><td>".$objStat->Value."</td></tr>";
	}
	
	$Content .= "<tr><th>Effective From</th><td>".$objStat->EffectiveFrom."</td></tr>";
	$Content .= "<tr><th>Effective To</th><td>".$objStat->EffectiveTo."</td></tr>";
	
		
    $Content .= '</table>';
	    
    return $Content;
}

function pnlStatementSubject($SubjectId){

	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
		
	$Content = "<table><tr>";
	$Content .=	"<td><a href='subject.php?subjectid=$SubjectId'>$SubjectId</a></td>";

	$objSubject = new clsSubject($SubjectId);
	
	$Class = $Dicts->getItem($objSubject->ClassDictId, $objSubject->ClassId, 100);

	$Content .= "<td>".$Class->Label."</td>";
	$Content .= "<td>".$objSubject->Identifier."</td>";
	$Content .= "<td>".$objSubject->Name."</td>";
	$Content .= "</tr></table>";
		
	return $Content;
	
}


function pnlStatementDates($StatId){

	$Content = "<table><tr>";
	$Content .=	"<th>Effective at</th><td></td>";
	$Content .= "</tr></table>";
		
	return $Content;
	
}


function pnlStatementLink($DictId, $Id, $TypeId){

	$Content = '';
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}

	$objDictItem = $Dicts->getItem($DictId, $Id, $TypeId);	
	if (is_object($objDictItem)){
		$Content = "<table><tr>";
		$Content .=	"<td>".$objDictItem->Label."</td>";
		$Content .= "</tr></table>";
	}
		
	return $Content;
	
}

?>