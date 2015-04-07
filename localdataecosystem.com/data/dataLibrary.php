<?php

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsSystem.php');

require_once(dirname(__FILE__).'/../class/clsLibrary.php');


function dataSourceUpdate($Mode, $Id = null, $Name = null, $Description = null, $URL) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a library');
	}

	global $Sources;
	if (!isset($Sources)){
		$Sources = new clsSources;
	}
	if (!$Sources->canEdit){
		throw new exception("You cannot update the library");
	}
			
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Sources->xpath->query("/lib:Sources/lib:Source[@id]") as $xmlExistingSource){
					$ExistingId = $xmlExistingSource->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlSource = $Sources->dom->createElementNS($Sources->LibNamespace,"Source");
			$Sources->dom->documentElement->appendChild($xmlSource);
			$xmlSource->setAttribute("id",$Id);
			
			$xmlSource->setAttribute("userid",$System->User->Id);
			
			break;
		default:
			
			$xmlSource = $Sources->xpath->query("/lib:Sources/lib:Source[@id='$Id']")->item(0);
			if (!is_object($xmlSource)){
				throw new exception("Source does not exist");
			}
			
			break;
	}
						
	xmlSetElement($xmlSource, "Name", $Name);
	xmlSetElement($xmlSource, "Description", $Description);
	xmlSetElement($xmlSource, "URL", $URL);
	
	$Sources->Save();

	return $Id;

}  	

function dataSourceDelete($Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a library');
	}
	
	global $Sources;
	if (!isset($Sources)){
		$Sources = new clsSources;
	}
	if (!$Sources->canEdit){
		throw new exception("You cannot update the library");
	}
	
	$xmlSource = $Sources->xpath->query("/lib:Sources/lib:Source[@id='$Id']")->item(0);
	if (!is_object($xmlSource)){
		throw new Exception("Source does not exist");
	}

	$xmlSource->parentNode->removeChild($xmlSource);

	$Sources->Save();
	
}



function dataDefUpdate($Mode, $Id = null, $TypeId, $SourceId, $Name = null, $Description = null, $URL) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a library');
	}

	global $Sources;
	if (!isset($Defs)){
		$Defs = new clsDefinitions;
	}
	if (!$Defs->canEdit){
		throw new exception("You cannot update the library");
	}
			
	switch ($Mode) {
		case 'new':			
			if (is_null($Id)){
				$MaxId = 0;
				foreach ($Defs->xpath->query("/lib:Definitions/lib:Definition[@id]") as $xmlExistingDef){
					$ExistingId = $xmlExistingDef->getAttribute("id");
					if ($ExistingId > $MaxId){
						$MaxId = $ExistingId;
					}
				}
				$Id = $MaxId + 1;
			}

			$xmlDef = $Defs->dom->createElementNS($Defs->LibNamespace,"Definition");
			$Defs->dom->documentElement->appendChild($xmlDef);
			$xmlDef->setAttribute("id",$Id);
			$xmlDef->setAttribute("userid",$System->User->Id);
			
			break;
		default:
			
			$xmlDef = $Defs->xpath->query("/lib:Definitions/lib:Definition[@id='$Id']")->item(0);
			if (!is_object($xmlDef)){
				throw new exception("Definition does not exist");
			}
			
			break;
	}
	
	$xmlDef->setAttribute("typeid", $TypeId);
	$xmlDef->setAttribute("sourceid", $SourceId);
	
	
	xmlSetElement($xmlDef, "Name", $Name);
	xmlSetElement($xmlDef, "Description", $Description);
	xmlSetElement($xmlDef, "URL", $URL);
	
	$Defs->Save();

	return $Id;

}  	

function dataDefDelete($Id = null) {
	
	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	if (!$System->LoggedOn){
		throw new exception('You must be logged on to update a library');
	}
	
	global $Defs;
	if (!isset($Defs)){
		$Defs = new clsDefinitions;
	}
	if (!$Defs->canEdit){
		throw new exception("You cannot update the library");
	}
	
	$xmlDef = $Defs->xpath->query("/lib:Definitions/lib:Definition[@id='$Id']")->item(0);
	if (!is_object($xmlDef)){
		throw new Exception("Definition does not exist");
	}

	$xmlDef->parentNode->removeChild($xmlDef);

	$Defs->Save();
	
}



?>