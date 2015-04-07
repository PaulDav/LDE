<?php

require_once(dirname(__FILE__).'/../class/clsProfile.php');
require_once(dirname(__FILE__).'/../class/clsGroup.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once('pnlProfile.php');

Function pnlSpecMap( $SpecId, $objForm = null ){

	global $Specs;
	if (!isset($Specs)){
		$objSpecs = new clsSpecs();
	}
		
	global $Profiles;
	if (!isset($Profiles)){
		$objProfiles = new clsProfiles();
	}
		
	global $Dicts;
	if (!isset($Dicts)){
		$objDicts = new clsDicts();
	}
	
	if (!isset($Specs->Items[$SpecId])){
		throw new exception("Unknown Specification");
	}
	$objSpec = $Specs->Items[$SpecId];
	
	$objProfile = null;
	
	if (!is_null($objSpec->ProfileId)){
		if (!isset($Profiles->Items[$objSpec->ProfileId])){
			throw new exception("Unknown Profile");
		}
		$objProfile = $Profiles->Items[$objSpec->ProfileId];		
	}
		
	$Content = "";
	
	if (is_object($objProfile)){
		
		if (is_null($objForm)){
			$objForm = new clsForm($objProfile->Id);
		}
							
		$objClass = $objForm->Class;
		$Content .= "<h4>".$objClass->Label."</h4>";
		
		$Content .= "<table class='sdbluebox'>";
		
		$Content .= "<thead><tr><th/><th>Field Num</th><th>Default</th><th>Translation</th></tr></thead>";
	
		foreach ($objForm->FormFields as $FieldNum=>$arrFields){
			if (isset($arrFields[1])){
				$objFormField = $arrFields[1];
	
				$FieldName = $objFormField->FieldName;
				
				$objProp = $objFormField->Property;
				$xmlField = $objSpec->Specs->xpath->query("spec:Fields/spec:Field[@name='$FieldName']",$objSpec->xml)->item(0);
				
				$Content .= "<tr>";
				$Content .= "<th>".$objProp->Label."</th>";
				
				$Content .= "<td>";
				if (is_object($xmlField)){
					$Content .= $xmlField->getAttribute("col");
				}
				$Content .= "</td>";
				
				$Content .= "<td>";
				if (is_object($xmlField)){
					$Content .= $xmlField->getAttribute("default");
				}
				$Content .= "</td>";
				
				$Content .= "<td>";
				if (is_object($xmlField)){
					$TransId = $xmlField->getAttribute("translation");
					if (isset($objSpec->Translations[$TransId])){
						$Content .= $objSpec->Translations[$TransId]->Name;
					}
				}
				$Content .= "</td>";
				
				
				$Content .= "</tr>";
			}
		}
			
		$Content .= "</table>";

		$Content .= "<table>";		
		
		foreach ($objForm->LinkForms as $ProfileRelId=>$arrLinks){
			foreach ($arrLinks as $seq=>$objLinkForm){
				if ($seq == 0){
//					if ($objLinkForm->Cardinality == 'extend'){
						$Content .= "<tr><th>".$objLinkForm->Relationship->Label."</th>";
						$Content .= "<td>".pnlSpecMap($SpecId, $objLinkForm->ObjectForm)."</td>";			
						$Content .= "</tr>";
//					}
				}
			}			
		}
		
	
		$Content .= "</table>";

	}
		
	return $Content;
								
}
	

?>