<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("class/clsLibrary.php");
	
	define('PAGE_NAME', 'library');

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
		
		$Page->Title = "library";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Sources = new clsSources();
		$Defs = new clsDefinitions();			
				
		$Tabs .= "<li><a href='#sources'>Sources";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='sources'>";

			$TabContent .= "<div><h3>Sources</h3></div>";
			
			if ($System->LoggedOn === true){					
				$TabContent .= "<a href='source.php?mode=new'>&bull; add</a><br/>";
			}

			if (count($Sources->Items) > 0){
						
				$TabContent .= "<table class='list'>";
				$TabContent .= '<thead>';
				$TabContent .= '<tr>';
					$TabContent .= "<th>Name</th><th>Described at</th>";
				$TabContent .= '</tr>';
				$TabContent .= '</thead>';
					
				foreach ( $Sources->Items as $objSource){
					$num = $num + 1;
					
					$TabContent .= "<tr>";
												
					$TabContent .= "<td><a href='source.php?sourceid=".$objSource->Id."'>".$objSource->Name."</a></td>";
					$TabContent .= "<td>".make_links($objSource->URL)."</td>";
					
				}

		 		$TabContent .= '</table>';
			}
			
		$TabContent .= "</div>";

		if ($num > 0 ){
   			$Tabs .= "($num)";							
		}
	    $Tabs .= "</a></li>";

	    
	    foreach ($System->Config->DefTypes as $optDefType){
		    $Tabs .= "<li><a href='#".$optDefType->Heading."'>".$optDefType->Heading;
			$num = 0;
			$TabContent .= "<div class='tabContent hide' id='".$optDefType->Heading."'>";
	
				$TabContent .= "<div><h3>".$optDefType->Heading."</h3></div>";
				
				if ($System->LoggedOn === true){					
					$TabContent .= "<a href='definition.php?mode=new&deftypeid=".$optDefType->Id."'>&bull; add</a><br/>";
				}
	
				if (isset($Defs->TypeItems[$optDefType->Id])){
							
					$TabContent .= "<table class='list'>";
					$TabContent .= '<thead>';
					$TabContent .= '<tr>';
						$TabContent .= "<th>Name</th><th>URL</th><th>Source</th>";
					$TabContent .= '</tr>';
					$TabContent .= '</thead>';
						
					foreach ( $Defs->TypeItems[$optDefType->Id] as $objDef){
						$num = $num + 1;
						
						$TabContent .= "<tr>";
													
						$TabContent .= "<td><a href='definition.php?defid=".$objDef->Id."'>".$objDef->Name."</a></td>";
						$TabContent .= "<td>".make_links($objDef->URL)."</td>";
						
						$TabContent .= "<td>";
						if (!is_null($objDef->SourceId)){
							if (isset($Sources->Items[$objDef->SourceId])){
								$TabContent .= $Sources->Items[$objDef->SourceId]->Name;
							}
						}
						$TabContent .= "</td>";
						
						$TabContent .= "</tr>";
						
					}
	
			 		$TabContent .= '</table>';
				}
				
			$TabContent .= "</div>";
	
			if ($num > 0 ){
	   			$Tabs .= "($num)";							
			}
		    $Tabs .= "</a></li>";
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