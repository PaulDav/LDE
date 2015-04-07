<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
		
	require_once("class/clsData.php");
	require_once("class/clsDict.php");
	require_once("class/clsView.php");
	
	
	define('PAGE_NAME', 'dashboards');

	session_start();
		
	$System = new clsSystem();
			
	$Page = new clsPage();

			
	try {

		$Dicts = new clsDicts();
		$Views = new clsViews();
		
		$Shapes = new clsShapes();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$Page->Title = "dashboards";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				$ModeOk = true;
				break;
		}
		if (!$ModeOk){
			throw new Exception("Invalid Mode");										
			break;
		}
								
				
		switch ($Mode){
			case 'view':
				$PanelB .= "<table><thead><tr><th>View</th></tr></thead><tbody>";
				
				foreach ($Shapes->Items as $objShape){
					$PanelB .= "<tr><td><a href='dashboard.php?shapeid=".$objShape->Id."'>".$objShape->Name."</a></td></tr>";						
				}
				
				
				$PanelB .= "</tbody></table>";
					
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