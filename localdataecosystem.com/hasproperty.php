<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlProperty.php");
	require_once("panel/pnlClass.php");
	require_once("panel/pnlRel.php");
	
	require_once("panel/pnlHasProperty.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'hasproperty');

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
		$HasPropId = null;
		$ClassId = null;
		$RelId = null;
		$PropId = null;
		$PropDictId = null;
		
		$Cardinality = '';
		$UseAsName = false;
		$UseAsIdentifier = false;
		
		$objParent = null;
		$objHasProp = null;
		
		$ParentType = "";

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
			$objParent = $objDict->Classes[$ClassId];
			$ParentType = "class";
		}
		
		if (isset($_REQUEST['relid'])){
			$RelId = $_REQUEST['relid'];
			$objParent = $objDict->Relationships[$RelId];
			$ParentType = "relationship";
		}
		
		
		if (is_null($objParent)) {
			throw new exception("No Parent Id specified");
		}
		

				
		if (isset($_REQUEST['propid'])){
			$PropId = $_REQUEST['propid'];
			$PropDictId = $DictId;
		}
		if (isset($_REQUEST['propdictid'])){
			$PropDictId = $_REQUEST['propdictid'];
		}
		
		
		if (isset($_REQUEST['haspropid'])){
			$HasPropId = $_REQUEST['haspropid'];
		}
		
		
		
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if ($HasPropId =='') {
					throw new exception("HasPropId not specified");
				}
				break;
		}

		if (!is_null($objParent)){
			if (!is_null($HasPropId)){
				if (!isset($objParent->Properties[$HasPropId])){
					throw new exception("Unknown Has Property Id");			
				}
				$objHasProp = $objParent->Properties[$HasPropId];
				if ($PropId == ''){
					$PropId = $objHasProp->PropId;
					$PropDictId = $objHasProp->PropDictId;
					$Cardinality = $objHasProp->Cardinality;
					$UseAsName = $objHasProp->UseAsName;
					$UseAsIdentifier = $objHasProp->UseAsIdentifier;
				}
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
			if (isset($FormFields['relid'])){
				$RelId = $FormFields['relid'];
			}
			
			if (isset($FormFields['propid'])){
				$PropId = $FormFields['propid'];
			}
			if (isset($FormFields['propdictid'])){
				$PropDictId = $FormFields['propdictid'];
			}
						
			if (isset($FormFields['haspropid'])){
				$HasPropId = $FormFields['haspropid'];
			}

			if (isset($FormFields['cardinality'])){
				$Cardinality = $FormFields['cardinality'];
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
		
		$Page->Title = "$Mode $ParentType property";		
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

		$ParentQueryString = "";
		switch ($ParentType){
			case "class":
				$ParentQueryString = "classid=$ClassId";
				break;
			case "relationship":
				$ParentQueryString = "relid=$RelId";
				break;						
		}
		

		switch ($Mode){
			case 'view':
				$PanelB .= pnlHasProperty( $ParentType, $DictId, $objParent->Id, $HasPropId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='hasproperty.php?dictid=$DictId&$ParentQueryString&haspropid=$HasPropId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='hasproperty.php?dictid=$DictId&$ParentQueryString&haspropid=$HasPropId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";				
				
				$Tabs .= "<li><a href='#dict'>Dictionary";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='dict'>";
					$TabContent .= "<div id='dict'><h3>in Dictionary</h3></div>";	
					$TabContent .= pnlDict($DictId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

			    switch ($ParentType){
			    	case "class":
			    		$Tabs .= "<li><a href='#class'>Class";
						$num = 0;
						$TabContent .= "<div class='tabContent hide' id='class'>";
							$TabContent .= "<div id='class'><h3>in Class</h3></div>";	
							$TabContent .= pnlClass($DictId,$ClassId);
						$TabContent .= "</div>";
					    $Tabs .= "</a></li>";
					    break;
			    	case "relationship":
			    		$Tabs .= "<li><a href='#relationship'>Relationship";
						$num = 0;
						$TabContent .= "<div class='tabContent hide' id='relationship'>";
							$TabContent .= "<div id='class'><h3>in Relationship</h3></div>";	
							$TabContent .= pnlRel($DictId,$RelId);
						$TabContent .= "</div>";
					    $Tabs .= "</a></li>";
					    break;
					    
			    }

			    
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
				
					$PanelB .= '<form method="post" action="doHasProperty.php">';
	
					$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
					if (!( $DictId == '')){
						$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
					}
					if (!is_null($ClassId)){
						$PanelB .= "<input type='hidden' name='classid' value='$ClassId'/>";
					}
					if (!is_null($RelId)){
						$PanelB .= "<input type='hidden' name='relid' value='$RelId'/>";
					}
					
					if (!( $PropDictId == '')){
						$PanelB .= "<input type='hidden' name='propdictid' value='$PropDictId'/>";
					}
					if (!( $PropId == '')){
						$PanelB .= "<input type='hidden' name='propid' value='$PropId'/>";
					}
					if (!( $HasPropId == '')){
						$PanelB .= "<input type='hidden' name='haspropid' value='$HasPropId'/>";
					}
					
					$PanelB .= '<table class="sdbluebox">';
					
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
				
				$PanelB .= pnlHasProperty( $ParentType, $DictId, $objParent->Id, $HasPropId );
				switch ($ParentType){
					case 'class':
						$PanelB .= "<a href='doHasProperty.php?dictid=$DictId&classid=$ClassId&haspropid=$HasPropId&mode=delete'>confirm delete?</a><br/>";
						break;
					case 'relationship':
						$PanelB .= "<a href='doHasProperty.php?dictid=$DictId&relid=$RelId&haspropid=$HasPropId&mode=delete'>confirm delete?</a><br/>";
						break;
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