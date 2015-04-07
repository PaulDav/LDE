<?php

require_once(dirname(__FILE__).'/../class/clsRights.php');
require_once(dirname(__FILE__).'/../class/clsLibrary.php');

require_once('pnlDef.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlOrgDef( $OrgId, $OrgDefId){

	global $System;
	if (!isset($System)){
		$System = new clsSystem();
	}
		
	global $Orgs;
	if (!isset($Orgs)){
		$Orgs = new clsOrganisations();
	}

	global $Defs;
	if (!isset($Defs)){
		$Defs = new clsDefinitions();
	}
	
	
	if (!isset($Orgs->Items[$OrgId])){
		throw new Exception("Unknown Organisation");
	}
	$objOrg = $Orgs->Items[$OrgId];

	if (!isset($objOrg->HasDefs[$OrgDefId])){
		throw new Exception("Unknown Org Def");
	}
	$objOrgDef = $objOrg->HasDefs[$OrgDefId];
	
	$DefId = $objOrgDef->DefId;
	
	if (!isset($Defs->Items[$DefId])){
		throw new Exception("Unknown Definition");
	}
	$objDef = $Defs->Items[$DefId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='orgdef.php?orgid=".$objOrg->Id."&orgdefid=".$objOrgDef->Id."'>".$OrgDefId."</a></td></tr>";
	if (isset($System->Config->DefTypes[$objOrgDef->DefTypeId])){
		$Content .= "<tr><th>";
		$Content .= $System->Config->DefTypes[$objOrgDef->DefTypeId]->Name;
		$Content .= "</th><td>".pnlDef($objDef->Id)."</td><tr>";		
	}

	if (!is_null($objOrgDef->DateFrom)){
		$Content .= "<tr><th>Date From</th><td>".$objOrgDef->DateFrom."</td></tr>";
	}
	
	if (!is_null($objOrgDef->DateTo)){
		$Content .= "<tr><th>Date To</th><td>".$objOrgDef->DateTo."</td></tr>";
	}
	
	$Content .= "<tr><th>URL</th><td>".make_links($objOrgDef->URL)."</td></tr>";
	$Content .= "<tr><th>Reference</th><td>".make_links($objOrgDef->Reference)."</td></tr>";
		
    $Content .= '</table>';
    
    return $Content;
}


?>