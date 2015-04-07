<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlProperty.php");
	require_once("panel/pnlClass.php");
	require_once("panel/pnlRel.php");
	
	require_once("panel/pnlList.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'haspropertylist');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	try {

		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$Dicts = new clsDicts();
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";

		$DictId = null;
		$GroupId = null;
		$ClassId = null;
		$RelId = null;
		$HasPropId = null;
		
		$ListId = '';
		$ListDictId = '';
		
		$objParent = null;
		

		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if ($DictId =='') {
			throw new exception("DictId not specified");
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		$GroupId = $objDict->GroupId;

		$ParentType = null;
		if (isset($_REQUEST['classid'])){
			$ClassId = $_REQUEST['classid'];
			$objParent = $objDict->Classes[$ClassId];
			$ParentType = 'class';
			$ParentQueryString = "classid=$ClassId";
		}
		
		if (isset($_REQUEST['relid'])){
			$RelId = $_REQUEST['relid'];
			$objParent = $objDict->Relationships[$RelId];
			$ParentType = 'relationship';
			$ParentQueryString = "relid=$RelId";
		}
		
		
		if (is_null($objParent)) {
			throw new exception("Parent not specified");
		}

		if (isset($_REQUEST['haspropid'])){
			$HasPropId = $_REQUEST['haspropid'];
		}
		if (is_null($HasPropId)) {
			throw new exception("Has Property Id not specified");
		}
		$objHasProp = $objParent->Properties[$HasPropId];
		
		
		if (isset($_REQUEST['listid'])){
			$ListId = $_REQUEST['listid'];
			$ListDictId = $DictId;
			if (isset($_REQUEST['listdictid'])){
				$ListDictId = $_REQUEST['listdictid'];
			}
			$objListDict = $objDict;
			if (!($ListDictId == $objDict->Id)){
				$objListDict = $Dicts->Dictionaries[$ListDictId];
			}
			if (!isset($$objListDict->Lists[$ListId])){
				throw new exception("Invalid List");
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
			
			if (isset($FormFields['haspropid'])){
				$HasPropId = $FormFields['haspropid'];
			}

			if (isset($FormFields['listdictid'])){
				$ListDictId = $FormFields['listdictid'];
			}
			if (isset($FormFields['listid'])){
				$ListId = $FormFields['listid'];
			}
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." $ParentType property list";
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'new':
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
			case 'new':
				
// select the list
				
				$PanelB .= "<h3>Choose a List from one of these selections</h3>";
				
				$optTabs = "";
				$optTabContent = "";
				
				$optTabs .= "<li><a href='#thislists'>Lists in this Group</a></li>";
				$optTabContent .= funSelectList("this");
				
				$optTabs .= "<li><a href='#mylists'>Lists in My Groups</a></li>";			    
				$optTabContent .= funSelectList("my");
				
				$optTabs .= "<li><a href='#publishedlists'>Lists in Published Dictionaries</a></li>";			    
				$optTabContent .= funSelectList("published");

				if (!empty($optTabs)){
					$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
				}
								
				break;
				
				
			case 'delete':
				
//				$PanelB .= pnlProperty( $DictId, $PropId );

//				$PanelB .= "<a href='doHasPropertyList.php?dictid=$DictId&$ParentQueryString&haspropid=$HasPropId&mode=delete'>confirm delete?</a><br/>";
				
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

	
	
function funSelectList($Selection){

	global $System;
	global $Mode;
	global $objDict;
	
	global $ParentType;
	global $objParent;
	global $HasPropId;
	
	global $Dicts;

	$Content = "";

	$Content .= "<div class='tabContent' id='".$Selection."lists'>";
	
	$Content .= "<div class='sdbluebox'>";
	

	$opts = array();
	
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if ($optDict->GroupId == $objDict->GroupId){					
					foreach ($objDict->Lists as $optList){
						$opts[$optDict->Id][$optList->Id] = $optList;
					}
				}
			}
			break;
			
		case "my":
			
			foreach ($Dicts->Dictionaries as $optDict){
				
				$optGroup = new clsGroup($optDict->GroupId);
				if (!$optGroup->canEdit){
					continue;
				}				
				
				foreach ($optDict->Lists as $optList){
					$opts[$optDict->Id][$optList->Id] = $optList;
				}
			}
			break;
			
		case "published":
			
			foreach ($Dicts->Dictionaries as $optDict){

				if (!$optDict->Publish){
					continue;
				}				

				foreach ($objDict->Lists as $optList){
					$opts[$optDict->Id][$optList->Id] = $optList;
				}
			}

			break;
			
	}

	if (count($opts) > 0){
		
		foreach ($opts as $optDictId=>$optLists){
			
			
			$Content .= '<form method="post" action="doHasPropertyList.php">';
		
			$ReturnURL = $_SERVER['SCRIPT_NAME'];
			$QueryString = $_SERVER['QUERY_STRING'];
			$ReturnURL = $ReturnURL.'?'.$QueryString;
				
			$Content .= "<input type='hidden' name='ReturnURL' value='$ReturnURL'/>";
			$Content .= "<input type='hidden' name='mode' value='new'/>";
			$Content .= "<input type='hidden' name='dictid' value='".$objDict->Id."'/>";

			switch ($ParentType){
				case 'class':
					$Content .= "<input type='hidden' name='classid' value='$objParent->Id'/>";
					break;
				case 'relationship':
					$Content .= "<input type='hidden' name='relid' value='$objParent->Id'/>";
					break;					
			}

			
			$Content .= "<input type='hidden' name='haspropid' value='$HasPropId'/>";
						
			$Content .= "<input type='hidden' name='listdictid' value='".$optDictId."'/>";
			
			
			$Content .= "<table class='list'>";

			$Content .= "<thead><tr><th>Dictionary</th><th>List</th><th>Description</th></tr></thead>";
			$Content .= "<tbody>";
						
			$Content .= "<tr>";
			$Content .= "<td rowspan='".(count($optLists) + 1)."'>";
				$optDict = $Dicts->Dictionaries[$optDictId];
				$Content .= $optDict->Name;
			$Content .= "</td>";
			$Content .= "</tr>";

			foreach ($optLists as $optList){
				$Content .= "<tr><td><input type='radio' name='listid' value='".$optList->Id."'/>".$optList->Label."</td>";
				$Content .= "<td>".nl2br($optList->Description)."</td>";
				$Content .= "</tr>";
			}
			
			$Content .= "</tbody>";
			$Content .= "</table>";
	
			$Content .= '<table><tr>';
				$Content .= '<td/>';
				$Content .= '<td>';
				
				$Content .= '<input type="submit" value="Add List">';
		
				$Content .= '</td>';
			$Content .= '</tr></table>';
				
		 	$Content .= "</form>";
		}
			
	}

	
 	$Content .= "</div>";
 	
	$Content .= "</div>";
	
	return $Content;
}
	
	
	
?>