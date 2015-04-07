<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
	
	require_once("panel/pnlOrg.php");
	require_once("panel/pnlSet.php");
	require_once("panel/pnlDocument.php");
	require_once("panel/pnlSelectSubject.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDocument.php");
	require_once("class/clsRights.php");
	require_once("class/clsShape.php");	
	require_once("class/clsDict.php");
	require_once("panel/pnlClassSubjects.php");
	require_once("panel/pnlSubject.php");
	require_once("panel/pnlStatement.php");
	require_once("panel/pnlClassFilters.php");
	
	
	define('PAGE_NAME', 'documentsubject');

	session_start();

	$System = new clsSystem();			
	$Page = new clsPage();
	

	$Script = "\n";
	
	$Script .= "<link rel='stylesheet' href='//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css'>";	
  	$Script .= "<script src='jquery/jquery-1.11.1.min.js'></script>";
  	
  	$Script .= "<script src='//code.jquery.com/ui/1.10.4/jquery-ui.js'></script>";	
	$Script .= "<script type='text/javascript' src='java/datepicker.js'></script>";
	
	$Script .= "<script type='text/javascript' src='java/utils.js'></script>";
	
	$Script .= "<script type='text/javascript' src='java/ajax.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClasses.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClassSubjects.js'></script>";
	
	
	$Page->Script .= $Script;
	
	$InitScript = '<script>';	
	$InitScript .= "function init(){ \n";
	$InitScript .= "    setDatePicker();";
	
	
	$PanelB = '';
	$PanelC = '';
	
	$Tabs = "";
	$TabContent = "";
	
	$AjaxDivNum = 0;
	global $AjaxDivNum;
	
	
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
		$ShapeClassId = null;
		$SubjectId = null;
				
		
		$objDoc = null;
		$objForm = null;
		$objShape = null;
		$objShapeClass = null;
		

		if (isset($_REQUEST['docid'])){
			$DocId = $_REQUEST['docid'];
		}

		if (isset($_REQUEST['subjectid'])){
			$SubjectId = $_REQUEST['subjectid'];
		}
		
		
		if (isset($_REQUEST['setid'])){
			$SetId = $_REQUEST['setid'];
		}

		if (isset($_REQUEST['shapeid'])){
			$ShapeId = $_REQUEST['shapeid'];
		}
		
		if (isset($_REQUEST['shapeclassid'])){
			$ShapeClassId = $_REQUEST['shapeclassid'];
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
			if (isset($Shapes->Items[$ShapeId])){			
				$objDoc->objShape = $Shapes->Items[$ShapeId];
			}
		}

		
		if (IsEmptyString($SetId)) {
			throw new exception("SetId not specified");
		}

				
		if ($System->Session->Error){
			$FormFields = GetUserInput(PAGE_NAME);
		}
		unset($_SESSION['forms'][PAGE_NAME]);
		$System->Session->Clear('Error');			
		

		switch ($Mode){
			case 'new':
				if (IsEmptyString($ShapeId)) {
					throw new exception("ShapeId not specified");
				}
				if (IsEmptyString($ShapeClassId)) {
					throw new exception("ShapeClassId not specified");
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

		if (!isset($Shapes->Items[$ShapeId])){		
			throw new exception("Unknown Shape");
		}
		
		$objShape = $Shapes->Items[$ShapeId];

		
		switch ($Mode){
			case 'view':
			case 'edit':
			case 'delete':
				if (!isset($objDoc->SubjectForms[$SubjectId])){
					throw new Exception("Subject not in the Document");
				}
				$objForm = $objDoc->SubjectForms[$SubjectId];
				$ShapeClassId = $objForm->ShapeClass->Id;
				break;
			case 'new':
				
				if (!isset($objDoc->BlankSubjectForms[$ShapeClassId])){
					throw new Exception("Invalid Shape Class");
				}
				break;
		}
				
		$objShapeClass = null;
		if (!is_null($ShapeClassId)){
			if (!isset($objShape->ShapeClasses[$ShapeClassId])){		
				throw new exception("Unknown Shape Class");
			}
			$objShapeClass = $objShape->ShapeClasses[$ShapeClassId];
		}
		
		
		switch ($Mode){
			case 'new':				
				
				if (is_null($SubjectId)){
					$objForm = $objDoc->BlankSubjectForms[$ShapeClassId];
				}
				else
				{
					$objForm = new clsForm($objShapeClass, $SubjectId);
				}				
				
				break;
		}
				
		
		
		if ($objOrg->canView === false){
			throw new exception("You cannot view this Organisation");
		}
		
		$Page->Title = "$Mode subject in document";
		$PanelB .= "<h1>".$Page->Title."</h1>";

		$PanelB .= '<h3>'.$objShapeClass->Class->Label.'</h3>';

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
				
				$PanelB .= pnlForm($objForm);
				
				if ($objSet->canEdit === true){
					$PanelB .= "<li><a href='documentsubject.php?docid=$DocId&subjectid=$SubjectId&shapeclassid=$ShapeClassId&mode=edit'>edit</a></li>";
					$PanelB .= "<li><a href='documentsubject.php?docid=$DocId&subjectid=$SubjectId&mode=delete'>delete</a></li>";
				}				
				
				
				if (!is_null($System->Config->DotRenderer)){			    
				    $Tabs .= "<li><a href='#visualize'>Visualize</a></li>";
					$TabContent .= "<div class='tabContent hide' id='visualize'>";
	
						$vizstyles = array();
						$vizstyles[0] = "None";
						$vizstyles[1] = "Entities";
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
											$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$objForm->getDot($VizStyle)."</script>";
											$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
											$Content .= "document.getElementById('graph1').innerHTML";
											$Content .= ",'svg');</script>";	
											$Content .= '</div>';
											
											break;
									}
									break;
								case 9:
									$Content .= '<pre>'.htmlentities($objForm->getDot($VizStyle)).'</pre>';
									break;
							}
									
						}

						$TabContent .= $Content;
												
					$TabContent .= "</div>";
			    }
			    
			    
			    
			    $Tabs .= "<li><a href='#matches'>Matches to Reference Subjects";
				$num = 0;
			    			    
			    $TabContent .= "<div class='tabContent hide' id='matches'>";

			    $TabContent .= "<table class='list'>";
				$TabContent .= '<thead>';
				$TabContent .= '<tr>';
				$TabContent .= "<th>Document</th><th>Statement Id</th><th>Matched to</th><th>Class</th><th>Set</th><th>by Organisation</th>";
				$TabContent .= '</tr>';
				$TabContent .= '</thead>';
					
				foreach ($objForm->SameAsStatements as $objSameAsStatement){

					$num = $num + 1;
					
					$objSameAsSubject = new clsSubject($objSameAsStatement->ObjectId);
					$objSameAsSubject->AsAtDocumentId = $DocId;

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
					if (is_object($objSameAsStatement)){
						$TabContent .= "<a href='statement.php?statid=".$objSameAsStatement->Id."'>".$objSameAsStatement->Id."</a>";
					}
					$TabContent .= "</td>";
					
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
			    
			    
			    
			    
			    
			    
			    
			    
			    $Tabs .= "<li><a href='#links'>Links";
				$num = 0;
			    			    
				if (!is_null($objDoc)){
					foreach ($objDoc->LinkForms as $objLinkForm){														
						$countForm = false;
						if ($objLinkForm->FromId == $SubjectId){
							$countForm = true;
						}
						if ($objLinkForm->ToId == $SubjectId){
							$countForm = true;
						}
						if ($countForm){
							$num += 1;
						}							
					}
				}					
				
				
			    $TabContent .= "<div class='tabContent hide' id='links'>";
								
				foreach ($objShape->ShapeLinks as $optShapeLink){
					
					$useShapeLink = false;
					
					$Reverse = false;
					if ($optShapeLink->FromShapeClassId == $objShapeClass->Id){
						$useShapeLink = true;						
					}
					if ($optShapeLink->ToShapeClassId == $objShapeClass->Id){
						$useShapeLink = true;					
						$Reverse = true;
					}
					if (!$useShapeLink){
						continue;
					}
					
					
					$optFromShapeClass = $objShape->ShapeClasses[$optShapeLink->FromShapeClassId];
					$optToShapeClass = $objShape->ShapeClasses[$optShapeLink->ToShapeClassId];
					$optRelLabel = $optShapeLink->Relationship->Label;
					if ($optShapeLink->Inverse === true){
						$optRelLabel = $optShapeLink->Relationship->InverseLabel;
					}
					
					$TabContent .= "<h3>".$optFromShapeClass->Class->Label." $optRelLabel ".$optToShapeClass->Class->Label."</h3>";	
					
					$LinkForms = $objDoc->getSubjectLinkForms($SubjectId, $optShapeLink);
					
					$TabContent .= "<div class='tab'>";					
					
					$TabContent .= pnlDocShapeLinkForms($objDoc, $optShapeLink, $SubjectId);
					
					if ($objSet->canEdit === true){
// check if another link can be added

						$canAddLink = true;
						switch ($optShapeLink->Cardinality){
							case 'one':
								if (count($LinkForms) > 0){
									$canAddLink = false;									
								}
								break;
						}
						
						if ($canAddLink){

							$TabContent .= "<li><a href='documentlink.php?setid=$SetId";
							if (!is_null($DocId)){
								$TabContent .= "&docid=$DocId";
							}
							$TabContent .= "&shapeid=$ShapeId&shapelinkid=".$optShapeLink->Id.'&mode=new';
							
							switch ($Reverse){
								case true:
									$TabContent .= "&toid=$SubjectId#linkfrom";								
									break;
								default:
									$TabContent .= "&fromid=$SubjectId#linkto";
									break;
							}
							$TabContent .= "'>add</a></li> ";
						}
					}
					
					$TabContent .= '</div>';
					
				}
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";		
					
			    
			    $Tabs .= "<li><a href='#statements'>Statements";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='statements'>";
					$TabContent .= "<h3>Statements</h3>";	

					if (count($objForm->Statements) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<td>Doc</td><th>Id</th><th>Type</th><th>About</th><th>Subject</th><th>Link</th><th>Value</th><th>Eff From</th><th>Eff To</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objForm->Statements as $objStat){
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
				

				break;
			case 'new':
			case 'edit':
				
				$Script = '';
				$Script .= "<script type='text/javascript' src='java/loadForm.js'></script>";
				$Page->Script .= $Script;
				
				$InitScript .= "    loadForm(); ";
				
				$FormPanel = "";
				$ThisDocPanel = "";
				$SelectPanel = "";
				$MatchPanel = "";
				
				$useForm = false;
				
				if ($objShapeClass->Create){
					$useForm = true;
				}

				if (!is_null($SubjectId)){
					if ($objShapeClass->Match){
						$useForm = true;
					}
				}
				else
				{
					
					if ($objShapeClass->Select){
					
/*						
						$SelectPanel .= pnlSubject($SubjectId);
						
						$SelectPanel .= '<form method="post" action="doDocSubject.php">';					
						
						$SelectPanel .= "<input type='hidden' name='mode' value='$Mode'/>";
						if (!IsEmptyString($ShapeId)){
							$SelectPanel .= "<input type='hidden' name='shapeid' value='$ShapeId'/>";
						}
							
						if (!IsEmptyString($ShapeClassId)){
							$SelectPanel .= "<input type='hidden' name='shapeclassid' value='$ShapeClassId'/>";
						}						
								
						if (!IsEmptyString($DocId)){
							$SelectPanel .= "<input type='hidden' name='docid' value='$DocId'/>";
						}
						if (!IsEmptyString($SetId)){
							$SelectPanel .= "<input type='hidden' name='setid' value='$SetId'/>";
						}				
	
						$SelectPanel .= "<input type='hidden' name='subjectid' value='$SubjectId'/>";
						
						$SelectPanel .= "<input type='submit' value='Add this ".$objShapeClass->Class->Label."'</input>";
						
						$UrlParams = array();
						$UrlParams['subjectid'] = '';
						$ReturnUrl = UpdateUrl($UrlParams);
						$SelectPanel .= "<li><a href='$ReturnUrl'>select another ".$objShapeClass->Class->Label."</a></li>";						
						
						$SelectPanel .= '</form>';
		*/				

						$SelectPanel .= "<h3>Select a ".$objShapeClass->Class->Label." from all datasets</h3>";
						$SelectPanel .= pnlSelectSubject($objShapeClass->Class, 'all');

					}

					if ($objShapeClass->Match){
						$MatchPanel .= "<h3>Select from reference datasets</h3>";
						$MatchPanel .= pnlSelectSubject($objShapeClass->Class, 'reference');

						// xxxxxxxxxxxxx

//						foreach ($Dicts->SameAsClasses($objShapeClass->Class->DictId, $objShapeClass->Class->Id) as $SameAsClass){
//							$MatchPanel .= funSelectSubject($SameAsClass, 'reference');
//						}																		
					}

					if ($objShapeClass->Create){					
						$ThisDocPanel .= "<h3>Select a ".$objShapeClass->Class->Label." from this dataset</h3>";	
						$ThisDocPanel .= pnlSelectSubject($objShapeClass->Class);												
					}
				}


				if ($useForm){				
					$FormPanel .= '<form method="post" action="doDocSubject.php">';					
						
					$FormPanel .= "<input type='hidden' name='mode' value='$Mode'/>";
					if (!IsEmptyString($ShapeId)){
						$FormPanel .= "<input type='hidden' name='shapeid' value='$ShapeId'/>";
					}
						
					if (!IsEmptyString($ShapeClassId)){
						$FormPanel .= "<input type='hidden' name='shapeclassid' value='$ShapeClassId'/>";
					}						
							
					if (!IsEmptyString($DocId)){
						$FormPanel .= "<input type='hidden' name='docid' value='$DocId'/>";
					}
					if (!IsEmptyString($SetId)){
						$FormPanel .= "<input type='hidden' name='setid' value='$SetId'/>";
					}				

					if (!IsEmptyString($SubjectId)){
						$FormPanel .= "<input type='hidden' name='subjectid' value='$SubjectId'/>";
					}

					$FormPanel .= frmForm($objForm);
					
					if (is_null($SubjectId)){
						$FormPanel .= "<input type='submit' value='Create a new ".$objShapeClass->Class->Label."'</input>";
					}
					else
					{
						$FormPanel .= "<input type='submit' value='Update ".$objShapeClass->Class->Label."'</input>";
					}
						
					$FormPanel .= '</form>';
				}
				
				if ($useForm){					
					$Tabs .= "<li><a href='#form'>";
					if (is_null($SubjectId)){					
						$Tabs .= 'Create';
					}
					else
					{
						$Tabs .= 'Edit';
					}
					
					$Tabs .= "</a></li>";
					$TabContent .= "<div class='tabContent hide' id='form'>";
						$TabContent .= $FormPanel;
					$TabContent .= "</div>";
				}
				
				if (is_null($SubjectId)){
				
					if ($objShapeClass->Create){					
						$Tabs .= "<li><a href='#thisdoc'>Select from this Document</a></li>";
						$TabContent .= "<div class='tabContent hide' id='thisdoc'>";
							$TabContent .= $ThisDocPanel;
						$TabContent .= "</div>";										
					}
						
					if ($objShapeClass->Select){						
						$Tabs .= "<li><a href='#select'>Select from all Subjects</a></li>";
						$TabContent .= "<div class='tabContent hide' id='select'>";
							$TabContent .= $SelectPanel;
						$TabContent .= "</div>";										
					}
	
					if ($objShapeClass->Match){						
						$Tabs .= "<li><a href='#match'>Match to Reference Data</a></li>";
						$TabContent .= "<div class='tabContent hide' id='match'>";
							$TabContent .= $MatchPanel;
						$TabContent .= "</div>";										
					}
				}
				
				break;
				
			case 'delete':
				
				$PanelB .= pnlForm($objForm);
				
				if ($objSet->canEdit === true){
					$PanelB .= "<li><a href='doDocSubject.php?docid=$DocId&subjectid=$SubjectId&mode=delete'>confirm delete</a></li>";
				}				

				break;

		}
		
		if (!is_null($DocId)){
			$Tabs .= "<li><a href='#document'>Document";
			$TabContent .= "<div class='tabContent hide' id='document'>";								
			$TabContent .= "<h3>Document</h3>";						
			$TabContent .= pnlDocument($DocId);
			$TabContent .= "</div>";
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

	$InitScript .= "} \n";
	$InitScript .= "</script>\n";
	$Page->Script .= $InitScript;
	
	$Page -> Display();
	
function frmForm($objForm){

	global $Page;
	
//	global $Dicts;
//	global $objProfile;

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