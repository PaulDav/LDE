<?php

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../function/utils.inc');

require_once("panel/pnlOrg.php");
require_once("panel/pnlLicence.php");


Function pnlLicenceOrg( $LicenceId, $OrgId){
	
	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences();
	}
	
	$objLicence = $Licences->getItem($LicenceId);
	if (!is_object($objLicence)){
		throw new Exception("Unknown Licence");
	}

	if (!isset($objLicence->OrgIds[$OrgId])){
		throw new exception("Organisation is not on the Licence");
	}
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Licence</th><td>".pnlLicence($LicenceId)."</td></tr>";
	$Content .= "<tr><th>for Organisation</th><td>".pnlOrg($OrgId)."</td></tr>";
	
    $Content .= '</table>';
    
    return $Content;
}


?>