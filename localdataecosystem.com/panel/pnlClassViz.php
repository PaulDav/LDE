<?php

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlClassViz( $DictId=null, $ClassId=null){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
		
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}
	
	if (!($objClass = $Dicts->getClass($DictId, $ClassId))){
		throw new Exception("Unknown Class");
	}
	
	if (is_null($objClass->Viz)){
		return;
	}
	$objViz = $objClass->Viz;
		
	$objDict = $Dicts->Dictionaries[$objClass->DictId];
	
	$Content = '';
	

	if (!isset($System->Config->Visualizers[$objViz->TypeId])){
		return;
	}
	
	$objVizType = $System->Config->Visualizers[$objViz->TypeId];
	

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Type</th><td>".$objVizType->Name."</td></tr>";
	$Content .= "<tr><td colspan='2'><h3>Parameters</h3></td></tr>";
	$ParamNum = 0;
	foreach ($objVizType->Params as $objVizTypeParam){
		$ParamNum = $ParamNum + 1;
		$Content .= "<tr><th>".$objVizTypeParam->Name."</th><td>";
		if (isset($objViz->Params[$ParamNum])){
			$objParam = $objViz->Params[$ParamNum];
			if ($objProp = $Dicts->getProperty($objParam->PropDictId,$objParam->PropId )){
				$Content .= $objProp->Label;
			}
		}
		$Content .= "</td></tr>";
	}
	
    $Content .= '</table>';
	    
    return $Content;
}


?>