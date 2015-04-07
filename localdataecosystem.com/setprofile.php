<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlSetProfile.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsData.php");
	require_once("class/clsProfile.php");
	
	define('PAGE_NAME', 'setprofile');

	session_start();
		
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

			
	try {

		$Profiles = new clsProfiles();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$SetProfileId = null;
		$SetId = null;
		$ProfileId = null;

		if (isset($_REQUEST['setprofileid'])){
			$SetProfileId = $_REQUEST['setprofileid'];
		}
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}

		if (isset($_REQUEST['profileid'])){
			$ProfileId = $_REQUEST['profileid'];
		}

		if (IsEmptyString($SetId)) {
			throw new exception("SetId not specified");
		}
		
		$objSet = new clsSet($SetId);
		
		switch ($Mode){
			case 'new':
				
				break;
			default:
				if (IsEmptyString($SetProfileId)) {
					throw new exception("SetProfileId not specified");
				}

				break;
		}

		if (!empty($SetProfileId)){
			if (!isset($objSet->SetProfiles[$SetProfileId])){
				throw new exception("Unknown SetProfileId");
			}
			$objSetProfile = $objSet->SetProfiles[$SetProfileId];
			$ProfileId = $objSetProfile->ProfileId;
		}		


		if ($System->Session->Error){
			if (isset($FormFields['profileid'])){
				$ProfileId = $FormFields['profileid'];
			}

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
		
		$Page->Title = $Mode." profile for set";
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
				
				$PanelB .= pnlSetProfile( $SetId, $SetProfileId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSet->canEdit === true){
					$PanelB .= "<li><a href='setprofile.php?setid=$SetId&setprofileid=$SetProfileId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSet->canControl === true){
					$PanelB .= "<li><a href='setprofile.php?setid=$SetId&setprofileid=$SetProfileId&mode=delete'>&bull; delete</a></li> ";
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
				
				if (is_null($ProfileId)){
					$PanelB .= "<h3>Set the Profile from any of these selections</h3>";	
			
					$optTabs = "";
					$optTabContent = "";
										
					$optTabs .= "<li><a href='#myprofiles'>Profiles in My Groups</a></li>";
					$optTabContent .= funSelectProfile("my");
										
					$optTabs .= "<li><a href='#publishedprofiles'>Published Profiles</a></li>";			    
					$optTabContent .= funSelectProfile("published");
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
				}
				else
				{
					
					$PanelB .= pnlProfile($ProfileId)."<br/>";
					
					$PanelB .= '<form method="post" action="doSetProfile.php">';
			
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					
					$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
					$PanelB .= "<input type='hidden' name='profileid' value='$ProfileId'/>";
					
					if (!IsEmptyString($SetProfileId)){
						$PanelB .= "<input type='hidden' name='setprofileid' value='$SetProfileId'/>";
					}
											
						
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Add Profile to the Set">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Profile on the Set">';
							break;
					}
	
					$PanelB .= '</form>';
				}

				break;
				
			case 'delete':
				
				$PanelB .= pnlSetProfile( $SetId, $SetProfileId );
				
				$PanelB .= "<a href='doSetProfile.php?setid=$SetId&setprofileid=$SetProfileId&mode=delete'>confirm remove profile from set?</a><br/>";
				
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


function funSelectProfile($Selection){

	global $System;
	
	global $Profiles;
	
	$Content = "";
				
	$TabId = "";
	$TabId = $Selection."profiles";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$ProfileFieldName = "profileid";

	$opts = array();

	switch ($Selection){
			
		case "my":
			
			foreach ($Profiles->Items as $optProfile){
							
				$optGroup = new clsGroup($optProfile->GroupId);
				if (!$optGroup->canEdit){
					continue;
				}				

				$opts[$optProfile->Id] = $optProfile;
			}
			break;
			
		case "published":
			
			foreach ($Profiles->Items as $optProfile){
			
				if (!$optProfile->Publish){
					continue;
				}
								
				$opts[$optProfile->Id] = $optProfile;
				
			}

			break;
			
	}
		
		
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Id</th><th>Name</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		
		foreach ($opts as $optProfileId=>$optProfile){

			$UrlParams = array();
			$UrlParams[$ProfileFieldName] = $optProfile->Id;
			$ReturnUrl = UpdateUrl($UrlParams);
						
			$Content .= "<tr>";
			$Content .= "<tr><td><a href='$ReturnUrl'>".$optProfile->Id."</a></td>";
			$Content .= "<td>".$optProfile->Name."</td>";
			$Content .= "<td>".nl2br($optProfile->Description)."</td>";
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