<?php

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../function/utils.inc');
require_once('pnlOrg.php');

Function pnlRole( $OrgId, $RoleId){
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs();
	}
	
	if (!isset($Orgs->Items[$OrgId])){
		throw new Exception("Unknown Organisation");
	}
	$objOrg = $Orgs->Items[$OrgId];

	if (!isset($objOrg->Roles[$RoleId])){
		throw new Exception("Unknown Role");
	}
	$objRole = $objOrg->Roles[$RoleId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='role.php?orgid=$OrgId&roleid=$RoleId'>".$RoleId."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objRole->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objRole->Description)."</td></tr>";
	$Content .= "<tr><th>of Organisation</th><td>".pnlOrg($OrgId)."</td></tr>";
	
    $Content .= '</table>';
	    
    return $Content;
}


?>