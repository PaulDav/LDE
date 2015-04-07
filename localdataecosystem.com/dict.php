<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlGroup.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsModel.php");
	
	define('PAGE_NAME', 'dict');

	session_start();
		
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

	$Model = new clsModel();

	try {
		
		$Dicts = new clsDicts();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
				
		$DictId = '';
		$GroupId = '';
		$Name = '';
		$Description = '';
		$Publish = false;

		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		
		if (isset($_REQUEST['groupid'])){
			$GroupId = $_REQUEST['groupid'];
		}
		
	
		
		switch ($Mode){
			case 'new':
				if ($GroupId =='') {
					throw new exception("GroupId not specified");
				}
				

				break;
			default:
				if ($DictId =='') {
					throw new exception("DictId not specified");
				}

				break;
		}
		
		if (!empty($DictId)){
			
			if (!isset($Dicts->Dictionaries[$DictId])){
				throw new Exception("Unknown Dictionary");
			}
			
			$objDict = $Dicts->Dictionaries[$DictId];
			$GroupId = $objDict->GroupId;
			$Name = $objDict->Name;
			$Description = $objDict->Description;
			$Publish = $objDict->Publish;			
		}		

		if ($System->Session->Error){
			if (isset($FormFields['groupid'])){
				$GroupId = $FormFields['groupid'];
			}
			if (isset($FormFields['name'])){
				$Name = $FormFields['name'];
			}
			if (isset($FormFields['description'])){
				$Description = $FormFields['description'];
			}				
			if (isset($FormFields['publish'])){
				if ($FormFields['publish'] == 'yes'){
					$Publish = true;
				}
			}				
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if (!empty($GroupId)){
			$objGroup = new clsGroup($GroupId);
			if ($objGroup->canView === false){
				throw new exception("You cannot view this Group");
			}
		}		
		
		$Page->Title = $Mode." dictionary";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objDict->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objGroup->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objDict->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objDict->canEdit){
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
				$PanelB .= pnlDict( $DictId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='dict.php?dictid=$DictId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='dict.php?dictid=$DictId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				
				
				if (!is_null($System->Config->DotRenderer)){			    
				    $Tabs .= "<li><a href='#visualize'>Visualize</a></li>";
					$TabContent .= "<div class='tabContent hide' id='visualize'>";
	
						$vizstyles = array();
						$vizstyles[0] = "None";
						$vizstyles[1] = "Classes";
						$vizstyles[2] = "Classes and Properties";
						
						$VizStyle = 1;
						if (isset($_REQUEST['vizstyle'])){
							$VizStyle = $_REQUEST['vizstyle'];
						}
				
											
						$Content = "";
						$Content .= "<h3>Options</h3>";
					
						$Action = "dict.php";
								
						$FormParams = array();				
						$Action = UpdateUrl($FormParams,$Action)."#visualize";
								
								
						$Content .= "<form method='post' action='$Action'>";
				
						$ReturnURL = $_SERVER['SCRIPT_NAME'];
						$QueryString = $_SERVER['QUERY_STRING'];
						$ReturnURL = $ReturnURL.'?'.$QueryString;
				
						$Content .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
						
						$Content .= '<table class="sdbluebox">';
									
						
						$Content .= '<tr>';
							$Content .= '<th>';
							$Content .= 'Style';
							$Content .= '</th>';
							$Content .= '<td>';
							$Content .= "<select name='vizstyle'>";
							foreach ($vizstyles as $optVizStyleCode=>$optVizStyle){
								$Content .= "<option value='$optVizStyleCode' ";
								if ($VizStyle == $optVizStyleCode){
									$Content .= " selected='true' ";
								}
								$Content .= ">$optVizStyle</option>";
							}
							$Content .= "</select>";
							$Content .= '</td>';
						$Content .= '</tr>';					
						
						$Content .= '</table>';
						
						$Content .= "<input type='submit' value='Apply'/>";
						
						$Content .= '</form>';
						
						
						if ($VizStyle > 0){
							switch ($System->Config->DotRenderer){
								case "viz.js":
									$Content .= "<div id='viz'></div>";
									$Page->Script .= "<script src='viz.js'></script>";
									$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$Dicts->getDictDot($DictId,$VizStyle)."</script>";
									$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
									$Content .= "document.getElementById('graph1').innerHTML";
									$Content .= ",'svg');</script>";				
									
									break;
								default:
									$Content .= "<img src='graphimage.php?dictid=$DictId&style=$VizStyle'/>";
									
									break;
							}
						}
										
						$TabContent .= $Content;
					
					$TabContent .= "</div>";
			    }
				
				$Tabs .= "<li><a href='#classes'>Classes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='classes'>";
					$TabContent .= "<h3>Classes</h3>";	

					if (count($objDict->Classes) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Label</th><th>Concept</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objDict->Classes as $objClass){
							
							$num = $num + 1;
							
							$TabContent .= "<tr>";
							
							$TabContent .= "<td><a href='class.php?dictid=".$objDict->Id."&classid=".$objClass->Id."'>".$objClass->Id."</a></td>";
							$TabContent .= "<td>".$objClass->Label."</td>";
							$TabContent .= "<td>".strtoupper($objClass->Concept)."</td>";
							
							$TabContent .= "<td>".nl2br(Truncate($objClass->Description))."</td>";
						}
				 		$TabContent .= '</table>';
					}
					
					
					
					if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='class.php?dictid=$DictId&mode=new'>&bull; add</a></li> </ul></div>";
					}
	
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";

			    $Tabs .= "<li><a href='#properties'>Properties";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='properties'>";
					$TabContent .= "<h3>Properties</h3>";	

					if (count($objDict->Properties) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Label</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objDict->Properties as $objProp){
							
							$num = $num + 1;
							
							$TabContent .= "<tr>";
							
							$TabContent .= "<td><a href='property.php?dictid=".$objDict->Id."&propid=".$objProp->Id."'>".$objProp->Id."</a></td>";
							$TabContent .= "<td>".$objProp->Label."</td>";
							$TabContent .= "<td>".nl2br(Truncate($objProp->Description))."</td>";
						}
				 		$TabContent .= '</table>';
					}
					
					if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='property.php?dictid=$DictId&mode=new'>&bull; add</a></li> </ul></div>";
					}
	
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    

			    $Tabs .= "<li><a href='#relationships'>Relationships";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='relationships'>";
					$TabContent .= "<h3>Relationships</h3>";	

					if (count($objDict->Relationships) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Subject</th><th/><th>Object</th><th>Cardinality</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objDict->Relationships as $objRel){
							
							$objSubjectDict = $objDict;
							if (!($objRel->SubjectDictId == $objDict->Id)){
								$objSubjectDict = $Dicts->Dictionaries[$objRel->SubjectDictId];								
							}
							$objSubject = $objSubjectDict->Classes[$objRel->SubjectId];

							$objObjectDict = $objDict;
							if (!($objRel->ObjectDictId == $objDict->Id)){
								$objObjectDict = $Dicts->Dictionaries[$objRel->ObjectDictId];								
							}
							$objObject = $objObjectDict->Classes[$objRel->ObjectId];
							
							
							$num = $num + 1;
							
							$TabContent .= "<tr>";
							
							$TabContent .= "<td><a href='relationship.php?dictid=".$objDict->Id."&relid=".$objRel->Id."'>".$objRel->Id."</a></td>";
							$TabContent .= "<td>".$objSubject->Label."</td>";							
							$TabContent .= "<td>".$objRel->Label."</td>";
							$TabContent .= "<td>".$objObject->Label."</td>";
							$TabContent .= "<td>".$objRel->Cardinality."</td>";
							$TabContent .= "<td>".nl2br(Truncate($objRel->Description))."</td>";
						}
				 		$TabContent .= '</table>';
					}
									
					if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='relationship.php?dictid=$DictId&mode=new'>&bull; add</a></li> </ul></div>";
					}
	
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    

			    $Tabs .= "<li><a href='#lists'>Lists";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='lists'>";
					$TabContent .= "<h3>Lists</h3>";	

					if (count($objDict->Lists) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Label</th><th>Description</th><th>Source</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';

						$arrLists = array();
						foreach ( $objDict->Lists as $objList){
							$arrLists[$objList->Label][$objList->Id] = $objList;	
						}
						ksort($arrLists);

						foreach ($arrLists as $arrList){
							foreach ($arrList as $objList){
								$num = $num + 1;
	
								$TabContent .= "<tr>";
	
								$TabContent .= "<td><a href='list.php?dictid=".$objDict->Id."&listid=".$objList->Id."'>".$objList->Id."</a></td>";
								$TabContent .= "<td>".$objList->Label."</td>";
								$TabContent .= "<td>".nl2br(Truncate($objList->Description))."</td>";
								$TabContent .= "<td>".$objList->Source."</td>";
							}

						}
				 		$TabContent .= '</table>';
					}
									
					if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='list.php?dictid=$DictId&mode=new'>&bull; add</a></li> </ul></div>";
					}
	
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    

			    $Tabs .= "<li><a href='#export'>Export</a></li>";
			    $TabContent .= "<div class='tabContent hide' id='export'>";

			    	$exportformats = array();
					$exportformats[0] = "None";
					$exportformats[1] = "xml schema";

					$exportformats[10] = "Description Set Profiles";
					$exportformats[11] = "Shape Expressions";
					$exportformats[12] = "Resource Shapes";
					
					
					$ExportFormat = 0;
					if (isset($_REQUEST['exportformat'])){
						$ExportFormat = $_REQUEST['exportformat'];
					}
													
					$Content = "";
					$Content .= "<h3>Options</h3>";
				
					$Action = "dict.php";
							
					$FormParams = array();				
					$Action = UpdateUrl($FormParams,$Action)."#export";
							
							
					$Content .= "<form method='post' action='$Action'>";
			
					$ReturnURL = $_SERVER['SCRIPT_NAME'];
					$QueryString = $_SERVER['QUERY_STRING'];
					$ReturnURL = $ReturnURL.'?'.$QueryString;
			
					$Content .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
					
					$Content .= '<table class="sdbluebox">';
								
					
					$Content .= '<tr>';
						$Content .= '<th>';
						$Content .= 'Style';
						$Content .= '</th>';
						$Content .= '<td>';
						$Content .= "<select name='exportformat'>";
						foreach ($exportformats as $optExportFormatCode=>$optExportFormat){
							$Content .= "<option value='$optExportFormatCode' ";
							if ($ExportFormat == $optExportFormatCode){
								$Content .= " selected='true' ";
							}
							$Content .= ">$optExportFormat</option>";
						}
						$Content .= "</select>";
						$Content .= '</td>';
					$Content .= '</tr>';					
					
					$Content .= '</table>';
					
					$Content .= "<input type='submit' value='Apply'/>";
					
					$Content .= '</form>';
					
					if ($ExportFormat > 0){
						$PanelB .= "<pre>"."<pre>";
					}
				
					$TabContent .= $Content;
			    
				$TabContent .= "</div>";

				
				if (!is_null($GroupId)){
					$Tabs .= "<li><a href='#group'>Group";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='group'>";
						$TabContent .= "<h3>in Group</h3>";	
						$TabContent .= pnlGroup($GroupId);	
					$TabContent .= "</div>";
				    $Tabs .= "</a></li>";
				}
				
				
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doDict.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $DictId == '')){
					$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				}
				if (!( $GroupId == '')){
					$PanelB .= "<input type='hidden' name='groupid' value='$GroupId'/>";
				}				
										
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $DictId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Name';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="name" size="30" maxlength="100" value="'.$Name.'">';
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
					$PanelB .= 'Publish?';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					
					$PanelB .= "<select name='publish'>";
					$PanelB .= "<option>No</option>";
					$PanelB .= "<option";
					if ($Publish === true){
						$PanelB .= " selected='true' ";
					}
					$PanelB .= ">Yes</option>";
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
								
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Dictionary">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Dictionary">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlDict( $DictId );
				
				$PanelB .= "<a href='doDict.php?dictid=$DictId&mode=delete'>confirm delete?</a><br/>";
				
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