<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlClass( $DictId, $ClassId){
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	$objDict = $Dicts->Dictionaries[$DictId];
	if (!isset($objDict->Classes[$ClassId])){
		throw new Exception("Unknown Class");
	}
	$objClass = $objDict->Classes[$ClassId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	
	if (!is_null($objDict->EcoSystem)){
		$Content .= "<tr><th>EcoSystem</th><td>".$objDict->EcoSystem."</td></tr>";
	}
	
	$Content .= "<tr><th>Id</th><td><a href='class.php?dictid=".$objDict->Id."&classid=$ClassId'>".$ClassId."</a></td></tr>";
	$Content .= "<tr><th>Label</th><td>".$objClass->Label."</td></tr>";
	$Content .= "<tr><th>Concept</th><td>".strtoupper($objClass->Concept)."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objClass->Description)."</td></tr>";
	$Content .= "<tr><th>Heading</th><td>".$objClass->Heading."</td></tr>";
	$Content .= "<tr><th>Source</th><td>".nl2br(make_links($objClass->Source))."</td></tr>";
	
	if (!is_null($objClass->SubClassOf)){
		$objSuperDict = $objDict;
		if (!(is_null($objClass->SubDictOf))){
			if (!($objClass->SubDictOf == $DictId)){
				$objSuperDict = $Dicts->Dictionaries[$objClass->SubDictOf];
			}
		}
		if (isset($objSuperDict->Classes[$objClass->SubClassOf])){
			$objSuperClass = $objSuperDict->Classes[$objClass->SubClassOf];
			$Content .= "<tr><th>Sub Class of</th><td>".pnlClass($objSuperClass->DictId, $objSuperClass->Id)."</td></tr>";			
		}
	}	
	
	
    $Content .= '</table>';
	    
    return $Content;
}


?>