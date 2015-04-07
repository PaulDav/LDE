<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
		
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsShape.php");	
	
	require_once("class/clsModel.php");
	
	define('PAGE_NAME', 'designs');

	session_start();
		
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();

	$Dicts = new clsDicts();
	$Shapes = new clsShapes();
	$Model = new clsModel();

	try {

		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		$LdeUri = null;
		$DictId = null;
		$GroupId = null;

		if (isset($_REQUEST['ldeuri'])){
			$LdeUri = $_REQUEST['ldeuri'];
		}
		
		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		
		if (isset($_REQUEST['groupid'])){
			$GroupId = $_REQUEST['groupid'];
		}
		
		
		$Page->Title = "design";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		$Tabs .= "<li><a href='#classes'>Classes";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='classes'>";
		$TabContent .= "<h3>Classes</h3>";	

		$TabContent .= "<table class='list'>";
		$TabContent .= '<thead>';
		$TabContent .= '<tr>';
		$TabContent .= "<th>Ecosystem</th><th>Dictionary</th><th>Label</th><th>Concept</th><th>Description</th>";
		$TabContent .= '</tr>';
		$TabContent .= '</thead>';

		foreach ($Dicts->Dictionaries as $objDict){
			
			foreach ( $objDict->Classes as $objClass){
				
				$num = $num + 1;
				
				$TabContent .= "<tr>";

				$TabContent .= "<td>".$objDict->EcoSystem."</td>";
				$TabContent .= "<td>".$objDict->Name."</td>";
								
				$TabContent .= "<td><a href='class.php?dictid=".$objDict->Id."&classid=".$objClass->Id."'>".$objClass->Label."</a></td>";
				$TabContent .= "<td>".strtoupper($objClass->Concept)."</td>";
				
				$TabContent .= "<td>".nl2br(Truncate($objClass->Description))."</td>";
				
				$TabContent .= "</tr>";				
				
			}
		}

		$TabContent .= '</table>';
		
		$TabContent .= "</div>";
	    $Tabs .= "($num)</a></li>";

	    
	    
	    $Tabs .= "<li><a href='#properties'>Properties";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='properties'>";
		$TabContent .= "<h3>Properties</h3>";	

		$TabContent .= "<table class='list'>";
		$TabContent .= '<thead>';
		$TabContent .= '<tr>';
		$TabContent .= "<th>Ecosystem</th><th>Dictionary</th><th>Label</th><th>Description</th>";
		$TabContent .= '</tr>';
		$TabContent .= '</thead>';
		
		
		foreach ($Dicts->Dictionaries as $objDict){
								
			foreach ( $objDict->Properties as $objProp){
				
				$num = $num + 1;
				
				$TabContent .= "<tr>";
				
				$TabContent .= "<td>".$objDict->EcoSystem."</td>";
				$TabContent .= "<td>".$objDict->Name."</td>";
								
				$TabContent .= "<td><a href='property.php?dictid=".$objDict->Id."&propid=".$objProp->Id."'>".$objProp->Label."</a></td>";
				$TabContent .= "<td>".nl2br(Truncate($objProp->Description))."</td>";
				
				$TabContent .= "</tr>";				
				
			}
		}

	 	$TabContent .= '</table>';
		
		$TabContent .= "</div>";
	    $Tabs .= "($num)</a></li>";
			    

	    $Tabs .= "<li><a href='#relationships'>Relationships";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='relationships'>";
		$TabContent .= "<h3>Relationships</h3>";	

		$TabContent .= "<table class='list'>";
		$TabContent .= '<thead>';
		$TabContent .= '<tr>';
		$TabContent .= "<th>Ecosystem</th><th>Dictionary</th><th>Subject</th><th/><th>Object</th><th>Cardinality</th><th>Description</th>";
		$TabContent .= '</tr>';
		$TabContent .= '</thead>';
		
		foreach ($Dicts->Dictionaries as $objDict){
			foreach ( $objDict->Relationships as $objRel){
							
				$objSubjectDict = $objDict;
				if (!($objRel->SubjectDictId == $objDict->Id)){
					$objSubjectDict = $Dicts->Dictionaries[$objRel->SubjectDictId];								
				}
				$objSubject = $objSubjectDict->Classes[$objRel->SubjectId];
	
				$objObjectDict = $objDict;
				if (!($objRel->ObjectDictId == $objDict->Id)){
					$objObjectDict = $Dicts->Dictionaries[$objRel->ObjectDictId];								
				}
				$objObject = $objObjectDict->Classes[$objRel->ObjectId];
							
							
				$num = $num + 1;
				
				$TabContent .= "<tr>";
				
				$TabContent .= "<td>".$objDict->EcoSystem."</td>";
				$TabContent .= "<td>".$objDict->Name."</td>";
								
				$TabContent .= "<td>".$objSubject->Label."</td>";							
				$TabContent .= "<td><a href='relationship.php?dictid=".$objDict->Id."&relid=".$objRel->Id."'>".$objRel->Label."</a></td>";				
				$TabContent .= "<td>".$objObject->Label."</td>";
				$TabContent .= "<td>".$objRel->Cardinality."</td>";
				$TabContent .= "<td>".nl2br(Truncate($objRel->Description))."</td>";
				
				$TabContent .= "</tr>";				
				
			}
		}
		$TabContent .= '</table>';
	
		$TabContent .= "</div>";
	    $Tabs .= "($num)</a></li>";
			    

	    $Tabs .= "<li><a href='#lists'>Lists";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='lists'>";
		$TabContent .= "<h3>Lists</h3>";	

						
		$TabContent .= "<table class='list'>";
		$TabContent .= '<thead>';
		$TabContent .= '<tr>';
			$TabContent .= "<th>Ecosystem</th><th>Dictionary</th><th>Label</th><th>Description</th><th>Source</th>";
		$TabContent .= '</tr>';
		$TabContent .= '</thead>';

		$arrLists = array();
		foreach ($Dicts->Dictionaries as $objDict){
			foreach ( $objDict->Lists as $objList){
				$arrLists[$objList->Label][$objList->Id] = $objList;	
			}
		}
		ksort($arrLists);

		foreach ($arrLists as $arrList){
			foreach ($arrList as $objList){
				$num = $num + 1;
	
				$TabContent .= "<tr>";
				
				$objDict = $Dicts->Dictionaries[$objList->DictId];
								
				$TabContent .= "<td>".$objDict->EcoSystem."</td>";
				$TabContent .= "<td>".$objDict->Name."</td>";
													
				$TabContent .= "<td><a href='list.php?dictid=".$objDict->Id."&listid=".$objList->Id."'>".$objList->Label."</a></td>";
				$TabContent .= "<td>".nl2br(Truncate($objList->Description))."</td>";
				$TabContent .= "<td>".$objList->Source."</td>";
				
				$TabContent .= "</tr>";				
				
			}

		}
		$TabContent .= '</table>';
	
		$TabContent .= "</div>";
	    $Tabs .= "($num)</a></li>";
	    
	    
	    $Tabs .= "<li><a href='#shapes'>Shapes";
		$num = 0;
		$TabContent .= "<div class='tabContent hide' id='shapes'>";
		$TabContent .= "<h3>Shapes</h3>";	

		$TabContent .= "<table class='list'>";
		$TabContent .= '<thead>';
		$TabContent .= '<tr>';
		$TabContent .= "<th>Ecosystem</th><th>Shape</th><th>Description</th>";
		$TabContent .= '</tr>';
		$TabContent .= '</thead>';

		foreach ($Shapes->Items as $objShape){
							
			$num = $num + 1;
			
			$TabContent .= "<tr>";

			$TabContent .= "<td>".$objShape->EcoSystem."</td>";
			$TabContent .= "<td>".$objShape->Name."</td>";
							
			$TabContent .= "<td><a href='shape.php?shapeid=".$objShape->Id;
			if (!is_null($objShape->EcoSystem)){
				$TabContent .= "&ecosystem=".$objShape->EcoSystem;
			}
			$TabContent .= "'>".$objShape->Name."</a></td>";				
			$TabContent .= "<td>".nl2br(Truncate($objShape->Description))."</td>";
			
		}

		$TabContent .= '</table>';
		
		$TabContent .= "</div>";
	    $Tabs .= "($num)</a></li>";
			    
		
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