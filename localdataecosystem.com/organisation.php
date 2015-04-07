<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsRights.php");
	require_once("class/clsLibrary.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
		
	define('PAGE_NAME', 'organisation');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	try {

		$Orgs = new clsOrganisations();
		$Defs = new clsDefinitions();
		$Licences = new clsLicences();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$OrgId = '';
		$Name = null;
		$Description = '';
		$URI = '';
		$WebSite = '';
		
		$objOrg = null;
		
		
		if (isset($_REQUEST['orgid'])){
			$OrgId = $_REQUEST['orgid'];
			if (!isset($Orgs->Items[$OrgId])){
				throw new exception("Unknown Organisation");
			}
			$objOrg = $Orgs->Items[$OrgId];
		}

		switch ($Mode){
			case 'new':
				break;
			default:
				if (is_null($OrgId)) {
					throw new exception("OrgId not specified");
				}
				break;
		}

		if (!empty($OrgId)){
			$Name = $objOrg->Name;
			$Description = $objOrg->Description;
			$URI = $objOrg->URI;
			$WebSite = $objOrg->WebSite;
		}		
		
		
		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		$Page->Title = $Mode." organisation";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objOrg->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($Orgs->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objOrg->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objOrg->canControl){
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
				$PanelB .= pnlOrg( $OrgId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objOrg->canEdit === true){
					$PanelB .= "<li><a href='organisation.php?orgid=$OrgId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objOrg->canControl === true){
					$PanelB .= "<li><a href='organisation.php?orgid=$OrgId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";

				$Tabs .= "<li><a href='#sets'>Datasets";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='sets'>";

					$TabContent .= "<div><h3>Datasets</h3></div>";
					
//					if ($objGroup->canEdit === true){					
						$TabContent .= "<a href='set.php?mode=new&orgid=$OrgId'>&bull; add</a><br/>";
//					}

					if (count($objOrg->SetIds) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Name</th><th>Status</th><th>Context</th><th>Licence Type</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objOrg->SetIds as $SetId){
							$num = $num + 1;
							
							$objSet = new clsSet($SetId);
							$TabContent .= "<tr>";
														
							$TabContent .= "<td><a href='set.php?setid=".$objSet->Id."'>".$objSet->Id."</a></td>";
							$TabContent .= "<td>".$objSet->Name."</td>";
							
							$TabContent .= "<td>".$objSet->StatusText."</td>";
							$TabContent .= "<td>".$objSet->Context->Name."</td>";
							$TabContent .= "<td>".$objSet->LicenceTypeText."</td>";
							
							$TabContent .= "</tr>";
							
						}

				 		$TabContent .= '</table>';
					}
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";
								
				$Tabs .= "<li><a href='#licences'>Licences";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='licences'>";

				$TabContent .= "<div><h3>Licences</h3></div>";
				
				$TabContent .= "<table class='list'><thead><tr><th>Id</th><th>Name</th><th>Description</th><th>Terms Met?</th></tr></thead><tbody>";

				foreach ($objOrg->OrgLicences as $objOrgLicence){
					$num = $num + 1;
					
					$TabContent .= "<tr><td><a href='licence.php?licenceid=".$objOrgLicence->Licence->Id."'>".$objOrgLicence->Licence->Id."</a></td>";
					$TabContent .= "<td>".$objOrgLicence->Licence->Name."</td>";					
					$TabContent .= "<td>".nl2br(truncate($objOrgLicence->Licence->Description))."</td>";
					$TabContent .= "<td>";
					switch ($objOrgLicence->TermsMet){
						case true:
							$TabContent .= 'yes';
							break;
						default:
							$TabContent .= 'no';
							break;
					}
					$TabContent .= "</td>";
					
					
					$TabContent .= "</tr>";
					
					
				}	
				$TabContent .= "</tbody></table>";
				
							
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";
				
			    $Tabs .= "<li><a href='#roles'>Roles";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='roles'>";

				$TabContent .= "<div><h3>Roles</h3></div>";
				
				if ($objOrg->canControl){
					$TabContent .= "<a href='role.php?mode=new&orgid=$OrgId'>&bull; add</a><br/>";
				}
				
				$TabContent .= "<table class='list'><thead><tr><th>Id</th><th>Name</th><th>Description</th></tr></thead><tbody>";

				foreach ($objOrg->Roles as $objRole){
					$num = $num + 1;

					$TabContent .= "<tr><td><a href='role.php?orgid=$OrgId&roleid=".$objRole->Id."'>".$objRole->Id."</a></td>";
					$TabContent .= "<td>".$objRole->Name."</td>";					
					$TabContent .= "<td>".nl2br(truncate($objRole->Description))."</td>";
					$TabContent .= "</tr>";

				}	
				$TabContent .= "</tbody></table>";
				
							
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";

			    
			    			    
			    $Tabs .= "<li><a href='#users'>Users";
				$num = 0;

				$TabContent .= "<div class='tabContent hide' id='users'>";

				$TabContent .= "<div><h3>Users</h3></div>";
				
				if ($objOrg->canControl){
					$TabContent .= "<a href='orguserrole.php?mode=new&orgid=$OrgId'>&bull; add</a><br/>";
				}
				
				$TabContent .= "<table class='list'><thead><tr><th/><th>Name</th><th>email</th><th>Roles</th><th>Start</th><th>End</th></tr></thead><tbody>";

				foreach ($objOrg->Users as $OrgUserId=>$OrgUser){
					try {
						$objUser = new clsUser($OrgUserId);

						$num = $num + 1;

						$RowSpan = count($OrgUser->UserRoles) + 1;
						$TabContent .= "<tr>";
						
						$TabContent .= "<td  rowspan='$RowSpan'>";						
						if (!is_null($objUser->PictureOf)) {
							$TabContent .= "<img height = '30' src='image.php?Id=".$objUser->PictureOf."' /><br/>";
						}
						$TabContent .= "</td>";
						$TabContent .= "<td rowspan='$RowSpan'>".$objUser->Name."</td>";
						$TabContent .= "<td rowspan='$RowSpan'>".$objUser->Email."</td>";
						
						$TabContent .= "</tr>";

						foreach ($OrgUser->UserRoles as $objUserRole){
							$RoleId = $objUserRole->RoleId;
							if (isset($objOrg->Roles[$RoleId])){
								$objRole = $objOrg->Roles[$RoleId];
							
								$TabContent .= "<tr rowspan='RowSpan'>";

								$TabContent .= "<td><a href='orguserrole.php?orgid=$OrgId&orguserroleid=".$objUserRole->Id."'>".$objRole->Name."</a></td>";

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

					catch (Exception $e) {
					    unset($e);
					}										
					
				}	
				$TabContent .= "</tbody></table>";
				
							
				$TabContent .= "</div>";

				if ($num > 0 ){
	    			$Tabs .= "($num)";							
				}
			    $Tabs .= "</a></li>";
			    
			    
			    
				foreach ($System->Config->OrgDefs as $optOrgDef){
					$optDefType = $System->Config->DefTypes[$optOrgDef->DefTypeId];
				
					$Tabs .= "<li><a href='#".$optDefType->Heading."'>".$optDefType->Heading;
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='".$optDefType->Heading."'>";
					
					
					$TabContent .= "<h3>".$optDefType->Name."</h3>";
					
					if ($System->LoggedOn === true){					
						$TabContent .= "<a href='orgdef.php?mode=new&orgid=$OrgId&deftypeid=".$optDefType->Id."'>&bull; add</a><br/>";
					}					

					$arrOrgDefs = array();
					foreach ($objOrg->HasDefs as $objOrgDef){
						if ($objOrgDef->DefTypeId == $optDefType->Id){
							$arrOrgDefs[] = $objOrgDef;
						}
					}
					
					if (count($arrOrgDefs) > 0){
						$TabContent .= "<table class='list'><thead><tr><th>Id</th><th>From</th><th>To</th><th>Name</th><th>Description</th></tr></thead><tbody>";
						foreach ($arrOrgDefs as $objOrgDef){
							if (isset($Defs->Items[$objOrgDef->DefId])){
								$objDef = $Defs->Items[$objOrgDef->DefId];
								$num = $num + 1;
								$TabContent .= "<tr><td><a href='orgdef.php?orgid=$OrgId&orgdefid=".$objOrgDef->Id."'>".$objOrgDef->Id."</a></td>";
								$TabContent .= "<td>".$objOrgDef->DateFrom."</td><td>".$objOrgDef->DateTo."</td><td>".$objDef->Name."</td><td>".nl2br(truncate($objDef->Description))."</td></tr>";
							}
						}	
						$TabContent .= "</tbody></table>";
					}
					
					
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";
				}
				break;
			case 'new':
			case 'edit':
				
				$PanelB .= "<div class='sdbluebox'>";
				
				$PanelB .= '<form method="post" action="doOrganisation.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!is_null($OrgId)){
					$PanelB .= "<input type='hidden' name='orgid' value='$OrgId'/>";
				}

				$PanelB .= "<table>";
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $OrgId;
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
					$PanelB .= 'Web Site';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<input type='text' name='website' size='100' maxlength='400' value='$WebSite'/>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'URI';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<input type='text' name='uri' size='100' maxlength='400' value='$URI'/>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
			 	$PanelB .= '</table>';
				
				switch ( $Mode ){
					case "new":
						$PanelB .= '<input type="submit" value="Create New Organisation">';
						break;
					case "edit":
						$PanelB .= '<input type="submit" value="Update Organisation">';
						break;
				}

				$PanelB .= '</form>';
				$PanelB .= "</div>";

				break;
				
			case 'delete':
				
				$PanelB .= pnlOrg( $OrgId );

				$PanelB .= "<a href='doOrganisation.php?orgid=$OrgId&mode=delete'>confirm delete?</a><br/>";
				
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