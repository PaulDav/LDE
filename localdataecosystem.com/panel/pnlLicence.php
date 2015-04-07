<?php

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlLicence( $LicenceId, $Version=null){
	
	global $Licences;
	if (!isset($Licences)){
		$Licences = new clsLicences();
	}
	
	$objLicence = $Licences->getItem($LicenceId, $Version);
	if (!is_object($objLicence)){
		throw new Exception("Unknown Licence");
	}
		
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='licence.php?licenceid=".$objLicence->Id."'>".$LicenceId."</a></td></tr>";
	$Content .= "<tr><th>Version</th><td>".$objLicence->Version."</td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objLicence->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objLicence->Description)."</td></tr>";
	
    $Content .= '</table>';
    
    return $Content;
}


?>