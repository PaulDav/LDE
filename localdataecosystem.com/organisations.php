<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("class/clsRights.php");
	
	define('PAGE_NAME', 'organisations');

	session_start();
	
	$System = new clsSystem();
	
	$Page = new clsPage();	

	$Mode = 'view';
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";

	if (isset($_REQUEST['mode'])){
		$Mode = $_REQUEST['mode'];
	}
	
	try {
		
		$Page->Title = "organisations";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Orgs = new clsOrganisations();
				
		$Tabs .= "<li><a href='#orgs'>Organisations";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='orgs'>";

			$TabContent .= "<div><h3>Organisations</h3></div>";
			
			if ($System->LoggedOn === true){					
				$TabContent .= "<a href='organisation.php?mode=new'>&bull; add</a><br/>";
			}

			if (count($Orgs->Items) > 0){
						
				$TabContent .= "<table class='list'>";
				$TabContent .= '<thead>';
				$TabContent .= '<tr>';
					$TabContent .= "<th>Name</th><th>Description</th>";
				$TabContent .= '</tr>';
				$TabContent .= '</thead>';

				foreach ( $Orgs->Items as $objOrg){
					$num = $num + 1;

					$TabContent .= "<tr>";

					$TabContent .= "<td><a href='organisation.php?orgid=".$objOrg->Id."'>".$objOrg->Name."</a></td>";
					$TabContent .= "<td>".truncate($objOrg->Description)."</td>";
					$TabContent .= "</tr>";
					
				}

		 		$TabContent .= '</table>';
			}
			
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