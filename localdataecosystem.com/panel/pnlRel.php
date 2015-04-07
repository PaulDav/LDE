<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../panel/pnlClass.php');


require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlRel( $DictId, $RelId, $FieldName = null, $ReturnURL = null){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!isset($objDict->Relationships[$RelId])){
		throw new Exception("Unknown Relationship");
	}
	$objRel = $objDict->Relationships[$RelId];
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='relationship.php?dictid=".$objDict->Id."&relid=$RelId'>".$RelId."</a></td></tr>";
	
	$objSubjectClass = $Dicts->Dictionaries[$objRel->SubjectDictId]->Classes[$objRel->SubjectId];	
	$Content .= "<tr><th>Subject</th><td><a href='class.php?dictid=".$objSubjectClass->DictId."&classid=".$objSubjectClass->Id."'>".$objSubjectClass->Label."</a><br/>";
	
		$Content .= '<table>';
		$Content .= '<tr><th>Extending?</th><td>';
		if ($objRel->Extending === true){
			$Content .= "&#10003";
		}
		$Content .= '</td></tr>';
		
		$Content .= "<tr><th>Label</th><td>".$objRel->Label."</td></tr>";
		
		$Content .= '</table>';
	
	$Content .= '</td></tr>';

	$objObjectClass = $Dicts->Dictionaries[$objRel->ObjectDictId]->Classes[$objRel->ObjectId];	
	$Content .= "<tr><th>Object</th><td><a href='class.php?dictid=".$objObjectClass->DictId."&classid=".$objObjectClass->Id."'>".$objObjectClass->Label."</a><br/>";
	
		$Content .= '<table>';
		$Content .= '<tr><th>Extending?</th><td>';
		if ($objRel->InverseExtending === true){
			$Content .= "&#10003";
		}
		$Content .= '</td></tr>';
		
		$Content .= "<tr><th>Label</th><td>".$objRel->InverseLabel."</td></tr>";
		
		$Content .= '</table>';
	
	$Content .= '</td></tr>';
	
		
	$Content .= "<tr><th>Concept Relationship</th><td>".$objRel->ConceptRelationship."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objRel->Description)."</td></tr>";

	$Content .= "<tr><th>Cardinality</th><td>".$objRel->Cardinality."</td></tr>";

	
	$Content .= "</td></tr>";
	
	
    $Content .= '</table>';
    return $Content;
}


?>