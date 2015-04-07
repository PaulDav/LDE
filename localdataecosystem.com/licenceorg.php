<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlLicence.php");
	require_once("panel/pnlLicenceOrg.php");
	
	require_once("class/clsRights.php");
	
	define('PAGE_NAME', 'licenceorg');

	session_start();
		
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
			
	try {

		$Licences = new clsLicences();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$LicenceId = null;
		$OrgId = null;
		
		if (isset($_REQUEST['licenceid'])){
			$LicenceId = $_REQUEST['licenceid'];
		}

		if (isset($_REQUEST['orgid'])){
			$OrgId = $_REQUEST['orgid'];
		}

		if (is_null($LicenceId)) {
			throw new exception("LicenceId not specified");
		}

		$objLicence = $Licences->getItem($LicenceId);
		if (!is_object($objLicence)){
			throw new exception("Unknown Licence");
		}

		switch ($Mode){
			case 'new':

				if (isset($Licence->OrgIds[$OrgId])){
					throw new exception("Organisation is already on the licence");
				}
				
				break;
			default:
				if (is_null($OrgId)) {
					throw new exception("OrgId not specified");
				}

				if (isset($Licence->OrgIds[$OrgId])){
					throw new exception("Organisation is not on the licence");
				}
				
				break;
		}

		if ($System->Session->Error){

			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}

				
		$Page->Title = $Mode." Organisation on a Licence";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objLicence->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objLicence->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objLicence->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objLicence->canEdit){
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
				
				$PanelB .= pnlLicenceOrg( $LicenceId, $OrgId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objLicence->canControl === true){
					$PanelB .= "<li><a href='licenceorg.php?licenceid=$LicenceId&orgid=$OrgId&mode=delete'>&bull; delete</a></li> ";
				}

				break;
			case 'new':
				
				$PanelB .= pnlLicence($LicenceId)."<br/>";
					
				$PanelB .= '<form method="post" action="doLicenceOrg.php">';
			
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					
				$PanelB .= "<input type='hidden' name='licenceid' value='$LicenceId'/>";
					
				$PanelB .= "<table class='sdbluebox'>";
				$PanelB .= "<tr><th>Organisation</th><td>";
				
				$PanelB .= "<select name='orgid'>";
				$PanelB .= "<option/>";
				foreach ($Orgs->Items as $optOrg){
					$PanelB .= "<option value='".$optOrg->Id."'>".$optOrg->Name."</option>";
				}
				$PanelB .= "</select>";
				$PanelB .= "</th></tr>";
				$PanelB .= "</table>";

				switch ( $Mode ){
					case "new":
						$PanelB .= '<input type="submit" value="Add Organisation to the Licence">';
						break;
						break;
				}
	
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlLicenceOrg( $LicenceId, $OrgId );
				
				$PanelB .= "<a href='doLicenceOrg.php?licenceid=$LicenceId&orgid=$OrgId&mode=delete'>confirm remove organisation from the licence?</a><br/>";
	
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

	
?>