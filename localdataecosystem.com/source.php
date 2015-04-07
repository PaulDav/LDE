<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("class/clsLibrary.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlSource.php");
		
	define('PAGE_NAME', 'source');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	try {

		$Sources = new clsSources();
		$Defs = new clsDefinitions();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$SourceId = '';
		$Name = null;
		$Description = '';
		$URL = '';

		$objSource = null;
		
		
		if (isset($_REQUEST['sourceid'])){
			$SourceId = $_REQUEST['sourceid'];
			if (!isset($Sources->Items[$SourceId])){
				throw new exception("Unknown Source");
			}
			$objSource = $Sources->Items[$SourceId];
		}

		switch ($Mode){
			case 'new':
				break;
			default:
				if (is_null($SourceId)) {
					throw new exception("SourceId not specified");
				}
				break;
		}

		if (!empty($SourceId)){
			$Name = $objSource->Name;
			$Description = $objSource->Description;
			$URL = $objSource->URL;
		}		
		
		
		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		$Page->Title = $Mode." source";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objSource->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($Sources->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objSource->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objSource->canControl){
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
				$PanelB .= pnlSource( $SourceId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSource->canEdit === true){
					$PanelB .= "<li><a href='source.php?sourceid=$SourceId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSource->canControl === true){
					$PanelB .= "<li><a href='source.php?sourceid=$SourceId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";

				$Tabs .= "<li><a href='#defs'>Definitions";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='defs'>";
					$TabContent .= "<h3>Definitions</h3>";

					$arrDefs = array();
					foreach ($Defs->Items as $objDef){
						if ($objDef->SourceId == $SourceId){
							$arrDefs[] = $objDef;
						}
					}
					
					if (count($arrDefs) > 0){
						$TabContent .= "<table class='list'><thead><tr><th>Id</th><th>Type</th><th>Name</th><th>Description</th></tr></thead><tbody>";
						foreach ($arrDefs as $objDef){
							$num = $num + 1;
							$TabContent .= "<tr><td><a href='definition.php?defid=".$objDef->Id."'>".$objDef->Id."</td>";
							
							$TabContent .= "<td>";							
							if (isset($System->Config->DefTypes[$objDef->TypeId])){
								$TabContent .= $System->Config->DefTypes[$objDef->TypeId]->Name;
							}

							
							$TabContent .= "</td>";							
							
							$TabContent .= "<td>".$objDef->Name."</td><td>".nl2br(truncate($objDef->Description))."</td></tr>";
						}
						
						$TabContent .= "</tbody></table>";
					}
					
					
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
				break;
			case 'new':
			case 'edit':
				
				$PanelB .= "<div class='sdbluebox'>";
				
				$PanelB .= '<form method="post" action="doSource.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!is_null($SourceId)){
					$PanelB .= "<input type='hidden' name='sourceid' value='$SourceId'/>";
				}

				$PanelB .= "<table>";
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $SourceId;
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
				
			 	$PanelB .= '</table>';
				
				switch ( $Mode ){
					case "new":
						$PanelB .= '<input type="submit" value="Create New Source">';
						break;
					case "edit":
						$PanelB .= '<input type="submit" value="Update Source">';
						break;
				}

				$PanelB .= '</form>';
				$PanelB .= "</div>";

				break;
				
			case 'delete':
				
				$PanelB .= pnlSource( $SourceId );

				$PanelB .= "<a href='doSource.php?sourceid=$SourceId&mode=delete'>confirm delete?</a><br/>";
				
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