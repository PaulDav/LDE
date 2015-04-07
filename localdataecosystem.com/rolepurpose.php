<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlRole.php");
	require_once("panel/pnlRolePurpose.php");
	
	require_once("class/clsLibrary.php");
	
	define('PAGE_NAME', 'rolepurpose');

	session_start();
	
	$System = new clsSystem();

	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
	
	try {

		$Defs = new clsDefinitions();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";

		$OrgId = null;
		$RoleId = null;		
		$RolePurposeId = null;
		$PurposeId = null;

		if (isset($_REQUEST['rolepurposeid'])){
			$RolePurposeId = $_REQUEST['rolepurposeid'];
		}

		if (isset($_REQUEST['orgid'])){
			$OrgId = $_REQUEST['orgid'];
		}
		
		if (isset($_REQUEST['roleid'])){
			$RoleId = $_REQUEST['roleid'];
		}

		if (isset($_REQUEST['purposeid'])){
			$PurposeId = $_REQUEST['purposeid'];
		}

		if (IsEmptyString($OrgId)) {
			throw new exception("OrgId not specified");
		}
		if (IsEmptyString($RoleId)) {
			throw new exception("RoleId not specified");
		}
		
		$objOrg = $Orgs->getItem($OrgId);
		if (!is_object($objOrg)){
			throw new exception("Unknown Organsiation");
		}
		if (!isset($objOrg->Roles[$RoleId])){
			throw new exception("Unknown Role");
		}
		$objRole = $objOrg->Roles[$RoleId];
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if (IsEmptyString($RolePurposeId)) {
					throw new exception("RolePurposeId not specified");
				}

				break;
		}

		if (!empty($RolePurposeId)){
			if (!isset($objRole->RolePurposes[$RolePurposeId])){
				throw new exception("Unknown Role Purpose Id");
			}
			$objRolePurpose = $objRole->RolePurposes[$RolePurposeId];
			$PurposeId = $objRolePurpose->PurposeId;
		}		

		if ($System->Session->Error){

			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		$Page->Title = $Mode." role purpose";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objRole->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objRole->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objRole->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objRole->canEdit){
					$ModeOk = true;
				}
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}
		
		switch ($Mode){
			case 'view':

				$PanelB .= pnlRolePurpose( $OrgId, $RoleId, $RolePurposeId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objRole->canControl === true){
//					$PanelB .= "<li><a href='rolepurpose.php?orgid=$OrgId&roleid=$RoleId&rolepurposeid=$RolePurposeId&mode=edit'>&bull; edit</a></li> ";
					$PanelB .= "<li><a href='rolepurpose.php?orgid=$OrgId&roleid=$RoleId&rolepurposeid=$RolePurposeId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$Tabs .= "<li><a href='#role'>Role";
				$TabContent .= "<div class='tabContent hide' id='role'>";
					$TabContent .= "<h3>for Role</h3>";	
					$TabContent .= pnlRole($OrgId, $RoleId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
				$TabContent .= "</div>";

				break;
			case 'new':
			case 'edit':
				
				if (is_null($PurposeId)){
					
					$PanelB .= "<h3>Set the Purpose from any of these selections</h3>";	
			
					$optTabs = "";
					$optTabContent = "";
										
					$optTabs .= "<li><a href='#allpurposes'>All Purposes</a></li>";
					$optTabContent .= funSelectPurpose("all");
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}

				}
				else
				{
					
					$PanelB .= pnlDef($PurposeId)."<br/>";
					
					$PanelB .= '<form method="post" action="doRolePurpose.php">';
			
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					
					$PanelB .= "<input type='hidden' name='orgid' value='$OrgId'/>";
					$PanelB .= "<input type='hidden' name='roleid' value='$RoleId'/>";					
					$PanelB .= "<input type='hidden' name='purposeid' value='$PurposeId'/>";
					
					if (!IsEmptyString($RolePurposeId)){
						$PanelB .= "<input type='hidden' name='rolepurposeid' value='$RolePurposeId'/>";
					}											
						
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Add Purpose to the Role">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Purpose on the Role">';
							break;
					}
					
					$PanelB .= '</form>';
				}

				break;
				
			case 'delete':
				
				$PanelB .= pnlRolePurpose( $OrgId, $RoleId, $RolePurposeId );
				
				$PanelB .= "<a href='doRolePurpose.php?orgid=$OrgId&roleid=$RoleId&rolepurposeid=$RolePurposeId&mode=delete'>confirm remove purpose from role?</a><br/>";
				
				break;
				
		}
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}
		
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();


function funSelectPurpose($Selection){

	global $System;
	
	global $Defs;
	
	$Content = "";
				
	$TabId = "";
	$TabId = $Selection."purposes";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$PurposeFieldName = "purposeid";

	$opts = array();

	switch ($Selection){
			
		case "all":
			
			foreach ($Defs->Items as $optDef){
							
				if (!($optDef->TypeId == 30)){
					continue;
				}				

				$opts[$optDef->Id] = $optDef;
			}
			break;
			
	}
		
		
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Id</th><th>Name</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		
		foreach ($opts as $optDefId=>$optDef){

			$UrlParams = array();
			$UrlParams[$PurposeFieldName] = $optDef->Id;
			$ReturnUrl = UpdateUrl($UrlParams);
						
			$Content .= "<tr>";
			$Content .= "<tr><td><a href='$ReturnUrl'>".$optDef->Id."</a></td>";
			$Content .= "<td>".$optDef->Name."</td>";
			$Content .= "<td>".nl2br($optDef->Description)."</td>";
			$Content .= "</tr>";
			
		}
		$Content .= "</tbody>";
		$Content .= "</table>";
		
	}
		
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}

	
	
?>