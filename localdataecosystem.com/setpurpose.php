<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlSetPurpose.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsData.php");
	require_once("class/clsLibrary.php");
	
	define('PAGE_NAME', 'setpurpose');

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
		
		$SetPurposeId = null;
		$SetId = null;
		$PurposeId = null;

		if (isset($_REQUEST['setpurposeid'])){
			$SetPurposeId = $_REQUEST['setpurposeid'];
		}
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}

		if (isset($_REQUEST['purposeid'])){
			$PurposeId = $_REQUEST['purposeid'];
		}

		if (IsEmptyString($SetId)) {
			throw new exception("SetId not specified");
		}
		
		$objSet = new clsSet($SetId);
		
		switch ($Mode){
			case 'new':
				
				break;
			default:
				if (IsEmptyString($SetPurposeId)) {
					throw new exception("SetPurposeId not specified");
				}

				break;
		}

		if (!empty($SetPurposeId)){
			if (!isset($objSet->SetPurposes[$SetPurposeId])){
				throw new exception("Unknown SetPurposeId");
			}
			$objSetPurpose = $objSet->SetPurposes[$SetPurposeId];
			$PurposeId = $objSetPurpose->PurposeId;
		}		


		if ($System->Session->Error){

			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}

		$objSet = new clsSet($SetId);
		
		if (!isset($Orgs->Items[$objSet->OrgId])){
			throw new exception("Unknown Organisation");
		}
		$objOrg = $Orgs->Items[$objSet->OrgId];		
		if ($objOrg->canView === false){
			throw new exception("You cannot view this Organisation");
		}
		
		$Page->Title = $Mode." purpose for set";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objSet->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objSet->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objSet->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objSet->canEdit){
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
				
				$PanelB .= pnlSetPurpose( $SetId, $SetPurposeId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSet->canEdit === true){
					$PanelB .= "<li><a href='setpurpose.php?setid=$SetId&setpurposeid=$SetPurposeId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSet->canControl === true){
					$PanelB .= "<li><a href='setpurpose.php?setid=$SetId&setpurposeid=$SetPurposeId&mode=delete'>&bull; delete</a></li> ";
				}

				$Tabs .= "<li><a href='#org'>Organisation";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='org'>";
					$TabContent .= "<h3>by Organisation</h3>";	
					$TabContent .= pnlOrg($objSet->OrgId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

				$TabContent .= "</div>";

				$Tabs .= "<li><a href='#set'>Set";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='set'>";
					$TabContent .= "<h3>in Set</h3>";	
					$TabContent .= pnlSet($objSet);	
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
					
					$PanelB .= '<form method="post" action="doSetPurpose.php">';
			
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					
					$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
					$PanelB .= "<input type='hidden' name='purposeid' value='$PurposeId'/>";
					
					if (!IsEmptyString($SetPurposeId)){
						$PanelB .= "<input type='hidden' name='setpurposeid' value='$SetPurposeId'/>";
					}
											
						
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Add Purpose to the Set">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Purpose on the Set">';
							break;
					}
	
					$PanelB .= '</form>';
				}

				break;
				
			case 'delete':
				
				$PanelB .= pnlSetPurpose( $SetId, $SetPurposeId );
				
				$PanelB .= "<a href='doSetPurpose.php?setid=$SetId&setpurposeid=$SetPurposeId&mode=delete'>confirm remove purpose from set?</a><br/>";
				
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