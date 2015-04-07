<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlClass.php");
	require_once("panel/pnlClassViz.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'classviz');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

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
						
		$DictId = null;
		$GroupId = null;
		$ClassId = null;

		$objClass = null;
		$objViz = null;
		
		$VizTypeId = null;
		$Params = array();
				
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

		if (!($objClass = $Dicts->getClass($DictId, $ClassId))){
			throw new exception("Unknown Class");
		}

		if (!is_null($objClass->Viz)){
			$objViz = $objClass->Viz;
			$VizTypeId = $objViz->TypeId;
		}
		
		if (isset($_REQUEST['viztypeid'])){
			if (!IsEmptyString($_REQUEST['viztypeid'])){
				$VizTypeId = $_REQUEST['viztypeid'];
			}
		}

		if (!isset($System->Config->Visualizers[$VizTypeId])){
			throw new exception("Unknown Visualisation Type");				
		}
		
		
		$objVizType = $System->Config->Visualizers[$VizTypeId];
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
				
		$objClassProperties = $Dicts->ClassProperties($objClass->DictId, $objClass->Id);
		
		$Page->Title = $Mode." class visualizer";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objDict->canView){
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
				
				$PanelB .= pnlClassViz ($DictId, $ClassId);
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='classviz.php?dictid=$DictId&classid=$ClassId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='classviz.php?dictid=$DictId&classid=$ClassId&mode=delete'>&bull; delete</a></li> ";
				}
				$PanelB .= "</ul></div>";
				
				break;
				
			case 'edit':
				
				$Panel = "<div class='sdbluebox'>";
				
				
				$PanelB .= '<form method="post" action="doClassViz.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				$PanelB .= "<input type='hidden' name='classid' value='$ClassId'/>";
				$PanelB .= "<input type='hidden' name='viztypeid' value='$VizTypeId'/>";
							
				
				$PanelB .= '<table>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Vizualizer';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= $objVizType->Name;
					$PanelB .= '</td>';
				$PanelB .= '</tr>';					
				
				$PanelB .= "<tr><td colspan='2'><h3>Parameters</h3></td></tr>";
				
				
				$ParamNum = 0;
				foreach ($objVizType->Params as $objVizTypeParam){
					$ParamNum = $ParamNum + 1;
					
					$PropDictId = null;
					$PropId = null;
					if (isset($objViz)){
						if (isset($objViz->Params[$ParamNum])){
							$PropDictId = $objViz->Params[$ParamNum]->PropDictId;
							$PropId = $objViz->Params[$ParamNum]->PropId;
						}
					}
					
				
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= $objVizTypeParam->Name;
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						
						$FieldName = "param$ParamNum";
						$PanelB .= "<select name='$FieldName'>";
						$PanelB .= "<option/>";
						$PropNum = 0;
						foreach ($objClassProperties as $optClassProp){
							if ($optProp = $Dicts->getProperty($optClassProp->PropDictId,$optClassProp->PropId )){
								$PanelB .= "<option value='$PropNum'";
								
								if ($optProp->DictId == $PropDictId){
									if ($optProp->Id == $PropId){
										$PanelB .= " selected='true' ";
									}
								}								
								
								$PanelB .= ">".$optProp->Label."</option>";
							}
							$PropNum = $PropNum + 1;
						}
						$PanelB .= "</select>";
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
				}

				
		
			 	$PanelB .= '</table>';
			 	$PanelB .= '<input type="submit" value="Set Visualizer for Class">';
			 	
				$PanelB .= '</form>';
				$PanelB .= "</div>";

				break;
				
			case 'delete':
				$PanelB .= pnlClassViz($DictId, $ClassId);
				$PanelB .= "<a href='doClassViz.php?dictid=$DictId&classid=$ClassId&mode=delete'>confirm delete?</a><br/>";
				
				break;
				
		}
		
		if (!empty($Tabs)){
			$PanelB .= "<ul id='tabs'>".$Tabs."</ul>".$TabContent;
		}

		$PanelB .= "<h3>Class</h3>";
		$PanelB .= pnlClass($DictId, $ClassId);
		
	 	$Page->ContentPanelB = $PanelB;
	 	$Page->ContentPanelC = $PanelC;
	 	
	}
	catch(Exception $e)  {
		$Page->ErrorMessage = $e->getMessage();
	}
	 	
	$Page -> Display();

	
?>