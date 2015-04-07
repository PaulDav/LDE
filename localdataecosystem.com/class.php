<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlClass.php");
	require_once("panel/pnlClassViz.php");
	
	require_once("class/clsDict.php");	
	require_once("class/clsModel.php");
	
	define('PAGE_NAME', 'class');

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
		$ClassId = '';
		$Concept = '';
		$Label = '';
		$Description = '';
		$Heading = '';
		$Source = '';
				

		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if ($DictId =='') {
			throw new exception("DictId not specified");
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		$GroupId = $objDict->GroupId;
		
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
		}

		switch ($Mode){
			case 'new':
				break;
			default:
				if ($ClassId =='') {
					throw new exception("ClassId not specified");
				}
				break;
		}

		if (!empty($ClassId)){
			$objClass = $objDict->Classes[$ClassId];
			$Label = $objClass->Label;
			$Concept = $objClass->Concept;
			$Description = $objClass->Description;
			$Heading = $objClass->Heading;	
			$Source = $objClass->Source;
		}		
		
		
		if ($System->Session->Error){
			$FormFields = GetUserInput(PAGE_NAME);
			if (isset($FormFields['dictid'])){
				$DictId = $FormFields['dictid'];
				$objDict = $Dicts->Dictionaries[$DictId];
			}
			if (isset($FormFields['classid'])){
				$ClassId = $FormFields['classid'];
			}
			
			if (isset($FormFields['concept'])){
				$Concept = $FormFields['concept'];
			}
			if (isset($FormFields['label'])){
				$Label = $FormFields['label'];
			}
			if (isset($FormFields['description'])){
				$Description = $FormFields['description'];
			}
			if (isset($FormFields['heading'])){
				$Heading = $FormFields['heading'];
			}
			if (isset($FormFields['source'])){
				$Source = $FormFields['source'];
			}
			
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." class";		
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
				$PanelB .= pnlClass( $DictId, $ClassId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='class.php?dictid=$DictId&classid=$ClassId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='class.php?dictid=$DictId&classid=$ClassId&mode=delete'>&bull; delete</a></li> ";
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
								$Content .= "<script type='text/vnd.graphviz' id='graph1'>".$Dicts->getClassDot($objClass->DictId, $objClass->Id, $VizStyle)."</script>";
								$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
								$Content .= "document.getElementById('graph1').innerHTML";
								$Content .= ",'svg');</script>";
								$Content .= "</div>";
								
								break;
						}
						
//						$Content .= '<pre>'.htmlentities($Dicts->getClassDot($objClass->DictId, $objClass->Id, $VizStyle)).'</pre>';
						
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
					

					$ClassProperties = $Dicts->ClassProperties($DictId,$ClassId);
					if (count($ClassProperties) > 0){

						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>EcoSystem</th><th>Class</th><th>Property</th><th>Description</th><th>Data Type</th><th>Lists</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						$PrevPropertyClass = null;
						
						foreach ( $ClassProperties as $objClassProperty){
							$num = $num + 1;
							
							$TabContent .= "<tr>";

							if (!isset($Dicts->Dictionaries[$objClassProperty->DictId])){
								continue;
							}
							if (!isset($Dicts->Dictionaries[$objClassProperty->DictId]->Classes[$objClassProperty->ClassId])){
								continue;
							}
							
							if (!isset($Dicts->Dictionaries[$objClassProperty->PropDictId])){
								continue;
							}
							if (!isset($Dicts->Dictionaries[$objClassProperty->PropDictId]->Properties[$objClassProperty->PropId])){
								continue;
							}

							$ClassPropertyDict = $Dicts->Dictionaries[$objClassProperty->DictId];
							$ClassPropertyClass = $ClassPropertyDict->Classes[$objClassProperty->ClassId];

							
							if (!($ClassPropertyClass == $PrevPropertyClass)){
								$TabContent .= "<td>";
								$TabContent .= $ClassPropertyDict->EcoSystem."</a>";
								$TabContent .= "</td>";
								$TabContent .= "<td>";
								$TabContent .= "<a href='class.php?dictid=".$objClassProperty->DictId."&classid=".$objClassProperty->ClassId."'>".$ClassPropertyClass->Label."</a>";
								$TabContent .= "</td>";
							}
							else
							{
								$TabContent .= "<td/><td/>";
							}
							
							$PrevPropertyClass = $ClassPropertyClass;
							
							$ClassPropertyProp = $Dicts->Dictionaries[$objClassProperty->PropDictId]->Properties[$objClassProperty->PropId];
							
							$objPropDict = $Dicts->Dictionaries[$objClassProperty->PropDictId];
							
							$objProperty = $objPropDict->Properties[$objClassProperty->PropId];
							
							$TabContent .= "<td><a href='classproperty.php?dictid=".$objClassProperty->DictId."&classid=".$objClassProperty->ClassId."&classpropid=".$objClassProperty->Id."'>".$objProperty->Label."</a></td>";
							$TabContent .= "<td>".nl2br(Truncate($objProperty->Description))."</td>";

							$TabContent .= '<td>';
							switch ($objProperty->Type){
								case 'simple':
									$TabContent .= $objProperty->Field->DataType;
									break;
								default:
									$TabContent .= $objProperty->Type;
									break;
							}
							$TabContent .= '</td>';
							
							$TabContent .= "<td>";
							foreach ($objProperty->Lists as $objPropertyList){
								$objListDict = $Dicts->Dictionaries[$objPropertyList->ListDictId];
								if (isset($objListDict->Lists[$objPropertyList->ListId])){
									$objList = $objListDict->Lists[$objPropertyList->ListId];
									$TabContent .= "<a href='list.php?dictid=".$objListDict->Id."&listid=".$objPropertyList->ListId."'>".$objList->Label."</a><br/>";									
								}
							}
							$TabContent .= "</td>";
														
							$TabContent .= "</tr>";
							
							
						}
				 		$TabContent .= '</table>';
						
					}
					
					
					if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='classproperty.php?dictid=$DictId&classid=$ClassId&mode=new'>&bull; add</a></li> </ul></div>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    
			    $Tabs .= "<li><a href='#subclasses'>Sub Classes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='subclasses'>";
					$TabContent .= "<h3>Sub Classes</h3>";

					$arrSubClasses = array();
					foreach ($Dicts->Dictionaries as $optDict){
						foreach ($optDict->Classes as $optClass){
							if (($optClass->SubDictOf == $DictId) && ($optClass->SubClassOf == $ClassId )){
								$arrSubClasses[$optClass->DictId][$optClass->Id] = $optClass;
							}
						}
					}
					

					if (count($arrSubClasses) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Dictionary</th><th>Id</th><th>Label</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';

						foreach ($arrSubClasses as $SubDictId=>$SubClasses){
							$TabContent .= "<tr>";
							$TabContent .= "<td rowspan='".(count($SubClasses) + 1)."'>";
							$optDict = $Dicts->Dictionaries[$SubDictId];
							$TabContent .= $optDict->Name;
							$TabContent .= "</td>";
							$TabContent .= "</tr>";

							foreach ( $SubClasses as $SubClass){
								
								$num = $num + 1;
								
								$TabContent .= "<tr>";								
								$TabContent .= "<td><a href='class.php?dictid=".$SubClass->DictId."&classid=".$SubClass->Id."'>".$SubClass->Id."</a></td>";
								$TabContent .= "<td>".$SubClass->Label."</td>";
								$TabContent .= "<td>".nl2br(Truncate($SubClass->Description))."</td>";
								$TabContent .= "</tr>";
								
							}
						}
				 		$TabContent .= '</table>';
					}
					
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";

			    $Tabs .= "<li><a href='#sameas'>Same As Classes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='sameas'>";
				
					$TabContent .= "<h3>Same As Classes</h3>";					

					$TabContent .= "<table class='list'>";
					$TabContent .= '<thead>';
					$TabContent .= '<tr>';
						$TabContent .= "<th>Id</th><th>Dictionary</th><th>Class</th><th>Description</th>";
					$TabContent .= '</tr>';
					$TabContent .= '</thead>';

					foreach ($Dicts->SameAsClasses($DictId, $ClassId) as $SameAsClassSeq=>$SameAsClass){
						$num = $num + 1;
							
						$TabContent .= "<tr>";
							
						$TabContent .= "<td><a href='sameasclass.php?dictid=$DictId&classid=$ClassId&sameasdictid=".$SameAsClass->DictId."&sameasclassid=".$SameAsClass->Id."'>".$SameAsClassSeq."</a></td>";
						$TabContent .= "<td>".$SameAsClass->DictId."</td>";
						$TabContent .= "<td>".$SameAsClass->Label."</td>";
						$TabContent .= "<td>".nl2br(Truncate($SameAsClass->Description))."</td>";
						
						$TabContent .= "</tr>";
					}
			 		$TabContent .= '</table>';
			 		
			 		if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='sameasclass.php?dictid=$DictId&classid=$ClassId&mode=new'>&bull; add</a></li> </ul></div>";
					}
			 							
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    $Tabs .= "<li><a href='#relationships'>Relationships";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='relationships'>";
					$TabContent .= "<h3>Relationships</h3>";
					
					$arrClassRels = array_merge($Dicts->RelationshipsFor($DictId, $ClassId),$Dicts->RelationshipsFor(null,null,$DictId, $ClassId));
					
					if (count($arrClassRels) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>EcoSystem</th><th>Dictionary</th><th>Label</th><th>Description</th><th>Related Class</th><th>Extending?</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';

						foreach ($arrClassRels as $objClassRel){
							
							$num = $num + 1;							
							
							$TabContent .= "<tr>";
							$objClassRelDict = $Dicts->Dictionaries[$objClassRel->DictId];
							$TabContent .= "<td>".$objClassRelDict->EcoSystem."</td>";
							$TabContent .= "<td>".$objClassRelDict->Name."</td>";
							
							$RelSubjectClass = $Dicts->getClass($objClassRel->SubjectDictId, $objClassRel->SubjectId);
							$RelObjectClass = $Dicts->getClass($objClassRel->ObjectDictId, $objClassRel->ObjectId);

							$RelLabel = $objClassRel->Label;
							$RelExtending = $objClassRel->Extending;
							$objRelatedClass = $RelObjectClass;
							if (!($RelSubjectClass === $objClass)){
								if ($RelObjectClass === $objClass){
									$objRelatedClass = $RelSubjectClass;
									$RelLabel = $objClassRel->InverseLabel;
									$RelExtending = $objClassRel->InverseExtending;
								}
							}
							$TabContent .= "<td><a href='relationship.php?dictid=".$objClassRel->DictId."&relid=".$objClassRel->Id."'>".$RelLabel."</a></td>";
							$TabContent .= "<td>".nl2br(Truncate($objClassRel->Description))."</td>";
							
							$TabContent .= "<td><a href='class.php?dictid=".$objRelatedClass->DictId."&classid=".$objRelatedClass->Id."'>".$objRelatedClass->Label."</a></td>";
							$TabContent .= '<td>';
							if ($RelExtending){
								$TabContent .= "Extending";
							}
							$TabContent .= '</td>';
							$TabContent .= '</tr>';
						}
				 		$TabContent .= '</table>';
					}
					
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    
			    
			    
			    if ( $objDict->canEdit ){
					$Tabs .= "<li><a href='#setsuperclass'>set the Super Class</a></li>";
				    
					$TabContent .= "<div class='tabContent hide' id='setsuperclass'>";
					
					if (!is_null($objClass->SubClassOf)){
						$TabContent .= "<a href='doSuperClass.php?mode=delete&dictid=$DictId&classid=$ClassId'>remove super class</a><br/>";
					}
										
					$TabContent .= "<h3>Set the Super Class from any of these selections</h3>";	
				
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#thissuperclasses'>Classes in this Group</a></li>";
					$optTabContent .= funSelectClass("this");
					
					$optTabs .= "<li><a href='#mysuperclasses'>Classes in My Groups</a></li>";
					$optTabContent .= funSelectClass("my");
										
					$optTabs .= "<li><a href='#publishedsuperclasses'>Classes in Published Dictionaries</a></li>";			    
					$optTabContent .= funSelectClass("published");

					if (!empty($optTabs)){
						$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					$TabContent .= "</div>";
				}
				
				$Tabs .= "<li><a href='#visualizer'>Visualizer</a></li>";
			    
				$TabContent .= "<div class='tabContent hide' id='visualizer'>";									
				$TabContent .= "<h3>Visualizer</h3>";
				if ($objDict->canEdit){
					$TabContent .= "<form method='POST' action='classviz.php?dictid=$DictId&classid=$ClassId&mode=edit'>";
					$TabContent .= "Choose a visualizer: <select name='viztypeid'>";
					$TabContent .= "<option/>";
					foreach ($System->Config->Visualizers as $optVisualizer){
						$TabContent .= "<option value='".$optVisualizer->Id."'>".$optVisualizer->Name."</option>";
					}
					$TabContent .= "</select>";
					$TabContent .= "<input type='submit' value='set paramters'/>";
					$TabContent .= "</form>";
				}
				
				if (!is_null($objClass->Viz)){
					$TabContent .= pnlClassViz($DictId, $ClassId);
					
					$TabContent .= "<div class='hmenu'><ul>";
					if ($objDict->canEdit === true){
						$TabContent .= "<li><a href='classviz.php?dictid=$DictId&classid=$ClassId&mode=edit'>&bull; edit</a></li> ";
					}
					if ($objDict->canControl === true){
						$TabContent .= "<li><a href='classviz.php?dictid=$DictId&classid=$ClassId&mode=delete'>&bull; delete</a></li> ";
					}
					$TabContent .= "</ul></div>";
				}
				$TabContent .= "</div>";

				
				

				
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doClass.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $DictId == '')){
					$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				}
				if (!( $ClassId == '')){
					$PanelB .= "<input type='hidden' name='classid' value='$ClassId'/>";
				}

				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $ClassId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Label';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="label" size="30" maxlength="100" value="'.$Label.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Concept';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<select name='concept'>";
					$PanelB .= "<option/>";
					foreach ($objModel->Concepts as $optConcept){
						$PanelB .= "<option";
						if ($optConcept->Name == $Concept){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">".$optConcept->Name."</option>";
					}
					$PanelB .= "</select>";
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
					$PanelB .= 'Heading';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="heading" size="30" maxlength="100" value="'.$Heading.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Source';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<textarea rows = "5" cols = "80" name="source" >';
					$PanelB .= $Source;
					$PanelB .= '</textarea>';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';				
				
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Class">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Class">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlClass( $DictId, $ClassId );

				$PanelB .= "<a href='doClass.php?dictid=$DictId&classid=$ClassId&mode=delete'>confirm delete?</a><br/>";
				
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
	
function funSelectClass($Selection){

	global $System;
	global $Mode;
	global $objClass;
	global $objDict;
	
	global $Dicts;

	$Content = "";

	$Content .= "<div class='tabContent' id='".$Selection."superclasses'>";
	
	$Content .= "<div class='sdbluebox'>";
	

	$opts = array();
	
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				
				if (is_null($optDict->EcoSystem)){
					if ($optDict->GroupId == $objDict->GroupId){					
						foreach ($optDict->Classes as $optClass){
							if ($optClass->DictId == $objDict->Id){
								if ($optClass->Id == $objClass->Id){
									continue;
								}
							}
						
							if (!($optClass->Concept == $objClass->Concept)){
								continue;
							}
							
							$opts[$optDict->Id][$optClass->Id] = $optClass;
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
						if ($optClass->DictId == $objDict->Id){
							if ($optClass->Id == $objClass->Id){
								continue;
							}
						}
					
						if (!($optClass->Concept == $objClass->Concept)){
							continue;
						}
						$opts[$optDict->Id][$optClass->Id] = $optClass;
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
					if ($optClass->DictId == $objDict->Id){
						if ($optClass->Id == $objClass->Id){
							continue;
						}
					}
				
					if (!($optClass->Concept == $objClass->Concept)){
						continue;
					}
					
					$opts[$optDict->Id][$optClass->Id] = $optClass;
				}
			}

			break;
			
	}

	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Dictionary</th><th>Class</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optDictId=>$optClasses){
			
			$Content .= "<tr>";
			$Content .= "<td rowspan='".(count($optClasses) + 1)."'>";
				$optDict = $Dicts->Dictionaries[$optDictId];
				$Content .= $optDict->Name;
			$Content .= "</td>";
			$Content .= "</tr>";

			foreach ($optClasses as $optClass){
				
				$ReturnUrl = "doSuperClass.php?mode=edit&dictid=".$objDict->Id."&classid=".$objClass->Id."&superdictid=$optDictId&superclassid=".$optClass->Id;
				
				$Content .= "<tr><td><a href='$ReturnUrl'>".$optClass->Label."</a></td>";
				$Content .= "<td>".nl2br($optClass->Description)."</td>";
				$Content .= "</tr>";
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