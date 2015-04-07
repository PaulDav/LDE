<?php
	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");

	require_once("panel/pnlShape.php");
	require_once("panel/pnlGroup.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsShape.php");
	
	require_once("form/frmSelectClass.php");
	require_once("form/frmShapeProperties.php");
	
	
	define('PAGE_NAME', 'shapeclass');

	session_start();
		
	$System = new clsSystem();
	
		
	$Page = new clsPage();
	
	if ($js = file_get_contents('java/jquery.js')){
		$Page->Script .= $js;
	}
		

	try {
		
		global $Dicts;
		$Dicts = new clsDicts();

		global $Shapes;
		$Shapes = new clsShapes();
				
		$Mode = 'edit';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
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
		$ShapeClassId = null;
		$objShapeClass = null;
		
		
		$ClassDictId = null;
		$ClassId = null;
		
		$Create = true;
		$Select = true;
		$Match = true;

		
		if (isset($_REQUEST['shapeid'])){
			$ShapeId = $_REQUEST['shapeid'];
		}

		if (isset($_REQUEST['shapeclassid'])){
			$ShapeClassId = $_REQUEST['shapeclassid'];
		}
				
		if (isset($_REQUEST['classdictid'])){
			$ClassDictId = $_REQUEST['classdictid'];
		}
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
		}
		
		if (is_null($ShapeId)) {
			throw new exception("ShapeId not specified");
		}

		if (!isset($Shapes->Items[$ShapeId])){
			throw new exception("Unknown Shape Id");
		}
		$objShape = $Shapes->Items[$ShapeId];
		$objParentShape = null;
				
		$GroupId = $objShape->GroupId;

		$objClass = null;
		$SuperClasses = null;
		
		if (!is_null($objParentShape)){
			$SuperClasses = array();			
			foreach ($objParentShape->ShapeClasses as $objParentShapeClass){
				$SuperClasses[] = $objParentShapeClass->Class;		
			}
		}		
				
		
		if (!is_null($ShapeClassId)){
			$objShapeClass = $objShape->ShapeClasses[$ShapeClassId];
		}
		
				
		if (!is_null($objShapeClass)){
			$objClass = $objShapeClass->Class;
			if (!is_null($objClass)){
				$ClassDictId = $objClass->DictId;
				$ClassId = $objClass->Id;
			}
			$Create = $objShapeClass->Create;
			$Select = $objShapeClass->Select;
			$Match = $objShapeClass->Match;
			
		}
		
		if (!is_null($ClassId)){
			$objClass = $Dicts->getClass($ClassDictId, $ClassId);
			if ($objClass === false){
				throw new exception("Invalid Class");
			}
		}
		

		if ($System->Session->Error){			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		
		$Fields = null;
				
		$Page->Title = $Mode." shape class";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objShape->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
			case 'edit':
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
		
		
		$Tabs .= "<li><a href='#shape'>Shape";
		$TabContent .= "<div class='tabContent hide' id='shape'>";
		$TabContent .= "<h3>Shape</h3>";	
		$TabContent .= pnlShape($ShapeId);
		$TabContent .= "</div>";
	    $Tabs .= "</a></li>";

		
		switch ($Mode){
			case 'view':
				
				$PanelB .= "<h3>Class</h3>";	
				$PanelB .= pnlShapeClass($objShapeClass);
				if ($objShape->canEdit){
					$PanelB .= "<li><a href='shapeclass.php?shapeid=$ShapeId&shapeclassid=$ShapeClassId&mode=edit'>edit</a></li> ";					
					$PanelB .= "<li><a href='shapeclass.php?shapeid=$ShapeId&shapeclassid=$ShapeClassId&mode=delete'>delete</a></li> ";
				}
				
				break;
			
			case 'new':
			case 'edit':
				
				if (is_null($objClass)){
					
					$PanelB .= "<h3>Select a Class</h3>";
					
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#thisclasses'>Classes in this Group</a></li>";
					
					$optTabContent .= "<div class='tabContent' id='thisclasses'>";
					$optTabContent .= funSelectClass("this", $SuperClasses);				
					$optTabContent .= "</div>";
					
					$optTabs .= "<li><a href='#myclasses'>Classes in My Groups</a></li>";
					$optTabContent .= "<div class='tabContent' id='myclasses'>";
					$optTabContent .= funSelectClass("my", $SuperClasses);
					$optTabContent .= "</div>";
					
					$optTabs .= "<li><a href='#publishedclasses'>Classes in Published Dictionaries</a></li>";
					$optTabContent .= "<div class='tabContent' id='publishedclasses'>";						
					$optTabContent .= funSelectClass("published", $SuperClasses);
					$optTabContent .= "</div>";
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
				}
				
				else
				
				{

					$PanelB .= "<div class='sdbluebox'>";
					
					$PanelB .= '<form method="post" action="doShapeClass.php">';
			
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					$PanelB .= "<input type='hidden' name='shapeid' value='$ShapeId'/>";
					if (!is_null($ShapeClassId)){
						$PanelB .= "<input type='hidden' name='shapeclassid' value='$ShapeClassId'/>";
					}
					
					$PanelB .= "<input type='hidden' name='classdictid' value='".$objClass->DictId."'/>";
					$PanelB .= "<input type='hidden' name='classid' value='".$objClass->Id."'/>";

					$PanelB .= "<table>";
					$PanelB .= "<tr><th>Class</th><td>".$objClass->Label."</td></tr>";	
					
					$PanelB .= "<tr><th>Create?</th><td>";					
					$PanelB .= "<input type='checkbox' name='create' value='selected'";
					if ($Create === true){
						$PanelB .= " checked='checked' ";
					}
					$PanelB .= ">";					
					$PanelB .= "</td></tr>";

					$PanelB .= "<tr><th>Select?</th><td>";					
					$PanelB .= "<input type='checkbox' name='select' value='selected'";
					if ($Select === true){
						$PanelB .= " checked='checked' ";
					}
					$PanelB .= ">";					
					$PanelB .= "</td></tr>";
					
					$PanelB .= "<tr><th>Match?</th><td>";					
					$PanelB .= "<input type='checkbox' name='match' value='selected'";
					if ($Match === true){
						$PanelB .= " checked='checked' ";
					}
					$PanelB .= ">";					
					$PanelB .= "</td></tr>";
					
					
					$PanelB .= "</table>";
					
					$ClassProperties = $Dicts->ClassProperties($objClass->DictId, $objClass->Id);
					if (count($ClassProperties) > 0){
						$PanelB .= "<div class='tab'>";					
						$PanelB .= frmShapeProperties($objShapeClass,$ClassProperties);
						$PanelB .= "</div>";
					}
					
					
					$PanelB .= "<input type='submit' value='Update Shape Class'/>";
					
					$PanelB .= '</form>';
					$PanelB .= "</div>";
					
				}
				
				break;

			case 'delete':
				
				$PanelB .= pnlShapeClass($objShapeClass);	
				
				$PanelB .= "<a href='doShapeClass.php?shapeid=$ShapeId&shapeclassid=$ShapeClassId&mode=delete'>confirm delete?</a><br/>";
				
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
		

	
	
function frmShapeClass($Class, $Fields = null){

	global $System;
	global $Dicts;
	
	$Content = '';
		
	$Content .= "<input type='hidden' name='".$FieldNamePrefix."classdictid' value='".$objShapeClass->Class->DictId."'/>";
	$Content .= "<input type='hidden' name='".$FieldNamePrefix."classid' value='".$objShapeClass->Class->Id."'/>";
	
	$Content .= "<table>";
	$Content .= "<tr><th>Class</th><td>".$objShapeClass->Class->Label."</td></tr>";	
	
	$Content .= "</table>";
	
	$Content .= "<div class='tab'>";
	$Content .= "<table>";
	$Content .= "<tr><th>Properties</th><td>";	
	
	$Content .= "<table>";
	
	$Content .= "<tr><th/><th>Selected?</th><th>Filter</th></tr>";
	
	
	
	$PropNum = 0;
	foreach ($Dicts->ClassProperties($objShapeClass->Class->DictId, $objShapeClass->Class->Id) as $objClassProperty){

		$PropNum = $PropNum + 1;
		$objProp = $Dicts->Dictionaries[$objClassProperty->PropDictId]->Properties[$objClassProperty->PropId];
		$Content .= "<tr>";
		$Content .= "<td>".$objProp->Label."</td>";
		$Content .= "<td>";
		$FieldName = $FieldNamePrefix."prop_".$PropNum."_sel";
		$Content .= "<input type='checkbox' name='$FieldName' value='selected' ";

		$FieldSelected = false;		
		
		if (isset($Fields[$FieldName])){
			if ($Fields[$FieldName] == 'selected'){
				$FieldSelected = true;
			}
		}
			
		if ($FieldSelected){
			$Content .= " checked='checked' ";
		}
			
		
		$Content .= "/>";
		$Content .= "</td>";
		$Content .= "<td>";


		
		$Content .= "</td>";
		$Content .= "</tr>";		
	}
	

	$Content .= "</table>";

	$Content .= "</td></tr></table>";

	$Content .= "</div>";

		
	return $Content;
}



function funSelectClass($Selection='this', $SuperClasses = null){

	global $System;
	global $Mode;
	global $objShape;
	
	global $Dicts;
	
	global $ReturnURL;

	$optClassList = array();
	if (is_array($SuperClasses)){
		foreach ($SuperClasses as $objSuperClass){
			$optClassList = array_merge($optClassList,$Dicts->SubClasses($objSuperClass->DictId, $objSuperClass->Id));
			$optClassList[] = $objSuperClass;
		}	
	}

	$Content = "";

	$TabId = "classes";
	
	$Content .= "<div class='tabContent' id='$TabId'>";
	
	$Content .= "<div class='sdbluebox'>";

	$DictFieldName = "classdictid";
	$ClassFieldName = "classid";
	
	
	$opts = array();

		
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if (is_null($optDict->EcoSystem)){
					if ($optDict->GroupId == $objShape->GroupId){					
						foreach ($optDict->Classes as $optClass){
							$Select = true;
							if (!is_null($SuperClasses)){
								$Select = false;
								foreach ($optClassList as $ClassList){
									if ($ClassList->DictId == $optClass->DictId){
										if ($ClassList->Id == $optClass->Id){
											$Select = true;
											continue;
										}
									}
								}
							}
							if ($Select === true){
								$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
							}
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
						
						$Select = true;
						if (!is_null($SuperClasses)){
							$Select = false;
							foreach ($optClassList as $ClassList){
								if ($ClassList->DictId == $optClass->DictId){
									if ($ClassList->Id == $optClass->Id){
										$Select = true;
										continue;
									}
								}
							}
						}
						
						if ($Select === true){
							$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
						}
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
					
					$Select = true;
					if (!is_null($SuperClasses)){
						$Select = false;
						foreach ($optClassList as $ClassList){
							if ($ClassList->DictId == $optClass->DictId){
								if ($ClassList->Id == $optClass->Id){
									$Select = true;
									continue;
								}
							}
						}
					}
					
					if ($Select === true){
						$opts[$optDict->Id][$optClass->Concept][$optClass->Id] = $optClass;
					}
				}
			}

			break;
			
	}	
		
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Dictionary</th><th>Concept</th><th>Class</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optDictId=>$optConcepts){
					
			$numRows = 1;
			foreach ($optConcepts as $optClasses){
				$numRows = $numRows + 1;
				foreach ($optClasses as $optClass){
					$numRows = $numRows + 1;
				}
			}
			
			$Content .= "<tr>";			
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
					$UrlParams[$DictFieldName] = $optClass->DictId;
					$UrlParams[$ClassFieldName] = $optClass->Id;
					$ReturnUrl = UpdateUrl($UrlParams);
										
					$Content .= "<tr><td><a href='$ReturnUrl'>".$optClass->Label."<a></td>";
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