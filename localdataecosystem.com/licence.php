<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");

	require_once("panel/pnlLicence.php");	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlSetPurpose.php");

	require_once("class/clsRights.php");
	require_once("class/clsData.php");
	require_once("class/clsShape.php");
	require_once("class/clsLibrary.php");
	require_once("class/clsArchive.php");
	
	define('PAGE_NAME', 'licence');

	session_start();
		
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
			
	try {

		$Licences = new clsLicences();
		$Defs = new clsDefinitions();
		$Orgs = new clsOrganisations();
		$Sets = new clsSets();
		$Shapes = new clsShapes();
		$Archive = new clsArchive();
						
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$LicenceId = null;
		$Version = null;
		$SetId = null;
		
		$objLicence = null;
		$objSet = null;
		
		$Name = null;		
		$Description = null;

		if (isset($_REQUEST['licenceid'])){
			$LicenceId = $_REQUEST['licenceid'];
		}
		if (isset($_REQUEST['version'])){
			$Version = $_REQUEST['version'];
		}
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
			$objSet = $Sets->getItem($SetId);
			if (!is_object($objSet)){
				throw new exception('Unknown Set');
			}
		}
		
		switch ($Mode){
			case 'new':
				if (is_null($SetId)) {
					throw new exception("SetId not specified");
				}
				
				break;
			default:
				if (is_null($LicenceId)) {
					throw new exception("LicenceId not specified");
				}
				
				break;
		}
		
		if (!empty($LicenceId)){
			$objLicence = $Licences->getItem($LicenceId, $Version);

			$Name = $objLicence->Name;
			$Description = $objLicence->Description;
			
			if (current($objLicence->SetIds)){			
				$SetId = current($objLicence->SetIds);
			}
			
		}		

		if ($System->Session->Error){

			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}

		if (!is_null($SetId)){
			$objSet = new clsSet($SetId);
			if (!isset($Orgs->Items[$objSet->OrgId])){
				throw new exception("Unknown Organisation");
			}
			$objControllerOrg = $Orgs->Items[$objSet->OrgId];		
			if ($objControllerOrg->canView === false){
				throw new exception("You cannot view this Organisation");
			}
		}
		
		$Page->Title = $Mode." licence";
		$PanelB .= "<h1>".$Page->Title."</h1>";
				
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objLicence->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if (!is_null($objSet)){
					if ($objSet->canControl){
						$ModeOk = true;
					}
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
				$PanelB .= pnlLicence( $LicenceId, $Version );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objLicence->canEdit === true){
					$PanelB .= "<li><a href='licence.php?licenceid=$LicenceId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objLicence->canControl === true){
					$PanelB .= "<li><a href='licence.php?licenceid=$LicenceId&mode=delete'>&bull; delete</a></li> ";
				}
											    
				$Tabs .= "<li><a href='#sets'>Sets";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='sets'>";
				
				$TabContent .= "<h3>Sets</h3>";
				$TabContent .= "<table class='list'/>";
				$TabContent .= '<thead><tr><th>Name</th><th>Context</th><th>Licence Type</th><th>Organisation</th><th>Shape</th><th>Purpose</th></tr></thead><thead>';
					
				foreach ($objLicence->SetIds as $SetId){
					$objSet = $Sets->getItem($SetId);
					if (is_object($objSet)){
						
						$num = $num + 1;
						
						$TabContent .= '<tr>';
						$TabContent .= '<td>';
						$TabContent .= "<a href='set.php?setid=".$objSet->Id."'>".$objSet->Name."</a>";
						$TabContent .= '</td>';
						
						$TabContent .= '<td>';
						$TabContent .= $objSet->Context->Name;
						$TabContent .= '</td>';

						$TabContent .= '<td>';
						$TabContent .= $objSet->LicenceTypeText;
						$TabContent .= '</td>';
						
						$TabContent .= '<td>';
						if (!is_null($objSet->OrgId)){
							$objSetOrg = $Orgs->getItem($objSet->OrgId);
							if (is_object($objSetOrg)){
								$TabContent .= $objSetOrg->Name;
							}
						}
						$TabContent .= '</td>';

						
						$TabContent .= '<td>';
						foreach ($objSet->SetShapes as $objSetShape){
							$objShape = $Shapes->getItem($objSetShape->ShapeId);
							if (is_object($objShape)){
								$TabContent .= $objShape->Name.'<br/>';
							}
						}
						$TabContent .= '</td>';
						

						$TabContent .= '<td>';
						foreach ($objSet->SetPurposes as $objSetPurpose){
							$objPurpose = $Defs->getItem($objSetPurpose->PurposeId);
							if (is_object($objPurpose)){
								$TabContent .= $objPurpose->Name.'<br/>';
							}
						}
						$TabContent .= '</td>';
						
						$TabContent .= '</tr>';
					}
				}					
					
				$TabContent .= "</tbody></table>";
								
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";

			    $Tabs .= "<li><a href='#orgs'>Organisations";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='orgs'>";
			    
			    $TabContent .= "<h3>Organisations</h3>";
			    
			    if ($objLicence->canEdit === true){
					$TabContent .= "<a href='licenceorg.php?licenceid=$LicenceId&mode=new'>&bull; add Organisation</a><br/><br/>";
				}
			    
				$TabContent .= "<table class='list'/>";
				$TabContent .= '<thead><tr><th>Name</th><th>Description</th></tr></thead><thead>';
					
				foreach ($objLicence->OrgIds as $OrgId){
					$objOrg = $Orgs->getItem($OrgId);
					if (is_object($objOrg)){
						
						$num = $num + 1;
						
						$TabContent .= '<tr>';
						$TabContent .= '<td>';
						$TabContent .= "<a href='licenceorg.php?licenceid=$LicenceId&orgid=".$objOrg->Id."'>".$objOrg->Name."</a>";
						$TabContent .= '</td>';
						
						$TabContent .= '<td>';
						$TabContent .= $objOrg->Description;
						$TabContent .= '</td>';
						
						$TabContent .= '</tr>';
					}
				}					
					
				$TabContent .= "</tbody></table>";
				
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
				foreach ($System->Config->LicenceDefs as $optLicenceDef){
					$optDefType = $System->Config->DefTypes[$optLicenceDef->DefTypeId];
				
					$Tabs .= "<li><a href='#".$optDefType->Heading."'>".$optDefType->Heading;
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='".$optDefType->Heading."'>";
					
					$TabContent .= "<h3>".$optDefType->Name."</h3>";
					
					if ($objLicence->canEdit){					
						$TabContent .= "<a href='licencedef.php?mode=new&licenceid=$LicenceId&deftypeid=".$optDefType->Id."'>&bull; add</a><br/>";
					}					

					$arrDefs = array();
					foreach ($objLicence->DefIds as $DefId){
						if (isset($Defs->Items[$DefId])){
							$objDef = $Defs->Items[$DefId];
							if ($objDef->TypeId == $optDefType->Id){
								$arrDefs[$DefId] = $objDef;
							}
						}
					}
					
					if (count($arrDefs) > 0){
						$TabContent .= "<table class='list'><thead><tr><th>Name</th><th>Description</th></tr></thead><tbody>";
						foreach ($arrDefs as $DefId=>$objDef){
							$num = $num + 1;
							$TabContent .= "<tr>";
							$TabContent .= "<td><a href='licencedef.php?licenceid=$LicenceId&defid=$DefId'>".$objDef->Name."</a></td>";
							$TabContent .= "<td>".nl2br(truncate($objDef->Description))."</td>";
							$TabContent .= "</tr>";
						}	
						$TabContent .= "</tbody></table>";
					}
										
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";
				}
			    
				$Tabs .= "<li><a href='#log'>Changes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='log'>";
				
				$TabContent .= "<h3>Log of Changes</h3>";

				$TabContent .= "<table class='list'><thead><tr><th>Version</th><th>Date/Time</th></tr></thead><tbody>";
				
				foreach ($Archive->getItems('licence', $objLicence->Id) as $objArchiveItem){
					$num = $num+1;
					
					$TabContent .= "<tr>";
					$TabContent .= "<td><a href='licence.php?licenceid=$LicenceId&version=".$objArchiveItem->Version."'>".$objArchiveItem->Version."</a></td>";
					$TabContent .= "<td>".convertDate($objArchiveItem->DateTime)."</td>";
					$TabContent .= "</tr>";
				}
				$TabContent .= "</tbody></table>";
				
				
				$TabContent .= "</div>";
				$Tabs .= "($num)</a></li>";				
				
				break;
			case 'new':
			case 'edit':
				
				$PanelB .= '<form method="post" action="doLicence.php">';
			
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					
				$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
					
				if (!is_null($LicenceId)){
					$PanelB .= "<input type='hidden' name='licenceid' value='$LicenceId'/>";
				}

				
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $LicenceId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Name';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="name" size="50" maxlength="100" value="'.$Name.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Description';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<textarea rows = "5" cols = "80" name="description" >';
					$PanelB .= $Description;
					$PanelB .= '</textarea>';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
										
			 	$PanelB .= '</table>';
				
				
				switch ( $Mode ){
					case "new":
						$PanelB .= '<input type="submit" value="Add Licence">';
						break;
					case "edit":
						$PanelB .= '<input type="submit" value="Update Licence">';
						break;
				}

				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlLicence( $LicenceId, $Version );
				
				$PanelB .= "<a href='doLicence.php?licenceid=$LicenceId&mode=delete'>confirm delete Licence?</a><br/>";
				
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