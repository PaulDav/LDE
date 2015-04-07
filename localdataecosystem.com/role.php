<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsRights.php");
	require_once("class/clsLibrary.php");
	require_once("class/clsArchive.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlRole.php");
	
	define('PAGE_NAME', 'role');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();
	
	$Script = "\n";
	
	$Script .= "<link rel='stylesheet' href='//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css'>";
  	$Script .= "<script src='//code.jquery.com/jquery-1.10.2.js'></script>";
  	$Script .= "<script src='//code.jquery.com/ui/1.10.4/jquery-ui.js'></script>";
	
	$Script .= "<script type='text/javascript'><!--
	
  $(function() {
   $('input').filter('.datepicker').datepicker({ dateFormat: 'dd/mm/yy' });
  });
 --></script>\n";
	
	
	$Page->Script .= $Script;
	
	$Name = null;
	$Description = null;

	try {

		$Defs = new clsDefinitions();
		$Orgs = new clsOrganisations();
		$Archive = new clsArchive();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";

		$OrgId = null;
		$RoleId = '';
		
		$objOrg = null;
		$objRole = null;
		
		if (isset($_REQUEST['orgid'])){
			$OrgId = $_REQUEST['orgid'];
		}
		
		if (is_null($OrgId)){
			throw new exception("Org Id Not Specified");
		}
		
		
		if (!isset($Orgs->Items[$OrgId])){
			throw new exception("Unknown Organisation");
		}
		$objOrg = $Orgs->Items[$OrgId];
				
		if (isset($_REQUEST['roleid'])){
			$RoleId = $_REQUEST['roleid'];
			if (!isset($objOrg->Roles[$RoleId])){
				throw new exception("Role is not on this Organisation");
			}
			$objRole = $objOrg->Roles[$RoleId];
		}

		
		switch ($Mode){
			case 'new':
				
				break;
			default:
				if (is_null($OrgId)) {
					throw new exception("OrgId not specified");
				}
				if (is_null($RoleId)) {
					throw new exception("RoleId not specified");
				}
				
				break;
		}

		
		if (!is_null($objRole)){
			$Name = $objRole->Name;
			$Description = $objRole->Description;
		}		

		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		
		$Page->Title = $Mode." organisation role";		
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
				$PanelB .= pnlRole( $OrgId, $RoleId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objOrg->canEdit === true){
					$PanelB .= "<li><a href='role.php?orgid=$OrgId&roleid=$RoleId&mode=edit'>&bull; edit</a></li> ";
					$PanelB .= "<li><a href='role.php?orgid=$OrgId&roleid=$RoleId&mode=delete'>&bull; delete</a></li> ";
				}
				$PanelB .= "</ul></div>";				
				
				
				$Tabs .= "<li><a href='#purposes'>Purposes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='purposes'>";
					$TabContent .= "<h3>Purposes</h3>";	

					if (count($objRole->RolePurposes) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Name</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objRole->RolePurposes as $objRolePurpose){
							$PurposeId = $objRolePurpose->PurposeId;
							if (isset($Defs->Items[$PurposeId])){
								$objDef = $Defs->Items[$PurposeId];
								$num = $num + 1;
								
								$TabContent .= "<tr>";
								
								$TabContent .= "<td><a href='rolepurpose.php?orgid=$OrgId&roleid=$RoleId&rolepurposeid=".$objRolePurpose->Id."'>".$objRolePurpose->Id."</a></td>";
								
								$TabContent .= "<td>".$objDef->Name."</td>";							
								$TabContent .= "<td>".nl2br(Truncate($objDef->Description))."</td>";
								
								$TabContent .= "</tr>";
							}
							
						}
						$TabContent .= '</table>';
					}

					if ($objRole->canEdit === true){
						$TabContent .= "<br/><a href='rolepurpose.php?orgid=$OrgId&roleid=$RoleId&mode=new'>&bull; add Purpose</a>";
					}

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
						
							if ($objUserRole->RoleId == $RoleId){
								$useUser = true;
								
								try{
									$objUser = new clsUser($objUserRole->UserId);
								}
								catch (Exception $e) {
									$objUser = null;
									$useUser = false;
								    unset($e);
								}

								if ($useUser){
						
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
			case 'new':
			case 'edit':

				$PanelB .= "<div class='sdbluebox'>";

				$PanelB .= '<form method="post" action="doRole.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				
				if (!is_null($OrgId)){
					$PanelB .= "<input type='hidden' name='orgid' value='$OrgId'/>";
				}

				if (!is_null($RoleId)){
					$PanelB .= "<input type='hidden' name='roleid' value='$RoleId'/>";
				}


				$PanelB .= "<table>";
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $RoleId;
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
				
								
			 	$PanelB .= '</table>';
				
				switch ( $Mode ){
					case "new":
						$PanelB .= "<input type='submit' value='Create New Role'/>";
						break;
					case "edit":
						$PanelB .= "<input type='submit' value='Update Role'/>";
						break;
				}

				$PanelB .= '</form>';
				$PanelB .= "</div>";

				break;
				
			case 'delete':
				
				$PanelB .= pnlRole( $OrgId, $RoleId );
				
				$PanelB .= "<a href='doRole.php?orgid=$OrgId&roleid=$RoleId&mode=delete'>confirm delete?</a><br/>";

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