<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsRights.php");
	require_once("class/clsUser.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrgUserRole.php");
	
	
	define('PAGE_NAME', 'orguserrole');

	session_start();
	
	$System = new clsSystem();

	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
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
	
	
	try {

		$Orgs = new clsOrganisations();
		$objUser = null;
		$objRole = null;
		$objOrgUserRole = null;
		
		$UserEmail = null;
		$StartDate = null;
		$EndDate = null;
		
		
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
		$UserId = null;
		$OrgUserRoleId = null;

		if (isset($_REQUEST['orguserroleid'])){
			$OrgUserRoleId = $_REQUEST['orguserroleid'];
		}

		if (isset($_REQUEST['orgid'])){
			$OrgId = $_REQUEST['orgid'];
		}
		
		if (isset($_REQUEST['roleid'])){
			$RoleId = $_REQUEST['roleid'];
		}

		if (isset($_REQUEST['userid'])){
			$UserId = $_REQUEST['userid'];
		}

		if (IsEmptyString($OrgId)) {
			throw new exception("OrgId not specified");
		}
		
		$objOrg = $Orgs->getItem($OrgId);
		if (!is_object($objOrg)){
			throw new exception("Unknown Organsiation");
		}
		
		
//		if (!isset($objOrg->Roles[$RoleId])){
//			throw new exception("Unknown Role");
//		}
//		$objRole = $objOrg->Roles[$RoleId];

		
		if (!empty($OrgUserRoleId)){
			if (!isset($objOrg->UserRoles[$OrgUserRoleId])){
				throw new exception("Unknown UserRole");
			}
			$objOrgUserRole = $objOrg->UserRoles[$OrgUserRoleId];			
			$RoleId = $objOrgUserRole->RoleId;
			$UserId = $objOrgUserRole->UserId;

			if (is_object($objOrgUserRole->StartDate)){
				$StartDate = $objOrgUserRole->StartDate->format('d/m/Y');
			}
			if (is_object($objOrgUserRole->EndDate)){
				$EndDate = $objOrgUserRole->EndDate->format('d/m/Y');
			}
			
		}
		
		
		switch ($Mode){
			case 'new':
				
				break;
			default:
				if (IsEmptyString($OrgUserRoleId)) {
					throw new exception("OrgUserRoleId not specified");
				}

				break;
		}

		switch ($Mode){
			case 'edit':
				if (!is_null($UserId)){
					try{
						$objUser = new clsUser($UserId);
						$UserEmail = $objUser->Email;
						
					}
					catch (Exception $e) {
					    unset($e);
					}
				}										
				break;
		}
		

		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		$Page->Title = $Mode." user role";
		$PanelB .= "<h1>".$Page->Title."</h1>";

		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objOrg->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objOrg->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objOrg->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objOrg->canEdit){
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

				$PanelB .= pnlOrgUserRole( $OrgId, $OrgUserRoleId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objOrg->canControl === true){
					$PanelB .= "<li><a href='orguserrole.php?orgid=$OrgId&orguserroleid=$OrgUserRoleId&mode=edit'>&bull; edit</a></li> ";
					$PanelB .= "<li><a href='orguserrole.php?orgid=$OrgId&orguserroleid=$OrgUserRoleId&mode=delete'>&bull; delete</a></li> ";
				}
							    
				break;
			case 'new':
			case 'edit':
									
				$PanelB .= '<form method="post" action="doOrgUserRole.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				
				$PanelB .= "<input type='hidden' name='orgid' value='$OrgId'/>";
				
				if (!IsEmptyString($OrgUserRoleId)){
					$PanelB .= "<input type='hidden' name='orguserroleid' value='$OrgUserRoleId'/>";
				}
				
				$PanelB .= "<div class='sdbluebox'>";
				
				$PanelB .= "<table>";
				
				if (is_null($objUser)){
					$PanelB .= "<tr><th>User eMail</th><td><input name='useremail'/ size='50'></td></tr>";
				}
				else
				{
					$PanelB .= "<input type='hidden' name='userid' value='$UserId'/>";
					
					$PanelB .= "<tr><th>User</th><td>";
					if (!is_null($objUser->PictureOf)) {
						$PanelB .= "<img height = '30' src='image.php?Id=".$objUser->PictureOf."' /><br/>";
					}
					$PanelB .= $objUser->Name."<br/>";
					$PanelB .= $objUser->Email."</td>";
					$PanelB .= "</tr>";
				}


				$PanelB .= "<tr><th>Role</th><td><select name='roleid'>";
				$PanelB .= "<option/>";
				foreach ($objOrg->Roles as $optRoleId=>$optRole){
					$PanelB .= "<option value='$optRoleId'";
					if ($optRoleId == $RoleId){
						$PanelB .= "selected='selected' ";
					}
					$PanelB .= ">".$optRole->Name."</option>";
				}
				
				$PanelB .= "</td></tr>";
				

				$PanelB .= "<tr><th>Start Date</th><td><input type='date' name='startdate' class='datepicker' id='startdate' value='$StartDate'>";
				$PanelB .= "<tr><th>End Date</th><td><input type='date' name='enddate' class='datepicker' id='enddate' value='$EndDate'>";

				$PanelB .= "</table>";
				
				switch ( $Mode ){
					case "new":
						$PanelB .= '<input type="submit" value="Add User Role">';
						break;
					case "edit":
						$PanelB .= '<input type="submit" value="Update User Role">';
						break;
				}

				$PanelB .= "</div>";

				$PanelB .= '</form>';
				
				break;
				
			case 'delete':
				
				$PanelB .= pnlOrgUserRole( $OrgId, $OrgUserRoleId );
								
				$PanelB .= "<a href='doOrgUserRole.php?orgid=$OrgId&orguserroleid=$OrgUserRoleId&mode=delete'>confirm remove user role?</a><br/>";
				
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