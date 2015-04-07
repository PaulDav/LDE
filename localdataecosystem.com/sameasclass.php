<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlClass.php");

	require_once("class/clsModel.php");	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'sameasclass');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	try {

		$Model = new clsModel();
		$Dicts = new clsDicts();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";

		$DictId = null;
		$GroupId = null;
		$ClassId = null;
		$SameAsClassId = null;
		$SameAsDictId = null;
		
		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if (is_null($DictId)) {
			throw new exception("DictId not specified");
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		$GroupId = $objDict->GroupId;

		
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
		}
		if (is_null($ClassId)) {
			throw new exception("ClassId not specified");
		}

		$objClass = $objDict->Classes[$ClassId];
				
		if (isset($_REQUEST['sameasclassid'])){
			$SameAsClassId = $_REQUEST['sameasclassid'];
			$SameAsDictId = $DictId;
		}
		if (isset($_REQUEST['sameasdictid'])){
			$SameAsDictId = $_REQUEST['sameasdictid'];
		}

		
		switch ($Mode){
			case 'edit':
			case 'delete':
				if (is_null($SameAsClassId)) {
					throw new exception("Same As Class not specified");
				}				
				break;
		}

		if ($System->Session->Error){			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}

		if (!is_null($SameAsClassId)){
			$objSameAsClass = $Dicts->getClass($SameAsDictId, $SameAsClassId);
			if (!is_object($objSameAsClass)){
				throw new exception("Unknown Same As Class");
			}
		}
		
		
		$Page->Title = $Mode." Same As Class";		
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
				
				$PanelB .= "<table>";
				$PanelB .= "<tr><th/><td>";				
				$PanelB .= pnlClass( $DictId, $ClassId );
				$PanelB .= "</td></tr>";
				
				$PanelB .= "<tr><th colspan = '2'>can have subjects from</th>";

				$PanelB .= "<tr><th/><td>";				
				$PanelB .= pnlClass( $SameAsDictId, $SameAsClassId );
				$PanelB .= "</td></tr>";
				
				$PanelB .= "</table>";

				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='sameasclass.php?dictid=$DictId&classid=$ClassId&sameasdictid=$SameAsDictId&sameasclassid=$SameAsClassId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";				
							    
				break;
			case 'new':
			case 'edit':
				
// select the class
				if (is_null($SameAsClassId)){
				
					$PanelB .= "<h3>Choose a Class from one of these selections</h3>";
					
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#thissameasclasses'>Classes in this Group</a></li>";					
					$optTabContent .= funSelectSameAsClass("this");
					
					$optTabs .= "<li><a href='#mysameasclasses'>Classes in My Groups</a></li>";			    
					$optTabContent .= funSelectSameAsClass("my");
					
					$optTabs .= "<li><a href='#publishedsameasclasses'>Classes in Published Dictionaries</a></li>";			    
					$optTabContent .= funSelectSameAsClass("published");
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
				}
				
				else
				{
				
					$PanelB .= '<form method="post" action="doSameAsClass.php">';
	
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
					$PanelB .= "<input type='hidden' name='classid' value='$ClassId'/>";
					$PanelB .= "<input type='hidden' name='sameasdictid' value='$SameAsDictId'/>";
					$PanelB .= "<input type='hidden' name='sameasclassid' value='$SameAsClassId'/>";
					
					$PanelB .= "<table>";
					$PanelB .= "<tr><th/><td>";				
					$PanelB .= pnlClass( $DictId, $ClassId );
					$PanelB .= "</td></tr>";
					
					$PanelB .= "<tr><th colspan = '2'>can have subjects from</th>";
	
					$PanelB .= "<tr><th/><td>";				
					$PanelB .= pnlClass( $SameAsDictId, $SameAsClassId );
					$PanelB .= "</td></tr>";
					
										
					$PanelB .= '<tr>';
						$PanelB .= '<td/>';
						$PanelB .= '<td>';
						
						switch ( $Mode ){
							case "new":
								$PanelB .= '<input type="submit" value="Add SameAs Class">';
								break;
						}
	
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
			
				 	$PanelB .= '</table>';
					$PanelB .= '</form>';
					
				}
				break;
				
				
			case 'delete':

				$PanelB .= "<table>";
				$PanelB .= "<tr><th/><td>";				
				$PanelB .= pnlClass( $DictId, $ClassId );
				$PanelB .= "</td></tr>";
				
				$PanelB .= "<tr><th colspan = '2'>can have subjects from</th>";

				$PanelB .= "<tr><th/><td>";				
				$PanelB .= pnlClass( $SameAsDictId, $SameAsClassId );
				$PanelB .= "</td></tr>";
				
				$PanelB .= "</table>";
				
				$PanelB .= "<li><a href='doSameAsClass.php?dictid=$DictId&classid=$ClassId&sameasdictid=$SameAsDictId&sameasclassid=$SameAsClassId&mode=delete'>confirm delete?</a></li> ";
				
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


function funSelectSameAsClass($Selection){

	global $System;
	global $Mode;
	global $objClass;
	global $objDict;
	
	global $Dicts;
	global $Model;

	$Content = "";

	$Content .= "<div class='tabContent' id='".$Selection."sameasclasses'>";
	
	$Content .= "<div class='sdbluebox'>";

	$opts = array();

	$objConcept = null;
	foreach ($Model->Concepts as $optConcept){		
		if ($optConcept->Name == $objClass->Concept){
			$objConcept = $optConcept;
			break;
		}
	}
	if (!is_null($objConcept)){
		$Concepts = array();
		$Concepts[$objConcept->Name] = $objConcept;
		foreach ($objConcept->SubConceptIds as $SubConceptId){
			if (isset($Model->Concepts[$SubConceptId])){
				$objSubConcept = $Model->Concepts[$SubConceptId];
				$Concepts[$objSubConcept->Name] = $objSubConcept;				
			}
		}
	}
	
	
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
						
							if (!isset($Concepts[$optClass->Concept])){
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
					
						if (!isset($Concepts[$optClass->Concept])){
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
				
					if (!isset($Concepts[$optClass->Concept])){
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
				
				$UrlParams = array();
				$UrlParams['sameasdictid'] = $optClass->DictId;
				$UrlParams['sameasclassid'] = $optClass->Id;
				
				$ReturnUrl = UpdateUrl($UrlParams);
				
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