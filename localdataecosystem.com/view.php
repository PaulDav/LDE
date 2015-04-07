<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlView.php");	
	require_once("panel/pnlViewSelection.php");	
	
	require_once("panel/pnlGroup.php");
	require_once("panel/pnlClass.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsView.php");
	
	require_once("form/frmSelectClass.php");
	
	
	define('PAGE_NAME', 'view');

	session_start();
		
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

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
		
				
		$ViewId = null;
		$GroupId = null;
		$Name = '';
		$Description = '';
		$Publish = false;
		
		$Action = null;
		$ClassDictId = null;
		$ClassId = null;
		
		if (isset($_REQUEST['viewid'])){
			$ViewId = $_REQUEST['viewid'];
		}
		
		if (isset($_REQUEST['groupid'])){
			$GroupId = $_REQUEST['groupid'];
		}
		
		if (isset($_REQUEST['action'])){
			$Action = $_REQUEST['action'];
		}

		if (isset($_REQUEST['classdictid'])){
			$ClassDictId = $_REQUEST['classdictid'];
		}
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
		}
		
		
		switch ($Mode){
			case 'new':
				if ($GroupId =='') {
					throw new exception("GroupId not specified");
				}
				

				break;
			default:
				if ($ViewId =='') {
					throw new exception("ViewId not specified");
				}

				break;
		}

		if (!empty($ViewId)){
			$objView = new clsView($ViewId);
			$GroupId = $objView->GroupId;
			$Name = $objView->Name;
			$Description = $objView->Description;
			$Publish = $objView->Publish;
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
			$Group = new clsGroup($GroupId);
			if ($Group->canView === false){
				throw new exception("You cannot view this Group");
			}
		}		
		
		$Page->Title = $Mode." view";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objView->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($Group->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objView->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objView->canEdit){
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
				$PanelB .= pnlView( $ViewId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objView->canEdit === true){
					$PanelB .= "<li><a href='view.php?viewid=$ViewId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objView->canControl === true){
					$PanelB .= "<li><a href='view.php?viewid=$ViewId&mode=delete'>&bull; delete</a></li> ";
				}

				$Tabs .= "<li><a href='#group'>Group";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='group'>";
					$TabContent .= "<h3>in Group</h3>";	
					$TabContent .= pnlGroup($GroupId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
				
			    $Tabs .= "<li><a href='#selections'>Selections</a></li>";
				$TabContent .= "<div class='tabContent hide' id='selections'>";
				$TabContent .= "<h3>Selections</h3>";
				
				if (count($objView->Selections) > 0){
					foreach ($objView->Selections as $objViewSelection){						
						$TabContent .= pnlViewSelection($objViewSelection);
						if ($objView->canEdit){
							$TabContent .= "<li><a href='viewselection.php?viewid=$ViewId&mode=edit&selseq=".$objViewSelection->Seq."'>&bull; edit</a></li> ";
						}
					}
				}
				
				if ($objView->canEdit){
					switch ($Action){
						case'addselection':
							
							if (is_null($ClassId)){
							
								$TabContent .= "<h4>Choose a class from these selections</h4>";
								
								$optTabs = "";
								$optTabContent = "";
								
								$optTabs .= "<li><a href='#thisclasses'>Classes in this Group</a></li>";
								
								$optTabContent .= "<div class='tabContent' id='thisclasses'>";
								$optTabContent .= frmSelectClass("this", $objView->GroupId);				
								$optTabContent .= "</div>";
								
								$optTabs .= "<li><a href='#myclasses'>Classes in My Groups</a></li>";
								$optTabContent .= "<div class='tabContent' id='myclasses'>";
								$optTabContent .= frmSelectClass("my");
								$optTabContent .= "</div>";
								
								$optTabs .= "<li><a href='#publishedclasses'>Classes in Published Dictionaries</a></li>";
								$optTabContent .= "<div class='tabContent' id='publishedclasses'>";						
								$optTabContent .= frmSelectClass("published");
								$optTabContent .= "</div>";
								
								if (!empty($optTabs)){
									$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
								}
							}
							else
							{
								$objDict = $Dicts->Dictionaries[$ClassDictId];
								$objClass = $objDict->Classes[$ClassId];
								$TabContent .= "<form method='post' action='doViewSelection.php'>";
								$TabContent .= "<input type='hidden' name='action' value='addselection'/>";
								$TabContent .= "<input type='hidden' name='viewid' value='$ViewId'/>";
								$TabContent .= "<input type='hidden' name='classdictid' value='$ClassDictId'/>";
								$TabContent .= "<input type='hidden' name='classid' value='$ClassId'/>";
								
								$TabContent .= "<table><tr><th>Class</th><td>".$objClass->Label."</td></tr></table>";
								$TabContent .= "<input type='submit' value='add this selection'/>";
								$TabContent .= "</form>";
							}
							
							break;
						default:
							$UrlParams = array();
							$UrlParams['action'] = 'addselection';
							$SelAction = UpdateUrl($UrlParams).'#selections';
							$TabContent .= "<ul><li><a href='$SelAction'>&bull;  Add a Selection</a></li></ul>";
							break;
					}
				}
			
				$TabContent .= "</div>";
				
				
				
				
			    			    
			    
			    if (!is_null($System->Config->DotRenderer)){			    
				    $Tabs .= "<li><a href='#visualize'>Visualize</a></li>";
					$TabContent .= "<div class='tabContent hide' id='visualize'>";
	
						$vizstyles = array();
						$vizstyles[0] = "None";
						$vizstyles[1] = "Classes";
	//					$vizstyles[2] = "Classes in Concept Packages";
	//					$vizstyles[3] = "Classes and Properties";

						$VizStyle = 1;
						if (isset($_REQUEST['vizstyle'])){
							$VizStyle = $_REQUEST['vizstyle'];
						}
				
											
						$Content = "";
						$Content .= "<h3>Options</h3>";
					
//						$Action = "view.php";
								
						$FormParams = array();				
						$Action = UpdateUrl($FormParams)."#visualize";
								
								
						$Content .= "<form method='post' action='$Action'>";
									
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
									$Content .= "<div id='viz'/>";
									$Page->Script .= "<script src='viz.js'></script>";
									$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$objView->getDot($VizStyle)."</script>";
									$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
									$Content .= "document.getElementById('graph1').innerHTML";
									$Content .= ",'svg');</script>";				
									
									break;
								default:
									$Content .= "<img src='graphimage.php?viewid=$ViewId&style=$VizStyle'/>";									
									break;
							}
						}
					
						$TabContent .= $Content;
					
					$TabContent .= "</div>";
			    }
			    
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doView.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $ViewId == '')){
					$PanelB .= "<input type='hidden' name='viewid' value='$ViewId'/>";
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
						$PanelB .= $ViewId;
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
							$PanelB .= '<input type="submit" value="Create New View">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update View">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlView( $ViewId );
				
				$PanelB .= "<a href='doView.php?viewid=$ViewId&Mode=delete'>confirm delete?</a><br/>";
				
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