<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsLibrary.php");
	require_once("class/clsRights.php");
	require_once("class/clsData.php");
	
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlDef.php");
		
	define('PAGE_NAME', 'definition');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	try {

		$Defs = new clsDefinitions();
		$Sources = new clsSources();
		$Orgs = new clsOrganisations();
		$Licences = new clsLicences();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$DefId = '';
		$Name = null;
		$DefTypeId = null;
		$Description = '';
		$URL = '';
		$SourceId = null;

		$objDef = null;
		
		
		if (isset($_REQUEST['defid'])){
			$DefId = $_REQUEST['defid'];
			if (!isset($Defs->Items[$DefId])){
				throw new exception("Unknown Definition");
			}
			$objDef = $Defs->Items[$DefId];
		}

		switch ($Mode){
			case 'new':
				
				if (isset($_REQUEST['deftypeid'])){
					$DefTypeId = $_REQUEST['deftypeid'];
				}
				
				break;
			default:
				if (is_null($DefId)) {
					throw new exception("DefId not specified");
				}
				break;
		}

		if (!empty($DefId)){
			$Name = $objDef->Name;
			$DefTypeId = $objDef->TypeId;
			$Description = $objDef->Description;
			$URL = $objDef->URL;
			$SourceId = $objDef->SourceId;
		}		

		
		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		
		if (is_null($DefTypeId)){
			throw new exception("Definition Type not specified");
		}		
		if (!isset($System->Config->DefTypes[$DefTypeId])){
			throw new exception("Invalid Definition Type");
		}
		$objDefType = $System->Config->DefTypes[$DefTypeId];
		
		
		$Page->Title = $Mode." ".$objDefType->Name;		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objDef->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($Defs->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objDef->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objDef->canControl){
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
				$PanelB .= pnlDef( $DefId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDef->canEdit === true){
					$PanelB .= "<li><a href='definition.php?defid=$DefId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDef->canControl === true){
					$PanelB .= "<li><a href='definition.php?defid=$DefId&mode=delete'>&bull; delete</a></li> ";
				}
				$PanelB .= "</ul></div>";

				
				switch ($objDef->TypeId){
					case 30: // Purpose

						$Tabs .= "<li><a href='#sets'>Data Sets";
						$num = 0;
						$TabContent .= "<div class='tabContent hide' id='sets'>";
						$TabContent .= "<h3>Data Sets</h3>";
						
						$TabContent .= "<table class='list'>";
						$TabContent .= "<thead><tr><th>Organisation</th><th>DataSet</th><th>Description</th></tr></thead><body>";

						$Sets = new clsSets();
						$Sets->PurposeId = $DefId;
						
						foreach ($Sets->Items as $optSet){

							if (isset($Orgs->Items[$optSet->OrgId])){
								$optOrg = $Orgs->Items[$optSet->OrgId];
							
								$num = $num + 1;
								$TabContent .= "<tr>";
								$TabContent .= "<td><a href='organisation.php?orgid=".$optOrg->Id."'>".$optOrg->Name."</a></td>";
								$TabContent .= "<td><a href='set.php?setid=".$optSet->Id."'>".$optSet->Name."</a></td>";
//								$TabContent .= "<td>".truncate(nl2br($optSet->Description))."</td>";
								$TabContent .= "</tr>";								
							}	
						}
						$TabContent .= "</tbody></table>";
						
						$TabContent .= "</div>";
					    $Tabs .= "($num)</a></li>";
				    
				    
					    $Tabs .= "<li><a href='#users'>Users";
						$num = 0;
						$TabContent .= "<div class='tabContent hide' id='users'>";
						$TabContent .= "<h3>Users</h3>";
						
						$TabContent .= "<table class='list'>";
						$TabContent .= "<thead><tr><th>User</th><th>Organisation</th><th>Role</th><th>Start Date</th><th>End Date</th></tr></thead><body>";
						
						foreach ($Orgs->Items as $objOrg){
							foreach ($objOrg->UserRoles as $objUserRole){
							
								if (isset($objOrg->Roles[$objUserRole->RoleId])){
									$objRole = $objOrg->Roles[$objUserRole->RoleId];

									$useRole = false;
									foreach ($objRole->RolePurposes as $objRolePurpose){
										if ($objRolePurpose->PurposeId == $DefId){
											$useRole = true;
											break;
										}
									}

									if ($useRole){
										try{
											$objUser = new clsUser($objUserRole->UserId);
										}
										catch (Exception $e) {
											$objUser = null;
											$useRole = false;
										    unset($e);
										}
									}

									if ($useRole){
							
										$num = $num + 1;
										$TabContent .= "<tr>";
										$TabContent .= "<td>";
										
										if (!is_null($objUser->PictureOf)) {
											$TabContent .= "<img height = '30' src='image.php?Id=".$objUser->PictureOf."' /><br/>";
										}
										$TabContent .= "<a href='orguserrole.php?orgid=".$objOrg->Id."&orguserroleid=".$objUserRole->Id."'>".$objUser->Name."</a></td>";
										$TabContent .= "<td><a href='organisation.php?orgid=".$objOrg->Id."'>".$objOrg->Name."</a></td>";
										$TabContent .= "<td><a href='role.php?orgid=".$objOrg->Id."&roleid=".$objRole->Id."'>".$objRole->Name."</a></td>";

										
										$TabContent .= "<td>";
										if (!is_null($objUserRole->StartDate)){
											$TabContent .= $objUserRole->StartDate->format('d/m/Y');
										}									
										$TabContent .= "</td>";
										$TabContent .= "<td>";
										if (!is_null($objUserRole->EndDate)){
											$TabContent .= $objUserRole->EndDate->format('d/m/Y');
										}									
										$TabContent .= "</td>";
										
										
										$TabContent .= "</tr>";								
									}
								}
							}	
						}
						$TabContent .= "</tbody></table>";
						
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";
				    
				    
										    
				    break;
				}
			    
				
				$Tabs .= "<li><a href='#lics'>Licenses";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='lics'>";
				
				$TabContent .= "<h3>Licenses</h3>";	

				$TabContent .= "<table class='list'>";
				$TabContent .= "<thead><tr><th>Id</th><th>Name</th><th>Description</th></tr></thead><body>";
				
				foreach ($Licences->Items as $objLicence){
					if (isset($objLicence->DefIds[$DefId])){
						$num = $num + 1;
						$TabContent .= "<tr><td><a href='licence.php?licenceid=".$objLicence->Id."'>".$objLicence->Id."</a></td><td>".$objLicence->Name."</td><td>".truncate(nl2br($objLicence->Id))."</td></tr>";						
					}
				}

				$TabContent .= "</tbody></table>";

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";


			    $showOrgs = false;
			    foreach ($System->Config->OrgDefs as $configOrgDef){
			    	if ($configOrgDef->DefTypeId == $DefTypeId ){
			    		$showOrgs = true;
			    	}
			    }

			    if ($showOrgs){
				    $Tabs .= "<li><a href='#orgs'>Organisations";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='orgs'>";
						$TabContent .= "<h3>Organisations</h3>";
						
						$TabContent .= "<table class='list'>";
						$TabContent .= "<thead><tr><th>Organisation</th><th>From</th><th>To</th></tr></thead><body>";
						
						foreach ($Orgs->Items as $optOrg){
							foreach ($optOrg->HasDefs as $optHasDef){
								if ($optHasDef->DefTypeId == $objDef->TypeId){
									$num = $num + 1;
									$TabContent .= "<tr>";
									$TabContent .= "<td><a href='organisation.php?orgid=".$optOrg->Id."'>".$optOrg->Name."</a></td>";
									$TabContent .= "<td>".$optHasDef->DateFrom."</td>";
									$TabContent .= "<td>".$optHasDef->DateTo."</td>";
									$TabContent .= "</tr>";								
								}
							}
						}
						
						$TabContent .= "</tbody></table>";
						
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";
			    }
			    
			    
				break;
			case 'new':
			case 'edit':
				
				$PanelB .= "<div class='sdbluebox'>";
				
				$PanelB .= '<form method="post" action="doDef.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!is_null($DefId)){
					$PanelB .= "<input type='hidden' name='defid' value='$DefId'/>";
				}
				
				if (!is_null($DefTypeId)){
					$PanelB .= "<input type='hidden' name='typeid' value='$DefTypeId'/>";
				}
				

				$PanelB .= "<table>";
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $DefId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Name';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="name" size="60" maxlength="100" value="'.$Name.'"/>';
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

				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'URL';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<input type='text' name='url' size='100' maxlength='400' value='$URL'/>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Source';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					
					$PanelB .= "<select name='sourceid'>";
					$PanelB .= "<option/>";
					foreach ($Sources->Items as $optSource){
						$PanelB .= "<option value='".$optSource->Id."'";
						if ($optSource->Id == $SourceId){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">".$optSource->Name."</option>";
					}
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
			 	$PanelB .= '</table>';
				
				switch ( $Mode ){
					case "new":
						$PanelB .= "<input type='submit' value='Create New ".$objDefType->Name."'/>";
						break;
					case "edit":
						$PanelB .= "<input type='submit' value='Update ".$objDefType->Name."'/>";
						break;
				}

				$PanelB .= '</form>';
				$PanelB .= "</div>";

				break;
				
			case 'delete':
				
				$PanelB .= pnlDef( $DefId );

				$PanelB .= "<a href='doDef.php?defid=$DefId&mode=delete'>confirm delete?</a><br/>";
				
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