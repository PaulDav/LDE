<?php

require_once(dirname(__FILE__).'/../class/clsSystem.php');
require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../class/clsLibrary.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

require_once("pnlDef.php");
require_once("pnlLicence.php");


Function pnlLicenceDef( $LicenceId, $DefId){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
	
	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences();
	}
	
	global $Defs;
	if (!isset($Defs)){
		$Defs = new clsDefinitions();
	}
	
	
	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new Exception("Unknown Licence");
	}

	if (!isset($objLicence->DefIds[$DefId])){
		throw new exception("Definition is not on the Licence");
	}

	$objDef = null;
	$DefTypeId = null;
	if (isset($Defs->Items[$DefId])){
		$objDef = $Defs->Items[$DefId];
		$DefTypeId = $objDef->TypeId;
	}

	$DefTypeLabel = 'Definition';
	if (!is_null($DefTypeId)){
		if (isset($System->Config->DefTypes[$DefTypeId])){
			$DefTypeLabel = $System->Config->DefTypes[$DefTypeId]->Name;
		}
	}
	
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Licence</th><td>".pnlLicence($LicenceId)."</td></tr>";
	$Content .= "<tr><th>$DefTypeLabel</th><td>".pnlDef($DefId)."</td></tr>";
	
    $Content .= '</table>';
    
    return $Content;
}


?>