<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");

	require_once("function/utils.inc");

	require_once("class/clsData.php");
	require_once("class/clsDict.php");
	require_once("class/clsRights.php");

	require_once("panel/pnlSubject.php");
	require_once("panel/pnlSubjectViz.php");
	require_once("panel/pnlDocument.php");

	require_once("panel/pnlClassSubjects.php");
	require_once("panel/pnlRelLinks.php");
	
	define('PAGE_NAME', 'subject');

	session_start();
		
	$System = new clsSystem();


	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
	
	try {

		$Dicts = new clsDicts();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		

		$SubjectId = null;
		$AsAtDocumentId = null;

		if (isset($_REQUEST['subjectid'])){
			$SubjectId = $_REQUEST['subjectid'];
		}		

		if (empty($SubjectId)){
			throw new exception("Subject Not Specified");
		}
		$objSubject = new clsSubject($SubjectId);

		
		if (isset($_REQUEST['asatdocumentid'])){
			$AsAtDocumentId = $_REQUEST['asatdocumentid'];
			$objSubject->AsAtDocumentId = $AsAtDocumentId;
		}		
		
		
		$Page->Title = "subject";		
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
				$PanelB .= pnlSubject($SubjectId, $AsAtDocumentId);
				
// get linkRels
/*
				$arrLinkTypes = array();
				foreach ($Subject->Links as $objLink){
					$objRel = $Dicts->getRelationship($objLink->RelDictId, $objLink->RelId);
					if (is_object($objRel)){
						switch ($objRel->Cardinality){
							case 'extend':
								break;
							default:
								if ($objLink->SubjectId == $SubjectId){
									$arrLinkTypes[$objLink->RelDictId][$objLink->RelId][false] = $objRel;
								}
								elseif ($objLink->ObjectId == $SubjectId){
									$arrLinkTypes[$objLink->RelDictId][$objLink->RelId][true] = $objRel;
								}								
								break;
						}
					}
						
				}
*/
				$arrLinkTypes = array();
				foreach ($objSubject->Links as $objLink){
					$objRel = $Dicts->getRelationship($objLink->RelDictId, $objLink->RelId);
					if (is_object($objRel)){
						if ($objLink->SubjectId == $SubjectId){
							$arrLinkTypes[$objLink->RelDictId][$objLink->RelId][false] = $objRel;
						}
						elseif ($objLink->ObjectId == $SubjectId){
							$arrLinkTypes[$objLink->RelDictId][$objLink->RelId][true] = $objRel;
						}								
					}
				}
				
				
				if (count($arrLinkTypes) > 0){
//					$PanelB .= "<h4>Links</h4>";
					$PanelB .= "<div class='tab'>";
					
					foreach ($arrLinkTypes as $RelDictId=>$arrDictLinkTypes){
						foreach ($arrDictLinkTypes as $RelId=>$arrInverse){
							foreach ($arrInverse as $Inverse=>$objRel){
								
								$ObjectClassLabel = '';								
								switch ($Inverse){
									case false:
										
										$objObjectClass = $Dicts->getClass($objRel->ObjectDictId, $objRel->ObjectId);
										if (is_object($objObjectClass)){
											$ObjectClassLabel = $objObjectClass->Label;
										}
										
										$PanelB .= "<h5>".$objRel->Label." $ObjectClassLabel</h5>";
										$PanelB .= "<div class='tab'>".pnlRelLinks($objSubject, $objRel, false)."</div>";									
										break;									
									default:
										$objObjectClass = $Dicts->getClass($objRel->SubjectDictId, $objRel->SubjectId);
										if (is_object($objObjectClass)){
											$ObjectClassLabel = $objObjectClass->Label;
										}
										
										$PanelB .= '<h5>'.$objRel->InverseLabel." $ObjectClassLabel</h5>";
										$PanelB .= "<div class='tab'>".pnlRelLinks($objSubject, $objRel, true)."</div>";									
										break;
								}
							}
						}						
					}
				}
				$PanelB .= "</div>";				
				
				
				/*
				
				$Statements = $Subject->getStatements();				
				$arrLinks = array();
				foreach ($Statements as $Statement){
					if ($Statement->TypeId == '300'){
						if ($Statement->SubjectId == $SubjectId){
							$Object = new clsSubject($Statement->ObjectId);
							$arrLinks[$Statement->LinkDictId][$Statement->LinkId][false][$Object->ClassDictId][$Object->ClassId][] = $Statement->ObjectId;
						}
						elseif ($Statement->ObjectId == $SubjectId){
							$Object = new clsSubject($Statement->SubjectId);
							$arrLinks[$Statement->LinkDictId][$Statement->LinkId][true][$Object->ClassDictId][$Object->ClassId][] = $Statement->SubjectId;
						}
					}
				}

				$PanelB .= "<h4>Links</h4>";
				$PanelB .= "<div class='tab'>";
				foreach ($arrLinks as $LinkDictId=>$arrLinkIds){
					foreach ($arrLinkIds as $LinkId=>$arrInverse){
						foreach ($arrInverse as $Inverse=>$arrClassDictIds){
						
							$Rel = $Dicts->Dictionaries[$LinkDictId]->Relationships[$LinkId];
							
							switch ($Inverse){
								case false:
									$PanelB .= '<h5>'.$Rel->Label.'</h5>';
									$PanelB .= "<div class='tab'>".pnlRelLinks($Subject, $Rel)."</div>";									
									break;									
								default:
									$PanelB .= '<h5>'.$Rel->InverseLabel.'</h5>';
									$PanelB .= "<div class='tab'>".pnlRelLinks($Subject, $Rel, true)."</div>";									
									break;
							}
						}						
					}
				}
				$PanelB .= "</div>";
*/

				
				
				
				$VizContent = pnlSubjectViz($SubjectId, $Page);
				if (!IsEmptyString($VizContent)){
					$Tabs .= "<li><a href='#viz1'>Visualizations</a></li>";
					$TabContent .= "<div class='tabContent hide' id='viz1'>";
					$TabContent .= $VizContent;
					$TabContent .= "</div>";	
				}

				if (!is_null($System->Config->DotRenderer)){
					$Tabs .= "<li><a href='#visualize'>Visualize</a></li>";
					$TabContent .= "<div class='tabContent hide' id='visualize'>";
	
						$vizstyles = array();
						$vizstyles[0] = "None";
						$vizstyles[3] = "Entities";
						$vizstyles[1] = "Entities in Concept Packages";						
						$vizstyles[2] = "Subjects";
						$vizstyles[4] = "Subjects in Datasets";
						
						
						$VizStyle = 4;
						if (isset($_REQUEST['vizstyle'])){
							$VizStyle = $_REQUEST['vizstyle'];
						}
				
											
						$Content = "";
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
											$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$objSubject->getDot($VizStyle)."</script>";
											$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
											$Content .= "document.getElementById('graph1').innerHTML";
											$Content .= ",'svg');</script>";
											$Content .= "</div>";
											
											break;
										default:
											$Content .= "<img src='graphimage.php?subjectid=$SubjectId&style=$VizStyle'  usemap='#subjectmap'/>";
											break;
									}
									break;
								case 9:
									$Content .= '<pre>'.htmlEntities($objSubject->getDot($VizStyle)).'</pre>';
									break;
							}
						}
						
						$TabContent .= $Content;
					
					$TabContent .= "</div>";
				}
				
				$Tabs .= "<li><a href='#matches'>Matches";
				$num = 0;
			    			    
			    $TabContent .= "<div class='tabContent hide' id='matches'>";

			    $TabContent .= "<table class='list'>";
				$TabContent .= '<thead>';
				$TabContent .= '<tr>';
				$TabContent .= "<th>Document</th><th>Matched to</th><th>Class</th><th>Set</th><th>by Organisation</th>";
				$TabContent .= '</tr>';
				$TabContent .= '</thead>';
					
				foreach ($objSubject->Matches as $objMatch){
					
					$SameAsSubjectId = $objMatch->SameAsSubjectId;
					if ($SameAsSubjectId == $SubjectId){
						$SameAsSubjectId = $objMatch->SubjectId;
					}

					$num = $num + 1;
					
					$DocId = $objMatch->Statement->DocId;
					
					$objSameAsSubject = new clsSubject($SameAsSubjectId);
					$objSameAsSubject->AsAtDocumentId = $AsAtDocumentId;

					$objSameAsClass = $Dicts->getClass($objSameAsSubject->ClassDictId, $objSameAsSubject->ClassId );
					$objSameAsDoc = new clsDocument($objSameAsSubject->CreatedDocumentId);
					$objSameAsSet = new clsSet($objSameAsDoc->SetId);
					$SameAsSetName = $objSameAsSet->Name;
					if (isset($Orgs->Items[$objSameAsSet->OrgId])){
						$objSameAsOrg = $Orgs->Items[$objSameAsSet->OrgId];
					}
					
					$TabContent .= "<tr>";
					$TabContent .= "<td><a href='document.php?docid=$DocId'>$DocId</a></td>";
										
					$TabContent .= "<td>";
					if (is_object($objSameAsSubject)){
						$TabContent .= "<a href='subject.php?subjectid=".$objSameAsSubject->Id."'>".$objSameAsSubject->Label."</a>";
					}
					$TabContent .= "</td>";
					
					$TabContent .= "<td>";
					if (is_object($objSameAsClass)){
						$TabContent .= "<a href='class.php?dictid=".$objSameAsClass->DictId."&classid=".$objSameAsClass->Id."'>".$objSameAsClass->Label."</a>";						
					}
					$TabContent .= "</td>";

					$TabContent .= "<td>";
					if (is_object($objSameAsSet)){
						$TabContent .= "<a href='set.php?setid=".$objSameAsSet->Id."'>".$objSameAsSet->Name."</a>";						
					}
					$TabContent .= "</td>";

					$TabContent .= "<td>";
					if (is_object($objSameAsOrg)){
						$TabContent .= "<a href='organisation.php?orgid=".$objSameAsOrg->Id."'>".$objSameAsOrg->Name."</a>";						
					}
					$TabContent .= "</td>";
										
					$TabContent .= "</tr>";
					
				}
				
				$TabContent .= "</table>";
				
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";		
			    
			    
			    
				
				
				
				$Tabs .= "<li><a href='#log'>Log of Changes</a></li>";
				$TabContent .= "<div class='tabContent hide' id='log'>";
				$TabContent .= "<h3>Log of Changes</h3>";

				$TabContent .= "<table class='sdgreybox'>";
				$TabContent .= "<thead><tr><th/><th>Data Set</th><th>Date</th><th>Organisation</th><th>Document</th><th>Form</th><th>Link</th></thead></tr>";
				
				foreach ($objSubject->DocumentIds as $LogDocId){
					$objLogDocument = new clsDocument($LogDocId);
					$objLogSet = new clsSet($objLogDocument->SetId);
					
					$objLogOrg = null;
					if (isset($Orgs->Items[$objLogSet->OrgId])){
						$objLogOrg = $Orgs->Items[$objLogSet->OrgId];
					}
					
					$objLogForm = null;
					if (isset($objLogDocument->SubjectForms[$SubjectId])){
						$objLogForm = $objLogDocument->SubjectForms[$SubjectId];
					}
					
//					$objLogSubject = new clsSubject($SubjectId);
//					$objLogSubject->AsAtDocumentId = $LogDocId;
					
					$arrLogLinks = array();
																	
					foreach ($objLogDocument->LinkForms as $objLinkForm){
						if ($objLinkForm->FromId == $SubjectId){
							$arrLogLinks[$objLinkForm->LinkId] = $objLinkForm;
						}
						if ($objLinkForm->ToId == $SubjectId){
							$arrLogLinks[$objLinkForm->LinkId] = $objLinkForm;
						}
						
					}
					
					
					$TabContent .= "<tr>";
					
					$TabContent .= "<td><a href='subject.php?subjectid=$SubjectId&asatdocumentid=$LogDocId'>&bull; view</a></td>";
					
					$TabContent .= "<td><a href='set.php?setid=".$objLogSet->Id."'>".$objLogSet->Name."</a></td>";
					
					$TabContent .= "<td>";
					if ($objLogSet->CurrentLog->StatusText == 'published'){
						$TabContent .= $objLogSet->CurrentLog->DateTime;
					}
					$TabContent .= "</td>";
					
					$TabContent .= "<td>";
					if (!is_null($objLogOrg)){					
						$TabContent .= $objLogOrg->Name;
					}
					$TabContent .= "</td>";
					
					$TabContent .= "<td><a href='document.php?docid=$LogDocId'>$LogDocId</a></td>";

					$TabContent .= "<td>";					
					if (is_object($objLogForm)){
						$TabContent .= pnlForm($objLogForm);
					}
					$TabContent .= "</td>";
					
					
					$TabContent .= "<td>";					
					foreach ($arrLogLinks as $objLogLinkForm){
						// put this back
						/*
						if (!is_null($objLogLinkForm->ObjectForm->SubjectId)){
							$Inverse = false;
							$LogLinkTargetId = $objLogLinkForm->ObjectForm->SubjectId;
							
							if ($objLogLinkForm->ObjectForm->SubjectId == $SubjectId){
								$Inverse = true;
								$LogLinkTargetId = $objLogLinkForm->ParentForm->SubjectId;
							}
							
							$LogLinkRelLabel = $objLogLinkForm->Relationship->Label;
							if (!$Inverse){
								if ($objLogLinkForm->Inverse === true){
									$Inverse = true;
								}
							}
							else
							{
								if ($objLogLinkForm->Inverse === true){
									$Inverse = false;
								}
							}

							if ($Inverse){
								$LogLinkRelLabel = $objLogLinkForm->Relationship->InverseLabel;
							}
							$TabContent .= $LogLinkRelLabel;
							$TabContent .= pnlLinkForm($objLogLinkForm);
							
							$TabContent .= pnlSubject($LogLinkTargetId, $LogDocId);
						}
						*/
					}
					$TabContent .= "</td>";
					
					
					
					
					$TabContent .= "</tr>";

				}

				$TabContent .= "</table>";
				
				
				$TabContent .= "</div>";	
				
				
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