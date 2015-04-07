<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");

	require_once("class/clsModel.php");
	require_once("class/clsData.php");
	require_once("class/clsDict.php");
	require_once("class/clsShape.php");
	
	require_once("panel/pnlClassFilters.php");
	
	
	define('PAGE_NAME', 'browse');

	session_start();
		
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

	$Script = '';
	
	$Script .= "<script type='text/javascript' src='java/datepicker.js'></script>";

	$Script .= "<script type='text/javascript' src='java/utils.js'></script>";
	
	$Script .= "<script type='text/javascript' src='java/ajax.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClasses.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClassSubjects.js'></script>";

	
	$Script .= "<script src='jquery/jquery-1.11.1.min.js'></script>";
  	$Script .= "<script src='//code.jquery.com/ui/1.10.4/jquery-ui.js'></script>";	
	$Script .= "<link rel='stylesheet' href='//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css'>";
	
	$Page->Script .= $Script;
	
	
	$InitScript = '<script>';	
	$InitScript .= "function init(){ \n";
	
	
	$ConceptId = null;		
	$DictId = null;
	$ClassId = null;
	
	
	$objConcept = null;
	$objClass = null;
				
	try {

		$Dicts = new clsDicts();
		$objConceptModel = new clsModel();

		$Shapes = new clsShapes();
		$Orgs = new clsOrganisations();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";


		if (isset($_REQUEST['conceptid'])){
			$ConceptId = $_REQUEST['conceptid'];
		}		
				
		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}		
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
		}
		
		if (!empty($DictId)){
			if (!isset($Dicts->Dictionaries[$DictId])){
				throw new exception("Unknown Dictionary");
			}
			$Dict = $Dicts->Dictionaries[$DictId];
			if (!empty($ClassId)){
				if (!isset($Dict->Classes[$ClassId])){
					throw new exception("Unknown Class");
				}
				$objClass = $Dict->Classes[$ClassId];
			}
		}
		
		if (is_null($objClass)){
			if (!is_null($ConceptId)){
				if (!isset($objConceptModel->Concepts[$ConceptId])){
					throw new exception('Unknown Concept');
				}
				$objConcept = $objConceptModel->Concepts[$ConceptId];
			}
		}
		
		
		$Page->Title = "browse";		
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

				if (!is_null($objClass)){
					
					$SuperClasses = $Dicts->SuperClasses($objClass->DictId, $objClass->Id);
					
					if (count($SuperClasses) > 0){
						
						$PanelB .= "<h4>Super Classes</h4>";
						$PanelB .= "<table><thead><tr><th>Dictionary</th><th>Class</th></tr></thead><tbody>";

						foreach ($SuperClasses as $optSuperClass){
							$optDict = $Dicts->Dictionaries[$optSuperClass->DictId];
							$UrlParams['dictid'] = $optSuperClass->DictId;	
							$UrlParams['classid'] = $optSuperClass->Id;	
							$ReturnUrl = UpdateUrl($UrlParams);
							$PanelB .= "<tr><td>".$optDict->Name."</td><td><a href='$ReturnUrl'>".$optSuperClass->Label."</a></td></tr>";
						}
						$PanelB .= "</table>";
					}
					
					
					
					$SubClasses = $Dicts->SubClasses($objClass->DictId, $objClass->Id);
					
					if (count($SubClasses) > 0){
						
						$PanelB .= "<h4>Sub Classes</h4>";
						$PanelB .= "<table><thead><tr><th>Dictionary</th><th>Class</th></tr></thead><tbody>";

						foreach ($SubClasses as $optSubClass){
							$optDict = $Dicts->Dictionaries[$optSubClass->DictId];
							$UrlParams['dictid'] = $optSubClass->DictId;	
							$UrlParams['classid'] = $optSubClass->Id;	
							$ReturnUrl = UpdateUrl($UrlParams);
							$PanelB .= "<tr><td>".$optDict->Name."</td><td><a href='$ReturnUrl'>".$optSubClass->Label."</a></td></tr>";							
						}
						$PanelB .= "</table>";
					}
					
					$PanelB .= "<h2>".$objClass->Heading."</h2>";					
					$InitScript .= "getClassSubjects('$DictId', '$ClassId');";

					$Tabs .= "<li><a href='#results'>Results</a></li>";
					$TabContent .= "<div class='tabContent hide' id='results'>";
					$TabContent .= "<h3>Results</h3>";			
					$TabContent .= "<div class='sdgreybox' id='classsubjects'>";
					$TabContent .= "</div>";
					$TabContent .= "</div>";	
					
					
					$Tabs .= "<li><a href='#filters'>Filters</a></li>";
					$TabContent .= "<div class='tabContent hide' id='filters'>";
					$TabContent .= "<h3>Filters</h3>";					
					$TabContent .= pnlClassFilters($objClass->DictId, $objClass->Id);
					$TabContent .= "<input type='submit' value='Apply'  onClick='getClassSubjects(&quot;$DictId&quot;, &quot;$ClassId&quot;);'/>";					
					$TabContent .= "<input type='submit' value='Clear'  onClick='clearFilters(); getClassSubjects(&quot;$DictId&quot;, &quot;$ClassId&quot;);'/>";					
					$TabContent .= "</div>";	
					

					
				}				
				else
				{
// find concept/classes in Shapes for a Reference Set
					
					$InitScript .= "    getDataClasses();";
					
					$Tabs .= "<li><a href='#open'>Open</a></li>";
					$TabContent .= "<div class='tabContent hide' id='open'>";
					
					$objSets = new clsSets();
					$objSets->LicenceTypeId = 1;
					$objSets->ContextId = 1;
					
					$ConceptClasses = array();
					
					foreach ($objSets->Items as $objSet){
						foreach ($objSet->SetShapes as $objSetShape){
							if (isset($Shapes->Items[$objSetShape->ShapeId])){
								$objShape = $Shapes->Items[$objSetShape->ShapeId];
								foreach ($objShape->ShapeClasses as $objShapeClass){
									if ($objShapeClass->Create === true){
										$ConceptClasses[$objShapeClass->Class->Concept][$objShapeClass->Class->DictId][$objShapeClass->Class->Id] = $objShapeClass->Class;
									}
								}
							}
						}
					}
					
					
					$TabContent .= "<table><thead><tr><th>Concept</th><th>Dictionary</th><th>Class</th></tr></thead><tbody>";
					
					foreach ($ConceptClasses as $Concept=>$DictClasses){

						$optConcept = $objConceptModel->getConceptByName($Concept);
						
						if (!is_object($optConcept)){
							continue;
						}
						
						$ConceptClasses = $Dicts->ConceptClasses($optConcept->Name);

						if (count($DictClasses) > 0){

							$RowSpan = 1;
							foreach ($DictClasses as $optDictId=>$arrClasses){
								$RowSpan = $RowSpan + 1;
								foreach ($arrClasses as $optClassId=>$optClass){
									$RowSpan = $RowSpan + 1;
								}
							}
														
							$TabContent .= "<tr><td rowspan = '$RowSpan'>".strtoupper($optConcept->Name)."</td></tr>";
						
							foreach ($DictClasses as $optDictId=>$arrClasses){
								$optDict = $Dicts->Dictionaries[$optDictId];

								$RowSpan = 1;
								foreach ($arrClasses as $optClassId=>$optClass){
									$RowSpan = $RowSpan + 1;
								}
								
								$TabContent .= "<tr><td rowspan='$RowSpan'>".$optDict->Name."</td></tr>";
								
								foreach ($arrClasses as $optClassId=>$optClass){
									$UrlParams = array();
									$UrlParams['dictid'] = $optDictId;	
									$UrlParams['classid'] = $optClassId;	
									$ReturnUrl = UpdateUrl($UrlParams);
									$TabContent .= "<tr><td><a href='$ReturnUrl'>".$optClass->Label."</a></td></tr>";
								}
							}
							$TabContent .= "<tr><td colspan='3'><hr/></td></tr>";
						}
					}				
						
					$TabContent .= "</tbody></table>";	

					$TabContent .= "</div>";	
					
					$Tabs .= "<li><a href='#class'>by Class</a></li>";
					$TabContent .= "<div class='tabContent hide' id='class'>";
		
					$TabContent .= "<div><h3>by class of data</h3></div>";
		
					$TabContent .= "<table class='sdbluebox'>";
					
					$TabContent .= '<tr>';
						$TabContent .= "<th>Context</th>";
						$TabContent .= "<td>";				
						$TabContent .= "<select onchange='getDataClasses()' id='filtercontext'/>";
						
						$TabContent .= '<option/>';
						foreach ($System->Config->SetContextTypes as $optContextId=>$optContext){
							$TabContent .= "<option value='$optContextId'>".$optContext->Name."</option>";
						}
						$TabContent .= "</select>";
						$TabContent .= "</td>";
					$TabContent .= '</tr>';
		
					$TabContent .= '<tr>';
						$TabContent .= "<th>Licence Type</th>";
						$TabContent .= "<td>";				
						$TabContent .= "<select onchange='getDataClasses()' id='filterlicencetype'/>";
						
						$TabContent .= '<option/>';
						foreach ($System->Config->SetLicenceTypeTypes as $optLicenceTypeId=>$optLiceneType){
							$TabContent .= "<option value='$optLicenceTypeId'>".$optLiceneType."</option>";
						}
						$TabContent .= "</select>";
						$TabContent .= "</td>";
					$TabContent .= '</tr>';
					
					
					$TabContent .= '<tr>';
						$TabContent .= "<th>Organisation</th>";
						$TabContent .= "<td>";				
						$TabContent .= "<select onchange='getDataClasses()' id='filterorg'/>";
						
						$TabContent .= '<option/>';
						foreach ($Orgs->Items as $optOrg){
							$TabContent .= "<option value='$optOrg->Id'>".$optOrg->Name."</option>";
						}
						$TabContent .= "</select>";
						$TabContent .= "</td>";
					$TabContent .= '</tr>';
		
					$TabContent .= '<tr>';
						$TabContent .= "<th>Shape</th>";
						$TabContent .= "<td>";				
						$TabContent .= "<select onchange='getDataClasses()' id='filtershape'/>";
						
						$TabContent .= '<option/>';
						foreach ($Shapes->Items as $optShape){
							$TabContent .= "<option value='$optShape->Id'>".$optShape->Name."</option>";
						}
						$TabContent .= "</select>";
						$TabContent .= "</td>";				
					$TabContent .= '</tr>';
					
					
					$TabContent .= "</table>";
					
				 	$TabContent .= "<div id='dataclasses'>";
				 	$TabContent .= "</div>";
				 	
					
				$TabContent .= "</div>";
					
					
				}
				
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
	 	
	$InitScript .= "    setDatePicker();";
	
	$InitScript .= "} \n";
	$InitScript .= "</script>\n";
	$Page->Script .= $InitScript;
	
	$Page -> Display();
		
?>