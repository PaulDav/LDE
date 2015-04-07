<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlDocument.php");
	
	
	require_once("class/clsGroup.php");
	require_once("class/clsDocument.php");
	require_once("class/clsRights.php");
	require_once("class/clsShape.php");	
	require_once("class/clsDict.php");
	require_once("panel/pnlSubject.php");
	require_once("panel/pnlStatement.php");
	
	
	
	define('PAGE_NAME', 'document');

	session_start();

	$System = new clsSystem();			
	$Page = new clsPage();
	

	$Script = "\n";
	
	$Script .= "<link rel='stylesheet' href='//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css'>";
	
  	// $Script .= "<script src='//code.jquery.com/jquery-1.10.2.js'></script>";
  	$Script .= "<script src='jquery/jquery-1.11.1.min.js'></script>";
  	  	
  	
  	$Script .= "<script src='//code.jquery.com/ui/1.10.4/jquery-ui.js'></script>";	
//	$Script .= "<script type='text/javascript' src='java/jquery.js'></script>";
	$Script .= "<script type='text/javascript' src='java/datepicker.js'></script>";
		
	$Page->Script .= $Script;
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";
	
	
	try {

		$Dicts = new clsDicts();
		$Shapes = new clsShapes();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		
		$DocId = null;
		$SetId = null;
		$ShapeId = null;
				
		
		$objDoc = null;
		$objShape = null;
		

		if (isset($_REQUEST['docid'])){
			$DocId = $_REQUEST['docid'];
		}
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}

		if (isset($_REQUEST['shapeid'])){
			$ShapeId = $_REQUEST['shapeid'];
		}
		

		if (!empty($DocId)){
			$objDoc = new clsDocument($DocId);			
			$SetId = $objDoc->SetId;
			if (!is_null($objDoc->objShape)){
				$ShapeId = $objDoc->ShapeId;
			}
		}
		else
		{
			$objDoc = new clsDocument();
			$objDoc->ShapeId = $ShapeId;
		}
		
		if ($System->Session->Error){
			$FormFields = GetUserInput(PAGE_NAME);
		}
		unset($_SESSION['forms'][PAGE_NAME]);
		$System->Session->Clear('Error');			
		

		switch ($Mode){
			case 'new':
				if (IsEmptyString($SetId)) {
					throw new exception("SetId not specified");
				}
				if (IsEmptyString($ShapeId)) {
					throw new exception("ShapeId not specified");
				}
				
				break;
			default:
				if (IsEmptyString($DocId)) {
					throw new exception("DocId not specified");
				}

				break;
		}


		$objSet = new clsSet($SetId);
		$objOrg = $Orgs->Items[$objSet->OrgId];
		
		$objShape = null;
		$ShapeName = null;
		if (!isset($Shapes->Items[$ShapeId])){					
			$Page->ErrorMessage .= "Shape not set <br/>";
		}		
		$objShape = $Shapes->Items[$ShapeId];
		$ShapeName = $objShape->Name;
		
		if ($objOrg->canView === false){
			throw new exception("You cannot view this Organisation");
		}

		
		$Page->Title = $Mode." ".$ShapeName." document";
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
			case 'new':
			case 'edit':

				if (!is_null($DocId)){
					$PanelB .= pnlDocument( $DocId );
					$PanelB .= "<li><a href='document.php?setid=$SetId&docid=$DocId&mode=delete'>delete</a></li> ";
				}
				
				if (is_object($objDoc)){
				
					if (!is_null($System->Config->DotRenderer)){			    
					    $Tabs .= "<li><a href='#visualize'>Visualize</a></li>";
						$TabContent .= "<div class='tabContent hide' id='visualize'>";
		
							$vizstyles = array();
							$vizstyles[0] = "None";
							$vizstyles[3] = "Entities";
							$vizstyles[1] = "Entities in Concept Packages";
							$vizstyles[2] = "Subjects";
							$vizstyles[4] = "Subjects in Datasets";
							
							$VizStyle = 2;
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

			
							$VizFormat = 1;
							if (isset($_REQUEST['vizformat'])){
								$VizFormat = $_REQUEST['vizformat'];
							}
							$Content .= '<tr>';
								$Content .= '<th>';
								$Content .= 'Format';
								$Content .= '</th>';
								$Content .= '<td>';
								$Content .= "<select name='vizformat'>";
								foreach ($System->Config->VizFormats as $optVizFormatCode=>$optVizFormat){
									$Content .= "<option value='$optVizFormatCode' ";
									if ($VizFormat == $optVizFormatCode){
										$Content .= " selected='true' ";
									}
									$Content .= ">$optVizFormat</option>";
								}
								$Content .= "</select>";
								$Content .= '</td>';
							$Content .= '</tr>';												
							
							$Content .= '</table>';
							
							$Content .= "<input type='submit' value='Apply'/>";
							
							$Content .= '</form>';
							
							if ($VizStyle > 0){
								switch ($VizFormat){
									case 1:
										switch ($System->Config->DotRenderer){
											case "viz.js":
												$Content .= "<div id='viz'/>";
												$Page->Script .= "<script src='viz.js'></script>";
												$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$objDoc->getDot($VizStyle)."</script>";
												$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
												$Content .= "document.getElementById('graph1').innerHTML";
												$Content .= ",'svg');</script>";	
												$Content .= '</div>';
															
												break;
										}
										break;
									case 9:
										$Content .= '<pre>'.htmlentities($objDoc->getDot($VizStyle)).'</pre>';
										break;
								}
							}
	
							$TabContent .= $Content;
													
						$TabContent .= "</div>";
				    }
				}
				
			    $Tabs .= "<li><a href='#subjects'>Subjects";
				$num = 0;
				if (!is_null($objDoc)){				
					foreach ($objDoc->SubjectForms as $objSubjectForm){
						if (!($objSubjectForm->CreateExtended)){						
							$num += 1;
						}
					}
				}

				if (!is_null($objShape)){				
				
					$TabContent .= "<div class='tabContent hide' id='subjects'>";			
								
					
					foreach ($objDoc->BlankSubjectForms as $objBlankSubjectForm){
						$optShapeClass = $objBlankSubjectForm->ShapeClass;
						
						// ignore if this is an extended class
						if (!($objBlankSubjectForm->CreateExtended)){
						
							$TabContent .= "<h3>".$optShapeClass->Class->Label."</h3>";	
							
							$TabContent .= "<div class='tab'>";
							$TabContent .= pnlDocShapeClassForms($objDoc, $optShapeClass);
							
							if ($objSet->canEdit === true){
								
								$canAddSubject = false;
								if ($optShapeClass->Create){
									$canAddSubject = true;
								}

								if ($canAddSubject){								
									$TabContent .= "<li><a href='documentsubject.php?setid=$SetId";
									if (!is_null($DocId)){
										$TabContent .= "&docid=$DocId";
									}
									$TabContent .= "&shapeid=$ShapeId&shapeclassid=".$optShapeClass->Id."&mode=new'>add</a></li> ";
								}
							}
							$TabContent .= '</div>';
						}
					}
					
					
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";				
				    
					$Tabs .= "<li><a href='#links'>Links";
					$num = 0;
					if (!is_null($objDoc)){
						$num = count($objDoc->LinkForms);
					}
				    
				    
				    $TabContent .= "<div class='tabContent hide' id='links'>";
									
				    
				    foreach ($objDoc->BlankLinkForms as $objBlankLinkForm){
						$optShapeLink = $objBlankLinkForm->ShapeLink;
				    						
						if ($objBlankLinkForm->CreateExtended === true){
							continue;
						}						
						
						$optFromShapeClass = $objShape->ShapeClasses[$optShapeLink->FromShapeClassId];
						$optToShapeClass = $objShape->ShapeClasses[$optShapeLink->ToShapeClassId];
						$optRelLabel = $optShapeLink->Relationship->Label;
						if ($optShapeLink->Inverse === true){
							$optRelLabel = $optShapeLink->Relationship->InverseLabel;
						}
						
						$TabContent .= "<h3>".$optFromShapeClass->Class->Label." $optRelLabel ".$optToShapeClass->Class->Label."</h3>";	
						
						$TabContent .= "<div class='tab'>";
						
						$TabContent .= pnlDocShapeLinkForms($objDoc, $optShapeLink);
						
						if ($objSet->canEdit === true){
							$TabContent .= "<li><a href='documentlink.php?setid=$SetId";
							if (!is_null($DocId)){
								$TabContent .= "&docid=$DocId";
							}
							$TabContent .= "&shapeid=$ShapeId&shapelinkid=".$optShapeLink->Id."&mode=new'>add</a></li> ";
						}
						
						$TabContent .= '</div>';
						
					}
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";	
				}		
									
			    if (is_object($objDoc)){
				    $Tabs .= "<li><a href='#statements'>Statements";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='statements'>";
						$TabContent .= "<h3>Statements</h3>";	
	
						if (count($objDoc->Statements) > 0){
							
							$TabContent .= "<table class='list'>";
							$TabContent .= '<thead>';
							$TabContent .= '<tr>';
								$TabContent .= "<td>Doc</td><th>Id</th><th>Type</th><th>About</th><th>Subject</th><th>Link</th><th>Value</th><th>Eff From</th><th>Eff To</th>";
							$TabContent .= '</tr>';
							$TabContent .= '</thead>';
						
							foreach ( $objDoc->Statements as $objStat){
								$num = $num + 1;
								
								$TabContent .= "<tr>";
	
								$TabContent .= "<td>".$objStat->DocId."</td>";
								
								$TabContent .= "<td><a href='statement.php?statid=".$objStat->Id."'>".$objStat->Id."</a></td>";
								
								$TabContent .= "<td>".$objStat->TypeLabel."</td>";
								
								$TabContent .= "<td>".$objStat->AboutId."</td>";
								
								$TabContent .= "<td>".$objStat->SubjectId."</td>";
								
								$TabContent .= "<td>";
								if (!is_null($objStat->LinkId)){
									$objDictItem = new clsDictItem($objStat->LinkDictId, $objStat->LinkId, $objStat->TypeId);
									$TabContent .= $objDictItem->Label;
								}
								$TabContent .= "</td>";
								
								$TabContent .= "<td>";
								if (!is_null($objStat->ObjectId)){
									$TabContent .= $objStat->ObjectId;
								}
								elseif (!is_null($objStat->Value)){
									$TabContent .= nl2br(Truncate($objStat->Value));								
								}
								$TabContent .= "</td>";
								
								$TabContent .= "<td>".$objStat->EffectiveFrom."</td>";
								$TabContent .= "<td>".$objStat->EffectiveTo."</td>";
															
								$TabContent .= "</tr>";
								
							}
					 		$TabContent .= '</table>';
						}
											
						
					$TabContent .= "</div>";
				    $Tabs .= "($num)</a></li>";
			    }
			    
			    $Tabs .= "<li><a href='#set'>Set";
				$TabContent .= "<div class='tabContent hide' id='set'>";
				$TabContent .= "<h3>Set</h3>";	
				$TabContent .= pnlSet($objSet);

				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
				break;
				
			case 'delete':
				
				$PanelB .= pnlDocument( $DocId);
				
				$PanelB .= "<li><a href='doDoc.php?docid=$DocId&mode=delete'>confirm delete?</a></li>";

				$PanelB .= "<br/>";

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
	





function funSelectSubject($objClass, $FieldName=null){
	
	$Content = "";
			
	$Selection = "this";
	
	$TabId = "";
	$TabId = "subjects";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$opts = array();

	$Action = UpdateUrl();
	
	switch ($Selection){
		case "this":
			$Content .= pnlClassSubjects($objClass->DictId, $objClass->Id, null, $FieldName, $Action);
			break;			
	}
				
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}



function frmForm($objForm){

	global $Page;
	
	global $Dicts;
	global $objProfile;

	$Content  = '';

//	echo '<pre>'.htmlentities($objForm->xml).'</pre>';
	
	$Script = "";	
	$Script .= "<script>\n";
	$Script .= "var xmlForm = ".json_encode($objForm->xml).";\n\n";
	$Script .= "</script>\n";
	
	
	$Page->Script = $Script .$Page->Script;
	
	$Content .= "<div id='divForm'></div>";
	
	return $Content;
								
}

function frmLinkForm($objLinkForm){
		
	global $Page;
	
	global $Dicts;
	global $objProfile;
	
//	echo '<pre>'.htmlentities($objLinkForm->xml).'</pre>';
	
	$Content = "";
							
	$Content .= "<table class='sdbluebox'>";
	
	if ($objLinkForm->EffDates){
		$Content .= "<tr><th>Effective From</th><td>";
		$Content .= "<input name='relefffrom' size=20 type='date' value='".$objLinkForm->EffectiveFrom."' class='datepicker' id='relefffrom'/>";
		$Content .= "</td></tr>";
		$Content .= "<tr><th>Effective To</th><td>";
		$Content .= "<input name='releffto' size=20 type='date' value='".$objLinkForm->EffectiveTo."' class='datepicker' id='releffto'/>";
		$Content .= "</td></tr>";	
	}
	$Content .= '</table>';
	
	$Script = "";
	
	$Script .= "<script>\n";
	$Script .= "var xmlLinkForm = ".json_encode($objLinkForm->xml).";\n\n";
	$Script .= "</script>\n";
	
	
	$Page->Script = $Script .$Page->Script;
	
	$Content .= "<div id='divLinkForm'></div>";
	
	return $Content;
		
}

function funSelectProfileClass(){

	global $System;
	global $Mode;
	
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