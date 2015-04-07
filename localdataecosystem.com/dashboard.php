<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");
		
	require_once("class/clsData.php");
	require_once("class/clsDict.php");
	
	require_once("class/clsShape.php");
		
	require_once("panel/pnlSelectionData.php");

	require_once("panel/pnlClassFilters.php");
	require_once("panel/pnlViewSelectionViz.php");
	
	define('PAGE_NAME', 'dashboard');

	session_start();
		
	$System = new clsSystem();
			
	$Page = new clsPage();
	
	$Script = '';
	
	$Script .= "<script src='jquery/jquery-1.11.1.min.js'></script>";
  	$Script .= "<script src='//code.jquery.com/ui/1.10.4/jquery-ui.js'></script>";	
	$Script .= "<link rel='stylesheet' href='//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css'>";
	$Script .= "<script type='text/javascript' src='java/datepicker.js'></script>";

	$Script .= "<script type='text/javascript' src='java/utils.js'></script>";
	
	$Script .= "<script type='text/javascript' src='java/ajax.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClasses.js'></script>";
	$Script .= "<script type='text/javascript' src='java/getClassSubjects.js'></script>";

	$Page->Script .= $Script;
	

//	if ($js = file_get_contents('java/jquery.js')){
//		$Page->Script .= $js;
//	}
	
	SaveUserInput(PAGE_NAME);
	$Fields = GetUserInput(PAGE_NAME);
	
	$ShapeId = null;
	$SubjectId = null;
			
	try {

		$Dicts = new clsDicts();
		$Shapes = new clsShapes();
		
		if (!isset($_REQUEST['shapeid'])){
			throw new exception("ShapeId not specified");
		}
		$ShapeId = $_REQUEST['shapeid'];

		if (!isset($Shapes->Items[$ShapeId])){			
			throw new exception("Unknown Shape");			
		}		
		$objShape = $Shapes->Items[$ShapeId];
		
		if (isset($_REQUEST['subjectid'])){
			$SubjectId = $_REQUEST['subjectid'];
		}
		if (!is_null($SubjectId)){
			$objSubject = new clsSubject($SubjectId);
		}
		
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
		
		
//		$Filters = makeFilters($objView);
		
		
		$Page->Title = "dashboard";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		$PanelB .= "<h2>".$objShape->Name."</h2>";
		
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
				
				if (is_null($SubjectId)){
					
					$arrClasses = array();
					foreach ($objShape->ShapeClasses as $optNum=>$optShapeClass){

// consider if the Class should be shown for the Shape

// combine Classes in to super classes
						$objClass = $optShapeClass->Class;
						$arrClasses[$objClass->DictId][$objClass->Id][$optNum] = $optShapeClass;
						foreach($arrSuperClasses = $Dicts->SuperClasses($objClass->DictId, $objClass->Id) as $objSuperClass){
							$arrClasses[$objSuperClass->DictId][$objSuperClass->Id][$optNum] = $optShapeClass;
						}
					}

// remove duplicate super classes
					foreach ($arrClasses as $DictId=>$arrClass){
						foreach ($arrClass as $ClassId=>$arrShapeClasses){
							foreach($Dicts->SuperClasses($DictId, $ClassId) as $objSuperClass){
								if (isset($arrClasses[$objSuperClass->DictId][$objSuperClass->Id])){
									if ($arrClasses[$objSuperClass->DictId][$objSuperClass->Id] == $arrShapeClasses){
										unset ($arrClasses[$objSuperClass->DictId][$objSuperClass->Id]);
									}
								}
							}
							
						}
					}
					
// remove sub classes 					

					foreach ($arrClasses as $DictId=>$arrClass){
						foreach ($arrClass as $ClassId=>$arrShapeClasses){
							foreach($Dicts->SubClasses($DictId, $ClassId) as $objSubClass){
								if (isset($arrClasses[$objSubClass->DictId][$objSubClass->Id])){
									unset ($arrClasses[$objSubClass->DictId][$objSubClass->Id]);
								}
							}
							
						}
					}

					$SummaryClasses = array();
					foreach ($arrClasses as $DictId=>$arrClass){
						foreach ($arrClass as $ClassId=>$arrShapeClasses){
							$objClass = $Dicts->getClass($DictId, $ClassId);
							$SummaryClasses[] = $objClass;
						}
					}

					
					$Script = '<script>';	
					$Script .= "function init(){ \n";
					$Script .= "    setDatePicker();";

					foreach ($SummaryClasses as $optNum=>$objClass){
						
						$TabContent .= "\n";
						
						$DictId = $objClass->DictId;
						$ClassId = $objClass->Id;

						$Tabs .= "<li><a href='#class$optNum'>".$objClass->Heading."<span id='countclasssubjects$optNum'></span></a></li>";
						$TabContent .= "<div class='tabContent hide' id='class$optNum'>";

						$TabContent .= "<h2>".$objClass->Heading."</h2>";

						$DivId = "classsubjects$optNum";
						$AjaxCall = "getClassSubjects('$DictId', '$ClassId', { ShapeId :'$ShapeId' }, 'dashboard.php', '$DivId');";
						$Script .= $AjaxCall;

						$AjaxCall = str_replace("'","&apos;",$AjaxCall);
						
						$TabContent .= "<h3>Filters</h3>";
						$TabContent .= "\n";
						
						$TabContent .= pnlClassFilters( $DictId, $ClassId, $DivId."_filter");
						$TabContent .= "\n";
						$TabContent .= "<input type='submit' value='Apply'  onClick='$AjaxCall'/>";
						
						$TabContent .= "\n";						
						$TabContent .= "<input type='submit' value='Clear'  onClick='clearFilters(&quot;$DivId&quot;); $AjaxCall'/>";					
						$TabContent .= "\n";
						
						
						$TabContent .= "<div class='sdgreybox' id='classsubjects$optNum'>";
						$TabContent .= "</div>";

						$TabContent .= "</div>";						
					}

					$Script .= "} \n";
					$Script .= "</script>\n";
					$Page->Script .= $Script;
					
				}
				else
				{
					$objColl = new clsCollection();
					$objColl->Shape = $objShape;
					$objColl->SubjectId = $SubjectId;

					$PanelB .= "<h3>".$objSubject->Identifier.' '.$objSubject->Name."</h3>";					

					if (!is_null($System->Config->DotRenderer)){			    
					    $Tabs .= "<li><a href='#visualize'>Visualize</a></li>";
						$TabContent .= "<div class='tabContent hide' id='visualize'>";
		
						$vizstyles = array();
						$vizstyles[0] = "None";
						$vizstyles[3] = "Entities";
						$vizstyles[1] = "Entities in Concept Packages";
						$vizstyles[2] = "Entities and Attributes";
							
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
							$Dot = $objColl->getDot($VizStyle);
							switch ($VizFormat){
								case 1:
							
									switch ($System->Config->DotRenderer){
										case "viz.js":
											$Content .= "<div id='viz'/>";
											
											$Page->Script .= "<script src='viz.js'></script>";
											$Content .= "<script type='text/vnd.graphviz' id='graph1'>$Dot</script>";
											$Content .= "<script>document.getElementById('viz').innerHTML += Viz(";
											$Content .= "document.getElementById('graph1').innerHTML";
											$Content .= ",'svg');</script>";	
											$Content .= '</div>';
		
											
											break;
									}
									break;
								case 9:
									$Content .= '<pre>'.htmlentities($Dot).'</pre>';
									break;
							}
						}
	
						$TabContent .= $Content;													
						$TabContent .= "</div>";
				    }
					$Tabs .= "<li><a href='#statements'>Statements";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='statements'>";
					
					$TabContent .= "<h3>Statements</h3>";	

					if (count($objColl->Statements) > 0){						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<td>Doc</td><th>Id</th><th>Type</th><th>About</th><th>Subject</th><th>Link</th><th>Value</th><th>Eff From</th><th>Eff To</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
						
						foreach ( $objColl->Statements as $objStat){
							
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
				    			    
				    $Tabs .= "<li><a href='#export'>Export</a></li>";
				    $TabContent .= "<div class='tabContent hide' id='export'>";
				    
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
	 	
	$Page -> Display();

	
?>