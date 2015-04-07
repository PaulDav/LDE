<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");

	require_once("class/clsShape.php");
	require_once("panel/pnlShape.php");
	
	require_once("panel/pnlGroup.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	
	
	
	define('PAGE_NAME', 'shape');

	session_start();
		
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

	try {

		$Shapes = new clsShapes;
		$Dicts = new clsDicts;
		$Specs = new clsSpecs;
		
		$objShape = null;
		$objGroup = null;
				
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$ShapeId = null;
		$GroupId = null;
		$Name = '';
		$Description = '';
		$Publish = false;

		
		if (isset($_REQUEST['shapeid'])){
			$ShapeId = $_REQUEST['shapeid'];
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
				if ($ShapeId =='') {
					throw new exception("ShapeId not specified");
				}

				break;
		}

		if (!empty($ShapeId)){
			if (!isset($Shapes->Items[$ShapeId])){				
				throw new exception("Unknown Shape");
			}
			$objShape = $Shapes->Items[$ShapeId];
			$GroupId = $objShape->GroupId;
			$Name = $objShape->Name;
			$Description = $objShape->Description;
			$Publish = $objShape->Publish;
		}		

		if ($System->Session->Error){			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if (!empty($GroupId)){
			$objGroup = new clsGroup($GroupId);
		}		
		
		$Page->Title = $Mode." shape";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objShape->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objGroup->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objShape->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objShape->canEdit){
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
				$PanelB .= pnlShape( $ShapeId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objShape->canEdit === true){
					$PanelB .= "<li><a href='shape.php?shapeid=$ShapeId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objShape->canControl === true){
					$PanelB .= "<li><a href='shape.php?shapeid=$ShapeId&mode=delete'>&bull; delete</a></li> ";
				}
				
				if (count($objShape->ShapeClasses) > 0){				
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
										$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$objShape->getDot($VizStyle)."</script>";
										$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
										$Content .= "document.getElementById('graph1').innerHTML";
										$Content .= ",'svg');</script>";	
										$Content .= '</div>';
	
									$Content .= '<pre>'.htmlentities($objShape->getDot($VizStyle)).'</pre>';
										
										break;
									default:
										$Content .= "<img src='graphimage.php?shapeid=$ShapeId&style=$VizStyle'/>";									
										break;
								}
							}
	
							$TabContent .= $Content;
													
						$TabContent .= "</div>";
				    }
				}

			    $Tabs .= "<li><a href='#subjects'>Subjects";			    
			    $num = 0;
				$TabContent .= "<div class='tabContent hide' id='subjects'>";
				$TabContent .= "<h3>Subjects</h3>";
				
				
				if (count($objShape->ShapeClasses) > 0){
						
					$TabContent .= "<table class='list'>";
					$TabContent .= '<thead>';
					$TabContent .= '<tr>';
						$TabContent .= "<th>Id</th><th>Dictionary</th><th>Class</th>";
					$TabContent .= '</tr>';
					$TabContent .= '</thead>';

					
					$TabContent .= '<tbody>';					
					foreach ( $objShape->ShapeClasses as $objShapeClass){
						
						$num = $num + 1;
						
						$TabContent .= "<tr>";
						
						$TabContent .= "<td><a href='shapeclass.php?shapeclassid=".$objShapeClass->Id."&shapeid=".$objShape->Id."'>".$objShapeClass->Id."</a></td>";

						$TabContent .= "<td>".$objShapeClass->Class->DictId."</td>";						
						$TabContent .= "<td>".$objShapeClass->Class->Label."</td>";						
						$TabContent .= "</tr>";
						
					}
					$TabContent .= '</tbody>';					
					
					
			 		$TabContent .= '</table>';
				}
				
				$TabContent .= "<li><a href='shapeclass.php?shapeid=".$objShape->Id."&mode=new'>&bull; add</a></li> ";
								
				$TabContent .= "</div>";
				$Tabs .= "($num)</a></li>";
				
				
			    $Tabs .= "<li><a href='#links'>Links";			    
			    $num = 0;
				$TabContent .= "<div class='tabContent hide' id='links'>";
				$TabContent .= "<h3>Links</h3>";
				
				
				if (count($objShape->ShapeLinks) > 0){
						
					$TabContent .= "<table class='list'>";
					$TabContent .= '<thead>';
					$TabContent .= '<tr>';
						$TabContent .= "<th>Id</th><th>Link from</th><th>Relationship</th><th>Link to</th>";
					$TabContent .= '</tr>';
					$TabContent .= '</thead>';

					
					$TabContent .= '<tbody>';					
					foreach ( $objShape->ShapeLinks as $optShapeLink){
						
						$num = $num + 1;
						
						$optFromShapeClass = null;
						if (isset($objShape->ShapeClasses[$optShapeLink->FromShapeClassId])){
							$optFromShapeClass = $objShape->ShapeClasses[$optShapeLink->FromShapeClassId];
						}
						$optToShapeClass = null;
						if (isset($objShape->ShapeClasses[$optShapeLink->ToShapeClassId])){
							$optToShapeClass = $objShape->ShapeClasses[$optShapeLink->ToShapeClassId];
						}
						
						$TabContent .= "<tr>";
						
						$TabContent .= "<td><a href='shapelink.php?shapelinkid=".$optShapeLink->Id."&shapeid=".$objShape->Id."'>".$optShapeLink->Id."</a></td>";

						$TabContent .= "<td>";
						if (!is_null($optFromShapeClass)){
							$TabContent .= $optFromShapeClass->Class->Label;
						}
						$TabContent .= "</td>";
						
						$TabContent .= "<td>".$optShapeLink->Relationship->Label."</td>";						
						
						$TabContent .= "<td>";
						if (!is_null($optToShapeClass)){
							$TabContent .= $optToShapeClass->Class->Label;
						}
						$TabContent .= "</td>";
												
						$TabContent .= "</tr>";
						
					}
					$TabContent .= '</tbody>';					
					
					
			 		$TabContent .= '</table>';
				}
				
				$TabContent .= "<li><a href='shapelink.php?shapeid=".$objShape->Id."&mode=new'>&bull; add</a></li> ";
								
				$TabContent .= "</div>";
				$Tabs .= "($num)</a></li>";
								
				$Tabs .= "<li><a href='#group'>Group";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='group'>";
					$TabContent .= "<h3>in Group</h3>";	
					$TabContent .= pnlGroup($GroupId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
				

			    
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doShape.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!isemptystring($ShapeId)){
					$PanelB .= "<input type='hidden' name='shapeid' value='$ShapeId'/>";
				}
				if (!isemptystring($GroupId)){
					$PanelB .= "<input type='hidden' name='groupid' value='$GroupId'/>";
				}				
										
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $ShapeId;
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
							$PanelB .= '<input type="submit" value="Create New Shape">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Shape">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlShape( $ShapeId );
				
				$PanelB .= "<a href='doShape.php?shapeid=$ShapeId&mode=delete'>confirm delete?</a><br/>";
				
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

	
	
function funViewShapeClass($objParent = null){

	global $objShape;

	if (is_null($objParent)){
		$objParent = $objShape->Selection;
	}
	$objShapeClass = $objParent->ShapeClass;
	
	$Content = "";
	$Content .= pnlShapeClass($objShapeClass);
	if ($objShape->canEdit){
		$Content .="<div class='tab'>";
		$Content .= "<li><a href='shapeclass.php?shapeid=".$objShape->Id;
		
		if (!is_null($objShapeClass)){
			$Content .= "&shapeclassid=".$objShapeClass->Id;
		}
		elseif (get_class($objParent) == 'clsShapeLink'){
			$Content .= "&shapelinkid=".$objParent->Id;
		}
		
		$Content .= "&mode=edit'>&bull; edit</a></li> ";
		$Content .="</div>";
	}
	
	if (!is_null($objShapeClass)){
		if (!is_null($objShapeClass->Class)){
			$Content .= "<div class='tab'>";
			$Content .= "<h3>Links</h3>";
		
			$Content .= "<div class='tab'>";
			foreach ($objShapeClass->ShapeLinks as $objShapeLink){
				$Content .= pnlShapeLink($objShapeLink);
				
				if ($objShape->canEdit){
					$Content .= "<li><a href='shapelink.php?shapeid=".$objShape->Id."&shapelinkid=".$objShapeLink->Id."&mode=view'>&bull; view</a></li> ";					
//					$Content .= "<li><a href='shapelink.php?shapeid=".$objShape->Id."&shapelinkid=".$objShapeLink->Id."&mode=edit'>&bull; edit</a></li> ";
				}
				
				$Content .= "<div class='tab'>";
				$Content .= funViewShapeClass($objShapeLink);
				$Content .= "</div>";
			}
			$Content .= "</div>";
			
			
			if ($objShape->canEdit){
				$Content .= "<br/><li><a href='shapelink.php?shapeid=".$objShape->Id."&shapeclassid=".$objShapeClass->Id."&mode=new'>&bull; add new link</a></li>";
			}
			$Content .= "</div>";
		}
	}
	
	return $Content;
	
}


function funSelectParentShape($Selection='this'){

	global $System;
	global $Mode;
	
	global $Shapes;
	global $objShape;
	
	global $ReturnURL;

	$optShapeList = array();

	$Content = "";
	
	$Content .= "<div class='sdbluebox'>";

	
	$opts = array();

		
	switch ($Selection){
		case "this":
			foreach ($Shapes->Items as $optShape){
				if ($optShape->Id === $objShape->Id){
					continue;
				}
				if ($optShape->GroupId == $objShape->GroupId){					
					$opts[$optShape->Id] = $optShape;
				}
			}
			break;
			
		case "my":
			
			foreach ($Shapes->Items as $optShape){
				if ($optShape->Id === $objShape->Id){
					continue;
				}
				
				$optGroup = new clsGroup($optShape->GroupId);
				if (!$optGroup->canEdit){
					continue;
				}				
				
				$opts[$optShape->Id] = $optShape;
			}
			break;
			
		case "published":
			
			foreach ($Shapes->Items as $optShape){

				if ($optShape->Id === $objShape->Id){
					continue;
				}
				
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

			$ReturnUrl = 'doShapeParent.php?shapeid='.$objShape->Id.'&parentid='.$optShape->Id;										
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


?>