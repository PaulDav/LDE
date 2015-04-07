<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlProperty.php");
	require_once("panel/pnlClass.php");
	
	require_once("panel/pnlClassProperty.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'classproperty');

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

		$DictId = '';
		$GroupId = '';
		$ClassPropId = '';
		$ClassId = '';
		$PropId = '';
		$PropDictId = '';
		
		$Cardinality = '';
		$Sequence = null;
		$UseAsName = false;
		$UseAsIdentifier = false;
		$UseInLists = false;

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
		if ($ClassId =='') {
			throw new exception("ClassId not specified");
		}

		$objClass = $objDict->Classes[$ClassId];
				
		if (isset($_REQUEST['propid'])){
			$PropId = $_REQUEST['propid'];
			$PropDictId = $DictId;
		}
		if (isset($_REQUEST['propdictid'])){
			$PropDictId = $_REQUEST['propdictid'];
		}
		
		
		if (isset($_REQUEST['classpropid'])){
			$ClassPropId = $_REQUEST['classpropid'];
		}
		
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if ($ClassPropId =='') {
					throw new exception("ClassPropId not specified");
				}
				break;
		}

		if (!empty($ClassPropId)){
			if (!isset($objClass->Properties[$ClassPropId])){
				throw new exception("Unknown Class Property Id");			
			}
			$objClassProp = $objClass->Properties[$ClassPropId];
			if ($PropId == ''){
				$PropId = $objClassProp->PropId;
				$PropDictId = $objClassProp->PropDictId;
				$Cardinality = $objClassProp->Cardinality;
				$Sequence = $objClassProp->Sequence;
				$UseAsName = $objClassProp->UseAsName;
				$UseAsIdentifier = $objClassProp->UseAsIdentifier;
				$UseInLists = $objClassProp->UseInLists;
			}
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
			
			if (isset($FormFields['propid'])){
				$PropId = $FormFields['propid'];
			}
			if (isset($FormFields['propdictid'])){
				$PropDictId = $FormFields['propdictid'];
			}
						
			if (isset($FormFields['classpropid'])){
				$ClassPropId = $FormFields['classpropid'];
			}

			if (isset($FormFields['cardinality'])){
				$Cardinality = $FormFields['cardinality'];
			}
			if (isset($FormFields['sequence'])){
				$Sequence = $FormFields['sequence'];
			}
			
			
			if (isset($FormFields['useasname'])){
				if ($UseAsName === false){
					if ($FormFields['useasname'] == 'true'){
						$UseAsName = true;
					}
				}
			}
			if (isset($FormFields['useasidentifier'])){
				if ($UseAsIdentifier === false){
					if ($FormFields['useasidentifier'] == 'true'){
						$UseAsIdentifier = true;
					}
				}
			}
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if (!empty($PropId)){
			$objPropDict = $objDict;
			if (!($PropDictId == $objDict->Id)){
				$objPropDict = $Dicts->Dictionaries[$PropDictId];
			}
			$objProp = $objPropDict->Properties[$PropId];
		}
		
		
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." class property";		
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
				$PanelB .= pnlClassProperty( $DictId, $ClassId, $ClassPropId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='classproperty.php?dictid=$DictId&classid=$ClassId&classpropid=$ClassPropId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='classproperty.php?dictid=$DictId&classid=$ClassId&classpropid=$ClassPropId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";				
				
				$Tabs .= "<li><a href='#dict'>Dictionary";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='dict'>";
					$TabContent .= "<div id='dict'><h3>in Dictionary</h3></div>";	
					$TabContent .= pnlDict($DictId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

			    $Tabs .= "<li><a href='#class'>Class";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='class'>";
					$TabContent .= "<div id='class'><h3>in Class</h3></div>";	
					$TabContent .= pnlClass($DictId,$ClassId);
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";			    
			    
				break;
			case 'new':
			case 'edit':
				
// select the property
				if ($PropId == ''){
				
					$PanelB .= "<h3>Choose a Property from one of these selections</h3>";
					
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#thisprops'>Properties in this Group</a></li>";					
					$optTabContent .= funSelectProp("this");
					
					$optTabs .= "<li><a href='#myprops'>Properties in My Groups</a></li>";			    
					$optTabContent .= funSelectProp("my");
					
					$optTabs .= "<li><a href='#publishedprops'>Properties in Published Dictionaries</a></li>";			    
					$optTabContent .= funSelectProp("published");
					
					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
				}
				
				else
				{
				
					$PanelB .= '<form method="post" action="doClassProperty.php">';
	
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					if (!( $DictId == '')){
						$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
					}
					if (!( $ClassId == '')){
						$PanelB .= "<input type='hidden' name='classid' value='$ClassId'/>";
					}
					if (!( $PropDictId == '')){
						$PanelB .= "<input type='hidden' name='propdictid' value='$PropDictId'/>";
					}
					if (!( $PropId == '')){
						$PanelB .= "<input type='hidden' name='propid' value='$PropId'/>";
					}
					if (!( $ClassPropId == '')){
						$PanelB .= "<input type='hidden' name='classpropid' value='$ClassPropId'/>";
					}
					
					$PanelB .= '<table class="sdbluebox">';
					
					
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Sequence';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= "<input name='sequence' value='$Sequence' size='3' maxlength = '3'/></td>";
					$PanelB .= '</tr>';					
					
					
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Cardinality';
						$PanelB .= '</th>';
						$PanelB .= '<td>';						
						$PanelB .= "<select name='cardinality'>";
						foreach ($System->Config->Cardinalities as $optCardinality){
							$PanelB .= "<option";
							if (isset($objClassProp)){
								if ($objClassProp->Cardinality == $optCardinality){
									$PanelB .= " selected='true' ";
								}
							}
							$PanelB .= ">$optCardinality</option>";
						}
						$PanelB .= "</select>";						
						
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
					
					
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Use as Name?';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= "<input type='checkbox' name='useasname' value='true' ";
						switch ($UseAsName){
							case true:
								$PanelB .= " checked='true' ";
								break;
						}			
						$PanelB .= '/></td>';
					$PanelB .= '</tr>';					
					
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Use as Identifier?';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= "<input type='checkbox' name='useasidentifier' value='true' ";
						switch ($UseAsIdentifier){
							case true:
								$PanelB .= " checked='true' ";
								break;
						}			
						$PanelB .= '/></td>';
					$PanelB .= '</tr>';					

					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Use in Lists?';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= "<input type='checkbox' name='useinlists' value='true' ";
						switch ($UseInLists){
							case true:
								$PanelB .= " checked='true' ";
								break;
						}			
						$PanelB .= '/></td>';
					$PanelB .= '</tr>';					
					
					
					$PanelB .= '<tr>';
						$PanelB .= '<td/>';
						$PanelB .= '<td>';
						
						switch ( $Mode ){
							case "new":
								$PanelB .= '<input type="submit" value="Add Property">';
								break;
							case "edit":
								$PanelB .= '<input type="submit" value="Update Property">';
								break;
						}
	
						$PanelB .= '</td>';
					$PanelB .= '</tr>';
			
				 	$PanelB .= '</table>';
					$PanelB .= '</form>';

					$PanelB .= pnlProperty($PropDictId,$PropId);
					
				}
				break;
				
				
			case 'delete':
				
				$PanelB .= pnlProperty( $PropDictId, $PropId );

				$PanelB .= "<a href='doClassProperty.php?dictid=$DictId&classid=$ClassId&classpropid=$ClassPropId&mode=delete'>confirm delete?</a><br/>";
				
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


function funSelectProp($Selection, $Action=null){

	global $System;
	global $Mode;
	global $objProp;
	global $objDict;
	
	global $Dicts;

	$Content = "";

	$Content .= "<div class='tabContent' id='".$Selection."props'>";
	
	$Content .= "<div class='sdbluebox'>";


	$opts = array();
	
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if (is_null($optDict->EcoSystem)){								
					if ($optDict->GroupId == $objDict->GroupId){					
						foreach ($optDict->Properties as $optProp){
							$opts[$optDict->Id][$optProp->Id] = $optProp;
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
					
					foreach ($optDict->Properties as $optProp){
						$opts[$optDict->Id][$optProp->Id] = $optProp;
					}
				}
			}
			break;
			
		case "published":
			
			foreach ($Dicts->Dictionaries as $optDict){

				if (!$optDict->Publish){
					continue;
				}				

				foreach ($optDict->Properties as $optProp){
					$opts[$optDict->Id][$optProp->Id] = $optProp;
				}
			}

			break;
			
	}

	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Dictionary</th><th>Property</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optDictId=>$optProperties){
			
			$Content .= "<tr>";
			$Content .= "<td rowspan='".(count($optProperties) + 1)."'>";
				$optDict = $Dicts->Dictionaries[$optDictId];
				$Content .= $optDict->Name;
			$Content .= "</td>";
			$Content .= "</tr>";

			foreach ($optProperties as $optProp){
				
				$UrlParams = array();
				$UrlParams['propdictid'] = $optDictId;
				$UrlParams['propid'] = $optProp->Id;
				$Action = UpdateUrl($UrlParams);
				
				$Content .= "<tr><td><a href='$Action'>".$optProp->Label."</a></td>";
				$Content .= "<td>".nl2br($optProp->Description)."</td>";
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