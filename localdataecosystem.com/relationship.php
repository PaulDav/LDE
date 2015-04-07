<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlClass.php");
	
	require_once("panel/pnlRel.php");
	
	require_once("class/clsDict.php");	
	require_once("class/clsModel.php");
	
	define('PAGE_NAME', 'relationship');

	session_start();
		
	$System = new clsSystem();
	
	
	$Page = new clsPage();

	$objModel = new clsModel();
			
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
		$RelId = '';
		$ConRel = '';
		$Label = '';
		$SubjectDictId = '';
		$SubjectId = '';
		$ObjectDictId = '';
		$ObjectId = '';
		$Description = '';
		$InverseLabel = '';
		$Cardinality = null;
		$Extending = false;
		$InverseExtending = false;
		
		$objRel = null;
		

		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if ($DictId =='') {
			throw new exception("DictId not specified");
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		$GroupId = $objDict->GroupId;
		
		if (isset($_REQUEST['relid'])){
			$RelId = $_REQUEST['relid'];
		}

		switch ($Mode){
			case 'new':
				break;
			default:
				if ($RelId =='') {
					throw new exception("RelId not specified");
				}
				break;
		}

		if (!empty($RelId)){
			$objRel = $objDict->Relationships[$RelId];
			$ConRel = $objRel->ConceptRelationship;
			$Label = $objRel->Label;
			$SubjectDictId = $objRel->SubjectDictId;
			$SubjectId = $objRel->SubjectId;
			$ObjectDictId = $objRel->ObjectDictId;
			$ObjectId = $objRel->ObjectId;
						
			$Description = $objRel->Description;
			
			$InverseLabel = $objRel->InverseLabel;
			$Cardinality = $objRel->Cardinality;
			$Extending = $objRel->Extending;
			$InverseExtending = $objRel->InverseExtending;
			
		}		
		
		SaveUserInput(PAGE_NAME);
		$FormFields = GetUserInput(PAGE_NAME);
		if (isset($FormFields['dictid'])){
			$DictId = $FormFields['dictid'];
			$objDict = $Dicts->Dictionaries[$DictId];
		}
		if (isset($FormFields['relid'])){
			$RelId = $FormFields['relid'];
		}
		
		if (isset($FormFields['subjectdictid'])){
			$SubjectDictId = $FormFields['subjectdictid'];
		}			
		if (isset($FormFields['subjectid'])){
			$SubjectId = $FormFields['subjectid'];
		}
		if (isset($FormFields['objectdictid'])){			
			$ObjectDictId = $FormFields['objectdictid'];
		}
		if (isset($FormFields['objectid'])){
			$ObjectId = $FormFields['objectid'];
		}
		
		if (isset($FormFields['label'])){
			$Label = $FormFields['label'];
		}
		if (isset($FormFields['conrel'])){
			$ConRel = $FormFields['conrel'];
		}
		
		if (isset($FormFields['description'])){
			$Description = $FormFields['description'];
		}
		if (isset($FormFields['inverselabel'])){
			$InverseLabel = $FormFields['inverselabel'];
		}
		
		unset($_SESSION['forms'][PAGE_NAME]);
		$System->Session->Clear('Error');			
		
		$UrlParams = array();
		if (!empty($SubjectDictId)){
			$UrlParams['subjectdictid'] = $SubjectDictId;
		}
		if (!empty($SubjectId)){
			$UrlParams['subjectid'] = $SubjectId;
		}
		if (!empty($ObjectDictId)){
			$UrlParams['objectdictid'] = $ObjectDictId;
		}
		if (!empty($ObjectId)){
			$UrlParams['objectid'] = $ObjectId;
		}
		
		$ReturnURL = UpdateUrl($UrlParams);
		
		
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." relationship";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objDict->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objDict->canEdit){
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
				$PanelB .= pnlRel( $DictId, $RelId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='relationship.php?dictid=$DictId&relid=$RelId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='relationship.php?dictid=$DictId&relid=$RelId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				

				
				
				if (!is_null($System->Config->DotRenderer)){
					$Tabs .= "<li><a href='#visualize'>Visualize</a></li>";
					$TabContent .= "<div class='tabContent hide' id='visualize'>";

					$vizstyles = array();
					$vizstyles[0] = "None";
					$vizstyles[2] = "Properties and Sub Classes";

					$VizStyle = 2;
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
						
					$Content .= '</table>';
						
					$Content .= "<input type='submit' value='Apply'/>";
					
					$Content .= '</form>';
						
					if ($VizStyle > 0){
						switch ($System->Config->DotRenderer){
							case "viz.js":
								$Content .= "<div id='viz'/>";
								$Page->Script .= "<script src='viz.js'></script>";
								$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$Dicts->getRelationshipDot($DictId, $RelId, $VizStyle)."</script>";
								$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
								$Content .= "document.getElementById('graph1').innerHTML";
								$Content .= ",'svg');</script>";
								$Content .= "</div>";
								
								break;
						}
						
//						$Content .= '<pre>'.htmlentities($Dicts->getRelationshipDot($DictId, $RelId, $VizStyle))."</pre>";
						
					}
						
					$TabContent .= $Content;
					
					$TabContent .= "</div>";
				}
				
				
				
				$Tabs .= "<li><a href='#dict'>Dictionary";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='dict'>";
					$TabContent .= "<div id='dict'><h3>in Dictionary</h3></div>";	
					$TabContent .= pnlDict($DictId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
			    $Tabs .= "<li><a href='#properties'>Properties";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='properties'>";
					$TabContent .= "<h3>Properties</h3>";
					
					$RelProperties = $Dicts->RelProperties($DictId,$RelId);
					if (count($RelProperties) > 0){

						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Relationship</th><th>Property</th><th>Description</th><th>Data Type</th><th>Lists</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						$PrevPropertyRel = null;
						foreach ( $RelProperties as $objHasProperty){
							$num = $num + 1;
							
							$TabContent .= "<tr>";

							if (!isset($Dicts->Dictionaries[$objHasProperty->DictId])){
								continue;
							}
							if (!isset($Dicts->Dictionaries[$objHasProperty->DictId]->Relationships[$objHasProperty->ParentId])){
								continue;
							}
							
							if (!isset($Dicts->Dictionaries[$objHasProperty->PropDictId])){
								continue;
							}
							if (!isset($Dicts->Dictionaries[$objHasProperty->PropDictId]->Properties[$objHasProperty->PropId])){
								continue;
							}
							
							$RelPropertyRel = $Dicts->Dictionaries[$objHasProperty->DictId]->Relationships[$objHasProperty->ParentId];

							$TabContent .= "<td>";
//							if (!($RelPropertyRel == $objRel)){								
								if (!($RelPropertyRel == $PrevPropertyRel)){
									$TabContent .= "<a href='relationship.php?dictid=".$objHasProperty->DictId."&relid=".$objHasProperty->ParentId."'>".$RelPropertyRel->Label."</a>";
								}
//							}
							$TabContent .= "</td>";
							
							$PrevPropertyRel = $RelPropertyRel;
							
							$RelPropertyProp = $Dicts->Dictionaries[$objHasProperty->PropDictId]->Properties[$objHasProperty->PropId];
							
							$objPropDict = $Dicts->Dictionaries[$objHasProperty->PropDictId];
							
							$objProperty = $objPropDict->Properties[$objHasProperty->PropId];
							
							$TabContent .= "<td><a href='hasproperty.php?dictid=".$objHasProperty->DictId."&relid=".$objHasProperty->ParentId."&haspropid=".$objHasProperty->Id."'>".$objProperty->Label."</a></td>";
							$TabContent .= "<td>".nl2br(Truncate($objProperty->Description))."</td>";
							$TabContent .= "<td>".$objProperty->Field->DataType."</td>";

							$TabContent .= "<td>";
							foreach ($objHasProperty->Lists as $objPropertyList){
								$objListDict = $Dicts->Dictionaries[$objPropertyList->ListDictId];
								if (isset($objListDict->Lists[$objPropertyList->ListId])){
									$objList = $objListDict->Lists[$objPropertyList->ListId];
									$TabContent .= "<a href='list.php?dictid=".$objListDict->Id."&listid=".$objPropertyList->ListId."'>".$objList->Label."</a>";									
								}
							}
							$TabContent .= "</td>";
														
							$TabContent .= "</tr>";
							
							
						}
				 		$TabContent .= '</table>';
						
					}
					
					
					if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='hasproperty.php?dictid=$DictId&relid=$RelId&mode=new'>&bull; add</a></li> </ul></div>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    			    
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doRel.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $DictId == '')){
					$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				}
				if (!( $RelId == '')){
					$PanelB .= "<input type='hidden' name='relid' value='$RelId'/>";
				}

				$PanelB .= '<table class="sdbluebox">';
				
				
				$ClassesSet = false;
				if (!empty($SubjectId) && !(empty($ObjectId))){
					$ClassesSet = true;
				}
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $RelId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}

				
				if (!empty($SubjectId)){
					if (empty($SubjectDictId) || $SubjectDictId == $DictId){
						$objSubjectDict = $objDict;
					}
					else
					{
						$objSubjectDict = $Dicts->Dictionaries[$SubjectDictId];
						$PanelB .= "<input type='hidden' name='subjectdictid' value='$SubjectDictId'/>";
					}
					
					
					
					$objSubject = $objSubjectDict->Classes[$SubjectId];
					$PanelB .= "<input type='hidden' name='subjectid' value='$SubjectId'/>";
					
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Subject';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
							$PanelB .= pnlClass($SubjectDictId,$SubjectId).'<br/>';
							$PanelB .= '<table>';
							$PanelB .= '<tr><th>Extended by?</td>';
							
							$PanelB .= '<td>';
							$PanelB .= "<input type='checkbox' name='extending' value='true'";
							if (!is_null($objRel)){
								if ($objRel->Extending === true){
									$PanelB .= " checked='true' ";
								}
							}
							$PanelB .= "</input>";						
							$PanelB .= '</td>';
							$PanelB .= '<tr>';
							
							
							$PanelB .= '<tr>';
								$PanelB .= '<th>';
								$PanelB .= 'Label';
								$PanelB .= '</th>';
								$PanelB .= '<td>';
								$PanelB .= '<input type="text" name="label" size="30" maxlength="100" value="'.$Label.'">';
								$PanelB .= '</td>';
							$PanelB .= '</tr>';
							
							$PanelB .= '</table>';
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
				}
				
				if (!empty($ObjectId)){
					if (empty($ObjectDictId) || $ObjectDictId == $DictId){
						$objObjectDict = $objDict;
					}
					else
					{
						$objObjectDict = $Dicts->Dictionaries[$ObjectDictId];
						$PanelB .= "<input type='hidden' name='objectdictid' value='$ObjectDictId'/>";
					}
					$objObject = $objObjectDict->Classes[$ObjectId];
					$PanelB .= "<input type='hidden' name='objectid' value='$ObjectId'/>";
										
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Object';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= pnlClass($ObjectDictId,$ObjectId).'<br/>';
						
						$PanelB .= '<table>';
							$PanelB .= '<tr><th>Extended by?</td>';
							
							$PanelB .= '<td>';
							$PanelB .= "<input type='checkbox' name='inverseextending' value='true'";
							if (!is_null($objRel)){
								if ($objRel->InverseExtending === true){
									$PanelB .= " checked='true' ";
								}
							}
							$PanelB .= "</input>";						
							$PanelB .= '</td>';
							$PanelB .= '<tr>';
							
							
							$PanelB .= '<tr>';
								$PanelB .= '<th>';
								$PanelB .= 'Inverse Label';
								$PanelB .= '</th>';
								$PanelB .= '<td>';
								$PanelB .= '<input type="text" name="inverselabel" size="30" maxlength="100" value="'.$InverseLabel.'">';
								$PanelB .= '</td>';
							$PanelB .= '</tr>';
							
							$PanelB .= '</table>';
						
						
						
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
				}
				
				
				if ($ClassesSet){
					
					$ConceptRelationships = array();
					$SubjectConceptId = "";
					$ObjectConceptId = "";
					foreach ($objModel->Concepts as $optConcept){
						if ($optConcept->Name == $objSubject->Concept){
							$SubjectConceptId = $optConcept->Id;
						}
						if ($optConcept->Name == $objObject->Concept){
							$ObjectConceptId = $optConcept->Id;
						}
					}
					

					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Concept Relationship';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						
						$optConRelIds = $objModel->getRelationships($SubjectConceptId,$ObjectConceptId);
							
						$PanelB .= "<select name='conrel'>";
						$PanelB .= "<option/>";
						
						foreach ($optConRelIds['normal'] as $optConRelId){
							$optConRel = $objModel->Relationships[$optConRelId];
							$PanelB .= "<option";
								if ($optConRel->Property == $ConRel){
									$PanelB .= " selected='true' ";
								}
							$PanelB .= ">".$optConRel->Property."</option>";
						}

						foreach ($optConRelIds['inverse'] as $optConRelId){
							$optConRel = $objModel->Relationships[$optConRelId];
							if (!($optConRel->InverseProperty == "")){
								$PanelB .= "<option";
									if ($optConRel->InverseProperty == $ConRel){
										$PanelB .= " selected='true' ";
									}
								$PanelB .= ">".$optConRel->InverseProperty."</option>";
							}
						}
						
						
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
						$PanelB .= 'Cardinality';
						$PanelB .= '</th>';
						$PanelB .= '<td>';						
						$PanelB .= "<select name='cardinality'>";
						$PanelB .= "<option/>";						
						foreach ($System->Config->RelCardinalities as $optCardinality){
							$PanelB .= "<option";
							if ($Cardinality == $optCardinality){
								$PanelB .= " selected='true' ";
							}
							$PanelB .= ">$optCardinality</option>";
						}
						$PanelB .= "</select>";						
						
						$PanelB .= '</td>';
					$PanelB .= '</tr>';	
					
				
					$PanelB .= '<tr>';
						$PanelB .= '<td/>';
						$PanelB .= '<td>';
						
						switch ( $Mode ){
							case "new":
								$PanelB .= '<input type="submit" value="Create New Relationship">';
								break;
							case "edit":
								$PanelB .= '<input type="submit" value="Update Relationship">';
								break;
						}
	
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
				}

			 	$PanelB .= '</table>';
				$PanelB .= '</form>';
				
				$Tabs .= "<li><a href='#setsubjectclass'>set the Subject Class</a></li>";

				$TabContent .= "<div class='tabContent hide' id='setsubjectclass'>";
									
				$TabContent .= "<h3>Set the Subject Class from any of these selections</h3>";	

				$optTabs = "";
				$optTabContent = "";
				
				$optTabs .= "<li><a href='#thissubjects'>Classes in this Group</a></li>";
				$optTabContent .= funSelectClass("this");				
				
				$optTabs .= "<li><a href='#mysubjects'>Classes in My Groups</a></li>";
				$optTabContent .= funSelectClass("my");
									
				$optTabs .= "<li><a href='#publishedsubjects'>Classes in Published Dictionaries</a></li>";			    
				$optTabContent .= funSelectClass("published");
				
				if (!empty($optTabs)){
					$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
				}
				

				$TabContent .= "</div>";

				
				
				
				$Tabs .= "<li><a href='#setobjectclass'>set the Object Class</a></li>";

				$TabContent .= "<div class='tabContent hide' id='setobjectclass'>";
									
				$TabContent .= "<h3>Set the Object Class from any of these selections</h3>";	
			
				$optTabs = "";
				$optTabContent = "";
				
				$optTabs .= "<li><a href='#thisobjects'>Classes in this Group</a></li>";
				$optTabContent .= funSelectClass("this","Object");
					
				$optTabs .= "<li><a href='#myobjects'>Classes in My Groups</a></li>";			    
				$optTabContent .= funSelectClass("my","Object");
									
				$optTabs .= "<li><a href='#publishedobjects'>Classes in Published Dictionaries</a></li>";			    
				$optTabContent .= funSelectClass("published","Object");

				if (!empty($optTabs)){
					$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
				}
				$TabContent .= "</div>";
				
				
				break;
				
			case 'delete':
				
				$PanelB .= pnlRel( $DictId, $RelId );

				$PanelB .= "<a href='doRel.php?dictid=$DictId&relid=$RelId&mode=delete'>confirm delete?</a><br/>";
				
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

	
function funSelectClass($Selection,$SubjectObject = "Subject"){

	
	global $System;
	global $Mode;
	global $objRel;
	global $objDict;
	
	global $Dicts;
	
	global $ReturnURL;
	
	$arrDictIds = array();
	switch ($Selection){
		case "this":
			$arrDictIds[] = $objDict->Id;
	}

	$Content = "";

	$TabId = "";
	switch ($SubjectObject){
		case "Subject":
			$TabId = $Selection."subjects";
			break;
		case "Object":
			$TabId = $Selection."objects";
			break;
	}
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$DictFieldName = "subjectdictid";
	$ClassFieldName = "subjectid";
	if ($SubjectObject == "Object"){
		$DictFieldName = "objectdictid";
		$ClassFieldName = "objectid";
	}
	
	

	$opts = array();

		
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if (is_null($optDict->EcoSystem)){
					if ($optDict->GroupId == $objDict->GroupId){					
						foreach ($optDict->Classes as $optClass){					
							$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
						}
					}
				}
			}
			break;
			
		case "my":
			
			foreach ($Dicts->Dictionaries as $optDict){
				if (is_null($optDict->EcoSystem)){
				
					$optGroup = new clsGroup($optDict->GroupId);
					if (!$optGroup->canEdit){
						continue;
					}				
					
					foreach ($optDict->Classes as $optClass){
						$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
					}
				}
			}
			break;
			
		case "published":
			
			foreach ($Dicts->Dictionaries as $optDict){

				if (!$optDict->Publish){
					continue;
				}				

				foreach ($optDict->Classes as $optClass){
					$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
				}
			}

			break;
			
	}
		
	if (count($opts) > 0){
		
		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Dictionary</th><th>Concept</th><th>Class</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
					
		foreach ($opts as $optDictId=>$optConcepts){
		

			$Content .= "<tr>";
			$numRows = 1;
			
			foreach ($optConcepts as $optClasses){
				$numRows = $numRows + 1;
				foreach ($optClasses as $optClass){
					$numRows = $numRows + 1;
				}
			}
					
			$Content .= "<td rowspan='$numRows'>";
				$optDict = $Dicts->Dictionaries[$optDictId];
				$Content .= $optDict->Name;
			$Content .= "</td>";
			$Content .= "</tr>";
		
		
			foreach ($optConcepts as $optConcept=>$optClasses){

				$Content .= "<td rowspan='".(count($optClasses) + 1)."'>";
					$Content .= $optConcept;
				$Content .= "</td>";
				$Content .= "</tr>";
				
				foreach ($optClasses as $optClass){
					
					$UrlParams = array();
					$UrlParams[$DictFieldName] = $optDictId;
					$UrlParams[$ClassFieldName] = $optClass->Id;
					$ReturnUrl = UpdateUrl($UrlParams);					
					
					$Content .= "<tr><td><a href='$ReturnUrl'>".$optClass->Label."</a></td>";
					$Content .= "<td>".nl2br($optClass->Description)."</td>";
					$Content .= "</tr>";
				}
			}
	
		}
		$Content .= "</tbody>";
		$Content .= "</table>";
		
	}
	
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}
	
	
	

	
?>