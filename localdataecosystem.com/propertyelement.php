<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlProperty.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'propertyelement');

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
		$PropId = null;
		$GroupSeq = null;
		$ElementDictId = null;
		$ElementPropId = null;
		$Cardinality = null;
		
		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if (is_null($DictId)) {
			throw new exception("DictId not specified");
		}
		
		if (!isset($Dicts->Dictionaries[$DictId])){
			throw new Exception("Dictionary does not exist");
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		
		$GroupId = $objDict->GroupId;
		
		if (isset($_REQUEST['propid'])){
			$PropId = $_REQUEST['propid'];
		}
		if (is_null($PropId)){
			throw new exception("Property not specified");
		}
		if (!isset($objDict->Properties[$PropId])){
			throw new exception("Property does not Exist");
		}
		$objProp = $objDict->Properties[$PropId];
		
		if (isset($_REQUEST['groupseq'])){
			$GroupSeq = $_REQUEST['groupseq'];
		}
		if (is_null($GroupSeq)){
			throw new exception("Element Group Sequence not specified");
		}
		if (!isset($objProp->ElementGroups[$GroupSeq])){
			throw new exception("Element Group Sequence does not Exist");
		}
		$objElementGroup = $objProp->ElementGroups[$GroupSeq];
		
		if (isset($_REQUEST['elementdictid'])){
			$ElementDictId = $_REQUEST['elementdictid'];
		}
		if (isset($_REQUEST['elementpropid'])){
			$ElementPropId = $_REQUEST['elementpropid'];
		}
		
		
		$objElementProperty = null;
		if (!is_null($ElementDictId)) {
			if (!is_null($ElementPropId)) {
				$objElementProperty = $Dicts->getProperty($ElementDictId, $ElementPropId);
			}
		}

		$objElement = $objElementGroup->getElement($ElementDictId, $ElementPropId);
		if (is_object($objElement)){
			$Cardinality = $objElement->Cardinality;
		}
		
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if (!is_object($objElement)){
					throw new exception("Invalid Element");
				}
				
				break;
		}


		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." property element";	

		$PanelB .= pnlProperty( $DictId, $PropId );
		
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
				
				$PanelB .= pnlPropertyElement($DictId, $PropId, $GroupSeq, $ElementDictId, $ElementPropId);
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='propertyelement.php?dictid=$DictId&propid=$PropId&groupseq=$GroupSeq&elementdictid=$ElementDictId&elementpropid=$ElementPropId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='propertyelement.php?dictid=$DictId&propid=$PropId&groupseq=$GroupSeq&elementdictid=$ElementDictId&elementpropid=$ElementPropId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				
				
				break;
			case 'new':
				
				if (is_null($objElementProperty)){

					$PanelB .= '<h3>Select a property to add as an element from these selections</h3>';
					
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#this'>Properties in this Group</a></li>";
					$optTabContent .= funSelectProp("this");
					
					$optTabs .= "<li><a href='#my'>Properties in My Groups</a></li>";			    
					$optTabContent .= funSelectProp("my");
					
					$optTabs .= "<li><a href='#published'>Properties in Published Dictionaries</a></li>";			    
					$optTabContent .= funSelectProp("published");

					if (!empty($optTabs)){
						$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					
					break;
				}
// otherwise, treat as edit
				
			case 'edit':
				$PanelB .= '<form method="post" action="doPropertyElement.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				$PanelB .= "<input type='hidden' name='propid' value='$PropId'/>";
				$PanelB .= "<input type='hidden' name='groupseq' value='$GroupSeq'/>";
				$PanelB .= "<input type='hidden' name='elementdictid' value='$ElementDictId'/>";
				$PanelB .= "<input type='hidden' name='elementpropid' value='$ElementPropId'/>";
				

				$PanelB .= '<table class="sdbluebox">';
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Element';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= pnlProperty($ElementDictId, $ElementPropId);
					$PanelB .= '</td>';
				$PanelB .= '</tr>';					
						
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Cardinality';
					$PanelB .= '</th>';
					$PanelB .= '<td>';						
					$PanelB .= "<select name='cardinality'>";
					foreach ($System->Config->Cardinalities as $optCardinality){
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
							$PanelB .= '<input type="submit" value="Create New Property Element">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Property Element">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlPropertyElement($DictId, $PropId, $GroupSeq, $ElementDictId, $ElementPropId);
				
				$PanelB .= "<a href='doPropertyElement.php?dictid=$DictId&propid=$PropId&groupseq=$GroupSeq&elementdictid=$ElementDictId&elementpropid=$ElementPropId&mode=delete'>confirm delete?</a><br/>";
				
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

	
function funSelectProp($Selection){

	global $System;
	global $Mode;
	global $objProp;
	global $objDict;
	
	global $Dicts;

	$Content = "";

	$Content .= "<div class='tabContent' id='$Selection'>";
	
	$Content .= "<div class='sdbluebox'>";
	

	$opts = array();
	
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if ($optDict->GroupId == $objDict->GroupId){					
					foreach ($optDict->Properties as $optProp){
						if ($optProp->DictId == $objDict->Id){
							if ($optProp->Id == $objProp->Id){
								continue;
							}
						}
						$opts[$optDict->Id][$optProp->Id] = $optProp;
					}
				}
			}
			break;
			
		case "my":
			
			foreach ($Dicts->Dictionaries as $optDict){
				
				if (is_null($optDict->GroupId)){
					continue;
				}
				
				$optGroup = new clsGroup($optDict->GroupId);
				if (!$optGroup->canEdit){
					continue;
				}				
				
				foreach ($optDict->Properties as $optProp){
					if ($optProp->DictId == $objDict->Id){
						if ($optProp->Id == $objProp->Id){
							continue;
						}
					}
				
					$opts[$optDict->Id][$optProp->Id] = $optProp;
				}
			}
			break;
			
		case "published":
			
			foreach ($Dicts->Dictionaries as $optDict){

				if (!$optDict->Publish){
					continue;
				}				

				foreach ($optDict->Properties as $optProp){
					if ($optProp->DictId == $objDict->Id){
						if ($optProp->Id == $objProp->Id){
							continue;
						}
					}
				
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
				$UrlParams['elementdictid'] = $optDictId;
				$UrlParams['elementpropid'] = $optProp->Id;
				$ReturnUrl = UpdateUrl($UrlParams);
								
				$Content .= "<tr><td><a href='$ReturnUrl'>".$optProp->Label."</a></td>";
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