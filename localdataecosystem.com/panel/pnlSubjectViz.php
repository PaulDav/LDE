<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsData.php');

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../function/funVisualize.php');

function pnlSubjectViz( $SubjectId, $Page=null){
	
	if (is_null($Page)){
		return;
	}
	
	global $System;
	if (!isset($Sysytem)){
		$System = new clsSystem;
	}	
	
	global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts;
	}
		
	$Content = "";
	
	$Content .= funVisualize(array($SubjectId), $Page);

	return $Content;

}

?>