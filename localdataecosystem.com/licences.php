<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");

	require_once("class/clsRights.php");
	require_once("class/clsData.php");
	require_once("class/clsLibrary.php");
	
	
	define('PAGE_NAME', 'licences');

	session_start();
	
	$System = new clsSystem();
	
	$Page = new clsPage();	
	
	$Script = '';
	$Script .= "<script type='text/javascript' src='java/ajax.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getLicences.js'></script>";
	$Script .= "<script>\n";

	$Script .= "function init(){ \n";
	$Script .= "    getLicences(); ";
	$Script .= "} \n";
	$Script .= "</script>\n";
	$Page->Script .= $Script;
	
	$Mode = 'view';
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
	
	try {
		
		$Page->Title = "licences";
		$PanelB .= "<h1>".$Page->Title."</h1>";

		$Licences = new clsLicences();
		$Orgs = new clsOrganisations();
		$Shapes = new clsShapes();
		$Defs = new clsDefinitions();
		
		$Tabs .= "<li><a href='#find'>Find";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='find'>";

			$TabContent .= "<div><h3>find a Licence</h3></div>";

			$TabContent .= "<table class='sdbluebox'>";
/*			
			$TabContent .= '<tr>';
				$TabContent .= "<th>Context</th>";
				$TabContent .= "<td>";				
				$TabContent .= "<select onchange='getSets()' id='filtercontext'/>";
				
				$TabContent .= '<option/>';
				foreach ($System->Config->SetContextTypes as $optContextId=>$optContext){
					$TabContent .= "<option value='$optContextId'>".$optContext."</option>";
				}
				$TabContent .= "</select>";
				$TabContent .= "</td>";
			$TabContent .= '</tr>';

			$TabContent .= '<tr>';
				$TabContent .= "<th>Licence Type</th>";
				$TabContent .= "<td>";				
				$TabContent .= "<select onchange='getSets()' id='filterlicencetype'/>";
				
				$TabContent .= '<option/>';
				foreach ($System->Config->SetLicenceTypeTypes as $optLicenceTypeId=>$optLiceneType){
					$TabContent .= "<option value='$optLicenceTypeId'>".$optLiceneType."</option>";
				}
				$TabContent .= "</select>";
				$TabContent .= "</td>";
			$TabContent .= '</tr>';
			
			
			$TabContent .= '<tr>';
				$TabContent .= "<th>Organisation</th>";
				$TabContent .= "<td>";				
				$TabContent .= "<select onchange='getSets()' id='filterorg'/>";
				
				$TabContent .= '<option/>';
				foreach ($Orgs->Items as $optOrg){
					$TabContent .= "<option value='$optOrg->Id'>".$optOrg->Name."</option>";
				}
				$TabContent .= "</select>";
				$TabContent .= "</td>";
			$TabContent .= '</tr>';

			$TabContent .= '<tr>';
				$TabContent .= "<th>Shape</th>";
				$TabContent .= "<td>";				
				$TabContent .= "<select onchange='getSets()' id='filtershape'/>";
				
				$TabContent .= '<option/>';
				foreach ($Shapes->Items as $optShape){
					$TabContent .= "<option value='$optShape->Id'>".$optShape->Name."</option>";
				}
				$TabContent .= "</select>";
				$TabContent .= "</td>";				
			$TabContent .= '</tr>';
			
*/			
			$TabContent .= "</table>";
			
		 	$TabContent .= "<div id='licences'>";
		 	$TabContent .= "</div>";
		 	
			
		$TabContent .= "</div>";

		if ($num > 0 ){
   			$Tabs .= "($num)";							
		}
	    $Tabs .= "</a></li>";

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