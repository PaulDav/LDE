<?php

require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsDict.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlProfileClass( $ProfileId, $ProfileClassId){
	
	Global $Dicts;
	if (!isset($Dicts)){
		$Dicts = new clsDicts();
	}

	$Content = '';
	
	$objProfile = new clsProfile($ProfileId);
	
	$objProfileClass = $objProfile->Classes[$ProfileClassId];
	$DictId = $objProfileClass->DictId;
	$ClassId = $objProfileClass->ClassId;
	if ($objClass = $Dicts->getClass($DictId,$ClassId)){
			
	
		$Content .= '<table class="sdgreybox">';
		$Content .= "<tr><th>Id</th><td><a href='profileclass.php?profileid=$ProfileId&profileclassid=$ProfileClassId'>$ProfileClassId</a></td></tr>";
		
		$Content .= "<tr><th>Class</th><td><a href=class.php?dictid=$DictId&classid=$ClassId>".$objClass->Label."</a></td></tr>";
	
		$Content .= "<tr><th>Create?</th><td>";
		switch ($objProfileClass->Create){
			case true:
				$Content .= "yes";
				break;
			default:
				$Content .= "no";
				break;
				
		}
		$Content .= "</td></tr>";
	
		$Content .= "<tr><th>Select?</th><td>";
		switch ($objProfileClass->Select){
			case true:
				$Content .= "yes";
				break;
			default:
				$Content .= "no";
				break;
				
		}
		$Content .= "</td></tr>";
		
		
	    $Content .= '</table>';
	}
	    
    return $Content;
}


?>