<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlSetShape.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsData.php");
	require_once("class/clsShape.php");
	
	define('PAGE_NAME', 'setshape');

	session_start();
		
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

			
	try {

		$Shapes = new clsShapes();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$SetShapeId = null;
		$SetId = null;
		$ShapeId = null;

		if (isset($_REQUEST['setshapeid'])){
			$SetShapeId = $_REQUEST['setshapeid'];
		}
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}

		if (isset($_REQUEST['shapeid'])){
			$ShapeId = $_REQUEST['shapeid'];
		}

		if (IsEmptyString($SetId)) {
			throw new exception("SetId not specified");
		}
		
		$objSet = new clsSet($SetId);
		
		switch ($Mode){
			case 'new':
				
				break;
			default:
				if (IsEmptyString($SetShapeId)) {
					throw new exception("SetShapeId not specified");
				}

				break;
		}

		if (!empty($SetShapeId)){
			if (!isset($objSet->SetShapes[$SetShapeId])){
				throw new exception("Unknown SetShapeId");
			}
			$objSetShape = $objSet->SetShapes[$SetShapeId];
			$ShapeId = $objSetShape->ShapeId;
		}		


		if ($System->Session->Error){
			if (isset($FormFields['shapeid'])){
				$ShapeId = $FormFields['shapeid'];
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
		
		$Page->Title = $Mode." shape for set";
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
				
				$PanelB .= pnlSetShape( $SetId, $SetShapeId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSet->canEdit === true){
					$PanelB .= "<li><a href='setshape.php?setid=$SetId&setshapeid=$SetShapeId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSet->canControl === true){
					$PanelB .= "<li><a href='setshape.php?setid=$SetId&setshapeid=$SetShapeId&mode=delete'>&bull; delete</a></li> ";
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
				
				if (is_null($ShapeId)){
					$PanelB .= "<h3>Select a Shape from any of these selections</h3>";	
			
					$optTabs = "";
					$optTabContent = "";
										
					$optTabs .= "<li><a href='#myshapes'>Shapes in My Groups</a></li>";
					$optTabContent .= funSelectShape("my");
										
					$optTabs .= "<li><a href='#publishedshapes'>Published Shapes</a></li>";			    
					$optTabContent .= funSelectShape("published");
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
				}
				else
				{
					
					$PanelB .= pnlShape($ShapeId)."<br/>";
					
					$PanelB .= '<form method="post" action="doSetShape.php">';
			
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					
					$PanelB .= "<input type='hidden' name='setid' value='$SetId'/>";
					$PanelB .= "<input type='hidden' name='shapeid' value='$ShapeId'/>";
					
					if (!IsEmptyString($SetShapeId)){
						$PanelB .= "<input type='hidden' name='setshapeid' value='$SetShapeId'/>";
					}
											
						
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Add Shape to the Set">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Shape on the Set">';
							break;
					}
	
					$PanelB .= '</form>';
				}

				break;
				
			case 'delete':
				
				$PanelB .= pnlSetShape( $SetId, $SetShapeId );
				
				$PanelB .= "<a href='doSetShape.php?setid=$SetId&setshapeid=$SetShapeId&mode=delete'>confirm remove shape from set?</a><br/>";
				
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


function funSelectShape($Selection){

	global $System;
	
	global $Shapes;
	
	$Content = "";
				
	$TabId = "";
	$TabId = $Selection."shapes";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$ShapeFieldName = "shapeid";

	$opts = array();

	switch ($Selection){
			
		case "my":
			
			foreach ($Shapes->Items as $optShape){
							
				$optGroup = new clsGroup($optShape->GroupId);
				if (!$optGroup->canEdit){
					continue;
				}				

				$opts[$optShape->Id] = $optShape;
			}
			break;
			
		case "published":
			
			foreach ($Shapes->Items as $optShape){
			
				if (!$optShape->Publish){
					continue;
				}
								
				$opts[$optShape->Id] = $optShape;
				
			}

			break;
			
	}
		
		
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Id</th><th>Name</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		
		foreach ($opts as $optShapeId=>$optShape){

			$UrlParams = array();
			$UrlParams[$ShapeFieldName] = $optShape->Id;
			$ReturnUrl = UpdateUrl($UrlParams);
						
			$Content .= "<tr>";
			$Content .= "<tr><td><a href='$ReturnUrl'>".$optShape->Id."</a></td>";
			$Content .= "<td>".$optShape->Name."</td>";
			$Content .= "<td>".nl2br($optShape->Description)."</td>";
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