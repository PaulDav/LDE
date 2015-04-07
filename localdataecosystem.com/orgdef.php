<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsRights.php");
	require_once("class/clsLibrary.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrgDef.php");
	require_once("panel/pnlOrg.php");
	
	define('PAGE_NAME', 'orgdef');

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

		$OrgDefId = null;
		$OrgId = null;
		$DefId = '';
		
		$DefTypeId = null;
		$DateFrom = null;
		$DateTo = null;
		$Reference = null;
		$URL = null;

		$objOrgDef = null;
		$objOrg = null;
		$objDef = null;
		
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
				
		if (isset($_REQUEST['orgdefid'])){
			$OrgDefId = $_REQUEST['orgdefid'];
			if (!isset($objOrg->HasDefs[$OrgDefId])){
				throw new exception("Not on this Organisation");
			}
			$objOrgDef = $objOrg->HasDefs[$OrgDefId];
		}

		
		switch ($Mode){
			case 'new':
				if (!isset($_REQUEST['deftypeid'])){
					throw new exception("Def Type not specified");
				}
					
				$DefTypeId = $_REQUEST['deftypeid'];
				
				break;
			default:
				if (is_null($OrgId)) {
					throw new exception("OrgId not specified");
				}
				if (is_null($OrgDefId)) {
					throw new exception("OrgDefId not specified");
				}
				
				break;
		}

		
		if (!is_null($objOrgDef)){
			$DateFrom = $objOrgDef->DateFrom;
			$DateTo = $objOrgDef->DateTo;
			
			$Reference = $objOrgDef->Reference;
			$URL = $objOrgDef->URL;
			
			$DefTypeId = $objOrgDef->DefTypeId;
		}		

		if (!isset($System->Config->DefTypes[$DefTypeId])){
			throw new exception("Unknown Definition Type");
		}
		$objDefType = $System->Config->DefTypes[$DefTypeId];
		
		
		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		
		$Page->Title = $Mode." organisation ".$objDefType->Name;		
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
				$PanelB .= pnlOrgDef( $OrgId, $OrgDefId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objOrg->canEdit === true){
					$PanelB .= "<li><a href='orgdef.php?orgid=$OrgId&orgdefid=$OrgDefId&mode=edit'>&bull; edit</a></li> ";
					$PanelB .= "<li><a href='orgdef.php?orgid=$OrgDefId&orgdefid=$OrgDefId&mode=delete'>&bull; delete</a></li> ";
				}
				$PanelB .= "</ul></div>";
				
				$PanelB .= '<h3>of Organisation</h3>';
				$PanelB .= pnlOrg($OrgId);

				break;
			case 'new':
			case 'edit':

				$PanelB .= "<div class='sdbluebox'>";

				$PanelB .= '<form method="post" action="doOrgDef.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				
				if (!is_null($OrgId)){
					$PanelB .= "<input type='hidden' name='orgid' value='$OrgId'/>";
				}

				if (!is_null($OrgDefId)){
					$PanelB .= "<input type='hidden' name='orgdefid' value='$OrgDefId'/>";
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
						$PanelB .= $OrgDefId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}

				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= $objDefType->Name;
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<select name='defid'><option/>";
					foreach ($Defs->Items as $optDef){
						
						if ($optDef->TypeId === $DefTypeId){
						
							$PanelB .= "<option ";
							if (is_object($objOrgDef)){
								if ($optDef->Id == $objOrgDef->DefId){
									$PanelB .= " selected='true' ";
								}
							}
							$PanelB .= " value='".$optDef->Id."'>".$optDef->Name."</option>";
						}
					}
					$PanelB .= '</select>';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				
				$PanelB .= '<tr>';
					$PanelB .= '<th>Date From</th>';
					$PanelB .= '<td>';
					$PanelB .= "<input type='date' name='datefrom' class='datepicker' id='datefrom' value='".$DateFrom."'/>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>Date To</th>';
					$PanelB .= '<td>';
					$PanelB .= "<input type='date' name='dateto' class='datepicker' id='dateto' value='".$DateTo."'/>";
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
					$PanelB .= 'Reference';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<input type='text' name='reference' size='40' maxlength='100' value='$Reference'/>";
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
				
				$PanelB .= pnlOrgDef( $OrgId, $OrgDefId );
				
				$PanelB .= "<a href='doOrgDef.php?orgid=$OrgId&orgdefid=$OrgDefId&mode=delete'>confirm delete?</a><br/>";
				
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