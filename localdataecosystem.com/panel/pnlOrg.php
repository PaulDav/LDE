<?php

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlOrg( $OrgId){
	
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrgs();
	}
	
	if (!isset($Orgs->Items[$OrgId])){
		throw new Exception("Unknown Organisation");
	}
	$objOrg = $Orgs->Items[$OrgId];
	
	
	$objUser = null;
	if (!is_null($objOrg->UserId)){
		$objUser = new clsUser($objOrg->UserId);
	}
	
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='organisation.php?orgid=".$objOrg->Id."'>".$OrgId."</a></td></tr>";
	$Content .= "<tr><th>Name</th><td>".$objOrg->Name."</td></tr>";
	$Content .= "<tr><th>Description</th><td>".nl2br($objOrg->Description)."</td></tr>";
	$Content .= "<tr><th>WebSite</th><td>".make_links($objOrg->WebSite)."</td></tr>";
	$Content .= "<tr><th>URI</th><td>".make_links($objOrg->URI)."</td></tr>";
	
    $Content .= '</table>';
	    
    return $Content;
}


?>