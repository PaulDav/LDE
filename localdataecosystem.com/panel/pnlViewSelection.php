<?php

require_once(dirname(__FILE__).'/../class/clsView.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');
require_once(dirname(__FILE__).'/../class/clsDict.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlViewSelection( $objSel){

	$Content = "";

	if (!isset($objSel->ViewClass)){
		return;
	}
	$objViewClass = $objSel->ViewClass;
	
	
	$Content .= "<div class='sdgreybox'>";
	$Content .= pnlViewClass($objViewClass);
	$Content .= "</div>";
	
	return $Content;
}



Function pnlViewClass( $objViewClass){

	$Content = "";
	
	if (!is_object($objViewClass)){
		return;
	}
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
		
	$objClass = $objViewClass->Class;
	if (!is_object($objClass)){
		return;
	}
	
	$Content .= "<table>";
	$Content .= "<tr><th>Class</th><td>".$objClass->Label."</td></tr>";	
	$Content .= "</table>";
	
	$Content .= "<div class='tab'>";
	$Content .= "<h4>Properties</h4>";	
	
	$Content .= "<table>";
	$Content .= "<tr><th/><th>Selected?</th><th>Filter</th></tr>";
	
	foreach ($Dicts->ClassProperties($objViewClass->Class->DictId, $objViewClass->Class->Id) as $objClassProperty){
		
		$objViewProp = null;
		foreach ($objViewClass->ViewProperties as $optViewProperty){
			if ($optViewProperty->Property->DictId == $objClassProperty->PropDictId){
				if ($optViewProperty->Property->Id == $objClassProperty->PropId){
					$objViewProp = $optViewProperty;
					continue;
				}
				
			}
		}
						
		$objProp = $Dicts->Dictionaries[$objClassProperty->PropDictId]->Properties[$objClassProperty->PropId];
		$Content .= "<tr>";
		$Content .= "<td>".$objProp->Label."</td>";
		
		$Content .= "<td>";		
		if (!is_null($objViewProp)){
			if ($objViewProp->Selected === true){
				$Content .= "&#10003";
			}
		}		
		$Content .= "</td>";

		$Content .= "<td>";		
		if (!is_null($objViewProp)){
			foreach ($objViewProp->Filters as $objFilter){
				$Content .= $objFilter->Type.' '.$objFilter->Value.' <br/>';
			}
		}		
		$Content .= "</td>";
		
		
		
		$Content .= "</tr>";		
	}
	$Content .= "</table>";
	
	if (count($objViewClass->ViewLinks) > 0){
		$Content .= "<h4>Links</h4>";
		
		foreach ($objViewClass->ViewLinks as $objViewLink){
			$Content .= "<div class='tab'>";
		 	$Content .= "<table>";
			$Content .= "<tr><th>Relationship</th><td>".$objViewLink->Relationship->Label."</td></tr>";	
			$Content .= "</table>";
			$Content .= "<div class='tab'>";
			$Content .= pnlViewClass($objViewLink->ViewObject);
			$Content .= "</div>";
			$Content .= "</div>";
		}
	}
	
		
	$Content .= "</div>";
	
	return $Content;
}


?>