<?php

require_once('pnlDef.php');

require_once(dirname(__FILE__).'/../function/utils.inc');
require_once(dirname(__FILE__).'/../class/clsRights.php');

Function pnlRolePurpose( $OrgId, $RoleId, $RolePurposeId){

	$Content = '';
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrganisations();
	}
	
	$objOrg = $Orgs->getItem($OrgId);
	if (!is_object($objOrg)){
		throw new exception("Unknown OrgId");
	}
	if (!isset($objOrg->Roles[$RoleId])){
		throw new exception("Unknown RoleId");
	}	
	$objRole = $objOrg->Roles[$RoleId];
	
	if (isset($objRole->RolePurposes[$RolePurposeId])){
		$objRolePurpose = $objRole->RolePurposes[$RolePurposeId];
	
		$Content .= '<table class="sdgreybox">';
		$Content .= "<tr><th>Id</th><td><a href='rolepurpose.php?orgid=$OrgId&roleid=$RoleId&rolepurposeid=$RolePurposeId'>".$RolePurposeId."</a></td></tr>";
		$Content .= "<tr><th>Purpose</th><td>".pnlDef($objRolePurpose->PurposeId)."</td></tr>";	
	    $Content .= '</table>';
	}
	    
    return $Content;
}


?>