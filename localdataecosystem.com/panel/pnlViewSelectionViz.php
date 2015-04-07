<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsView.php');
require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../function/funVisualize.php');

function pnlViewSelectionViz( $objViewSelection, $Page=null,$objSelectionFilter = null){
	
	if (is_null($Page)){
		return;
	}
	
	global $System;
	if (!isset($Sysytem)){
		$System = new clsSystem;
	}	
	
	global $Views;
	if (!isset($Views)){
		$Views = new clsViews;
	}
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}
	
	$objViewClass = $objViewSelection->ViewClass;
	
	$Content = "";
	$Content .= "<div class='sdgreybox'>";
	
	$objSubjects = new clsSubjects();
	$objSubjects->Filter = $objSelectionFilter;
	$objSubjects->ViewSelection = $objViewSelection;
	
	$SubjectIds = $objSubjects->getClass($objViewClass->Class->DictId, $objViewClass->Class->Id);
	$Content .= funVisualize($SubjectIds, $Page);
	$Content .= "</div>";
		

	return $Content;

}

?>