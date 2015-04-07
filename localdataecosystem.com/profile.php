<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlProfile.php");
	require_once("panel/pnlProfileClass.php");

	require_once("panel/pnlShape.php");
	
	
	require_once("panel/pnlGroup.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsProfile.php");
	
	define('PAGE_NAME', 'profile');

	session_start();
		
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

	try {

		$Profiles = new clsProfiles;
		$Shapes = new clsShapes;
		$Dicts = new clsDicts;
		$Specs = new clsSpecs;

		$objProfile = null;
		$objShape = null;
		$objGroup = null;
		$objSel = null;
		
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
				
		$ProfileId = null;
		$GroupId = null;
		$Name = '';
		$Description = '';
		$Publish = false;
//		$StartShapeClassId = null;
		
		$ClassDictId = null;
		$ClassId = null;

		if (isset($_REQUEST['profileid'])){
			$ProfileId = $_REQUEST['profileid'];
		}
		
		if (isset($_REQUEST['groupid'])){
			$GroupId = $_REQUEST['groupid'];
		}
/*		
		if (isset($_REQUEST['startshapeclassid'])){
			if (!IsEmptyString($_REQUEST['startshapeclassid'])){
				$StartShapeClassId = $_REQUEST['startshapeclassid'];
			}
		}				
*/		

		switch ($Mode){
			case 'new':
				if ($GroupId =='') {
					throw new exception("GroupId not specified");
				}
				

				break;
			default:
				if (is_null($ProfileId)) {
					throw new exception("ProfileId not specified");
				}

				break;
		}

		
		if (!empty($ProfileId)){
			if (!isset($Profiles->Items[$ProfileId])){
				throw new exception("Unknown Profile");
			}
			$objProfile = $Profiles->Items[$ProfileId];
			$GroupId = $objProfile->GroupId;
			$Name = $objProfile->Name;
			$Description = $objProfile->Description;
			$Publish = $objProfile->Publish;
			
			if (!is_null($objProfile->SelectionId)){
				if (isset($Shapes->Selections[$objProfile->SelectionId])){
					$objSel = $Shapes->Selections[$objProfile->SelectionId];
					/*
					if (is_null($StartShapeClassId)){
						if (isset($objSel->Selection->ShapeClass)){
							$StartShapeClassId = $objSel->Selection->ShapeClass->Id;
						}
					}
					*/
				}
			}			
			
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
		
		$Page->Title = $Mode." profile";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objProfile->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($Group->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objProfile->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objProfile->canEdit){
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
				$PanelB .= pnlProfile( $ProfileId );
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objProfile->canEdit === true){
					$PanelB .= "<li><a href='profile.php?profileid=$ProfileId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objProfile->canControl === true){
					$PanelB .= "<li><a href='profile.php?profileid=$ProfileId&mode=delete'>&bull; delete</a></li> ";
				}
				

				$Tabs .= "<li><a href='#group'>Group";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='group'>";
					$TabContent .= "<h3>in Group</h3>";	
					$TabContent .= pnlGroup($GroupId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
				$Tabs .= "<li><a href='#shape'>Shape</a></li>";
				$TabContent .= "<div class='tabContent hide' id='shape'>";
			    
				if ($objProfile->canEdit){
					
					if (is_null($objProfile->ShapeId)){
						
						$TabContent .= "<h3>set Shape</h3>";
						
						$optTabs = "";
						$optTabContent = "";
						
						$optTabs .= "<li><a href='#thisshapes'>Shapes in this Group</a></li>";					
						$optTabContent .= "<div class='tabContent' id='thisshapes'>";
						$optTabContent .= funSelectShape("this");				
						$optTabContent .= "</div>";
						
						$optTabs .= "<li><a href='#myshapes'>Shapes in My Groups</a></li>";
						$optTabContent .= "<div class='tabContent' id='myshapes'>";
						$optTabContent .= funSelectShape("my");
						$optTabContent .= "</div>";
						
						$optTabs .= "<li><a href='#publishedshapes'>Published Shapes</a></li>";
						$optTabContent .= "<div class='tabContent' id='publishedshapes'>";						
						$optTabContent .= funSelectShape("published");
						$optTabContent .= "</div>";
						
						if (!empty($optTabs)){
							$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
						}
						
					}
					else
					{
						$TabContent .= "<ul><li><a href='doProfileShape.php?profileid=$ProfileId&mode=delete'>&bull; remove shape from this profile</a></li></ul><br/>";
					}
				}

				if (!is_null($objProfile->ShapeId)){
					if (isset($Shapes->Items[$objProfile->ShapeId])){
						$objShape = $Shapes->Items[$objProfile->ShapeId];
						$TabContent .= pnlShapeSelection($objShape);
					}
				}
					
				$TabContent .= "</div>";					
    
				
				if (!is_null($objShape)){
				    $Tabs .= "<li><a href='#selection'>Selection</a></li>";
					$TabContent .= "<div class='tabContent hide' id='selection'>";
	
/*									
					if ($objProfile->canEdit === true){
						
						if (is_null($StartShapeClassId)){
							$TabContent .= "<h3>set the Subject of the Profile from the Shape</h3>";
							$TabContent .= funSelectSubject();				
						}
						else
						{
							
							if (!isset($objShape->ShapeClasses[$StartShapeClassId])){
								throw new exception("Invalid Subject");
							}
							
							$objShapeClass = $objShape->ShapeClasses[$StartShapeClassId];
							
							$TabContent .= "<table><tr><th>Subject Class</th><td>".$objShapeClass->Class->Label."</td></tr></table>";
							
							$UrlParams['startshapeclassid'] = null;			
							$ReturnUrl = UpdateUrl($UrlParams);
							$TabContent .= "<ul><li><a href='$ReturnUrl#selection'>&bull; choose another Subject</a></li></ul><br/>";
						}
					}
*/					
					if (!is_null($objSel)){
						$TabContent .= pnlShapeSelection($objSel);
					}
					
					
					if ($objProfile->canEdit === true){
						if (is_null($objProfile->SelectionId)){
							$TabContent .= "<li><a href='selection.php?profileid=$ProfileId&mode=new'>&bull; edit</a></li> ";
						}
						else
						{
							$TabContent .= "<li><a href='selection.php?profileid=$ProfileId&selid=".$objProfile->SelectionId."&mode=edit'>&bull; edit</a></li> ";
						}
					}
					
					$TabContent .= "</div>";	
					
					
	
					if (!is_null($objSel)){
						
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
						
								$Content = '';			
								$Content .= "<h3>Options</h3>";
							
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
											$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$objSel->getDot($VizStyle)."</script>";
											$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
											$Content .= "document.getElementById('graph1').innerHTML";
											$Content .= ",'svg');</script>";
											$Content .= "</div>";	
		
		//									$Content .= '<pre>'.htmlentities($objSel->getDot($VizStyle)).'</pre>';
											
											break;
										default:
											$Content .= "<img src='graphimage.php?shapeid=".$objSel->Id."&style=$VizStyle'/>";									
											break;
									}
								}
		
								$TabContent .= $Content;
								
							$TabContent .= "</div>";
					    }
					}
				
				
					$Tabs .= "<li><a href='#partitions'>Partitions</a></li>";
					$TabContent .= "<div class='tabContent hide' id='partitions'>";
					$TabContent .= "<h3>Partitions</h3>";	
					$TabContent .= "</div>";					
					
				    
				    $Tabs .= "<li><a href='#specs'>Import Specifications";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='specs'>";
						$TabContent .= "<h3>Import Specifications</h3>";	
	
						$arrSpecs = array();
						foreach ($Specs->Items as $optSpec){
							if ($optSpec->ProfileId == $ProfileId){
								$arrSpecs[] = $optSpec;
							}
						}
	
						if (count($arrSpecs) > 0){
							$TabContent .= "<table><thead><th>Name</th></thead><tbody>";
							foreach ($arrSpecs as $optSpec){
								$num = $num + 1;
								$TabContent .= "<tr>";
								$TabContent .= "<td><a href='spec.php?specid=".$optSpec->Id."'>".$optSpec->Name."</a></td>";
								$TabContent .= "</tr>";
							}
							$TabContent .= "</tbody></table>";
						}
	
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";
				}

				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doProfile.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!is_null($ProfileId)){
					$PanelB .= "<input type='hidden' name='profileid' value='$ProfileId'/>";
				}
				if (!is_null($GroupId)){
					$PanelB .= "<input type='hidden' name='groupid' value='$GroupId'/>";
				}				
										
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $ProfileId;
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
							$PanelB .= '<input type="submit" value="Create New Profile">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Profile">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlProfile( $ProfileId );
				
				$PanelB .= "<a href='doProfile.php?profileid=$ProfileId&mode=delete'>confirm delete?</a><br/>";
				
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
		
	
	
function funSelectShape($Selection='this'){

	global $System;
	global $Mode;
	
	global $Shapes;
	global $objProfile;
	
	global $ReturnURL;

	$optShapeList = array();

	$Content = "";
	
	$Content .= "<div class='sdbluebox'>";

	
	$opts = array();

		
	switch ($Selection){
		case "this":
			foreach ($Shapes->Items as $optShape){
				if ($optShape->GroupId == $objProfile->GroupId){					
					$opts[$optShape->Id] = $optShape;
				}
			}
			break;
			
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

		$Content .= "<thead><tr><th>Shape</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optShape){

			$ReturnUrl = 'doProfileShape.php?profileid='.$objProfile->Id.'&shapeid='.$optShape->Id;					
			$Content .= "<tr><td><a href='$ReturnUrl'>".$optShape->Name."<a></td>";
			$Content .= "<td>".nl2br($optShape->Description)."</td>";
			$Content .= "</tr>";
	
		}
		$Content .= "</tbody>";
		$Content .= "</table>";
		
	}
		
 	$Content .= "</div>";
 	
	return $Content;
}
	

function funSelectSubject(){

	global $System;
	global $Mode;
	
	global $Shapes;
	global $objShape;
	
	global $ReturnURL;
	
	$opts = array();
		
	foreach ($objShape->ShapeClasses as $optShapeClass){
		$opts[$optShapeClass->Id] = $optShapeClass;
	}
	

	$Content = "";
	
	$Content .= "<div class='sdbluebox'>";

	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Class</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optShapeClass){

			$UrlParams = array();
			$UrlParams['startshapeclassid'] = $optShapeClass->Id;			
			$ReturnUrl = UpdateUrl($UrlParams);

			$Content .= "<tr><td><a href='$ReturnUrl#selection'>".$optShapeClass->Class->Label."<a></td></tr>";

		}
		$Content .= "</tbody>";
		$Content .= "</table>";
		
	}
	

 	$Content .= "</div>";
 	
	return $Content;
	
}	
?>