<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlDef.php");
	require_once("panel/pnlLicence.php");
	require_once("panel/pnlLicenceDef.php");
	
	require_once("class/clsRights.php");
	require_once("class/clsLibrary.php");
	
	define('PAGE_NAME', 'licencedef');

	session_start();
		
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
			
	try {

		$Licences = new clsLicences();
		$Defs = new clsDefinitions();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$LicenceId = null;
		$DefTypeId = null;
		$DefTypeLabel = 'Definition';
		$DefId = null;
		
		if (isset($_REQUEST['licenceid'])){
			$LicenceId = $_REQUEST['licenceid'];
		}

		if (isset($_REQUEST['deftypeid'])){
			$DefTypeId = $_REQUEST['deftypeid'];
		}
		
		if (isset($_REQUEST['defid'])){
			$DefId = $_REQUEST['defid'];
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

				if (isset($Licence->DefIds[$DefId])){
					throw new exception("Definition is already on the licence");
				}
				
				break;
			default:
				if (is_null($DefId)) {
					throw new exception("DefId not specified");
				}

				if (isset($Licence->DefIds[$DefId])){
					throw new exception("Definition is not on the licence");
				}

				if (isset($Defs->Items[$DefId])){
					$DefTypeId = $Defs->Items[$DefId]->TypeId;
				}
				
				break;
		}

		if ($System->Session->Error){

			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}

		if (!is_null($DefTypeId)){
			if (isset($System->Config->DefTypes[$DefTypeId])){
				$DefTypeLabel = $System->Config->DefTypes[$DefTypeId]->Name;
			}
		}
				
		$Page->Title = $Mode." $DefTypeLabel on a Licence";
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
				
				$PanelB .= pnlLicenceDef( $LicenceId, $DefId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objLicence->canControl === true){
					$PanelB .= "<li><a href='licencedef.php?licenceid=$LicenceId&defid=$DefId&mode=delete'>&bull; delete</a></li> ";
				}

				break;
			case 'new':
				
				$PanelB .= pnlLicence($LicenceId)."<br/>";
					
				$PanelB .= '<form method="post" action="doLicenceDef.php">';
			
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					
				$PanelB .= "<input type='hidden' name='licenceid' value='$LicenceId'/>";
					
				$PanelB .= "<table class='sdbluebox'>";
				$PanelB .= "<tr><th>$DefTypeLabel</th><td>";
				
				$PanelB .= "<select name='defid'>";
				$PanelB .= "<option/>";
				foreach ($Defs->Items as $optDef){
					if ($optDef->TypeId == $DefTypeId){					
						$PanelB .= "<option value='".$optDef->Id."'>".$optDef->Name."</option>";
					}
				}
				$PanelB .= "</select>";
				$PanelB .= "</th></tr>";
				$PanelB .= "</table>";

				switch ( $Mode ){
					case "new":
						$PanelB .= "<input type='submit' value='Add $DefTypeLabel to the Licence'>";
						break;
				}
	
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlLicenceDef( $LicenceId, $DefId );
				
				$PanelB .= "<a href='doLicenceDef.php?licenceid=$LicenceId&defid=$DefId&mode=delete'>confirm remove $DefTypeLabel from the licence?</a><br/>";
	
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