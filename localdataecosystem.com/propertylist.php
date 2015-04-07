<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlProperty.php");
	require_once("panel/pnlList.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'propertylist');

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

		$DictId = '';
		$GroupId = '';
		$PropId = '';
		
		$ListId = null;
		$ListDictId = null;
		

		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if ($DictId =='') {
			throw new exception("DictId not specified");
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		$GroupId = $objDict->GroupId;

		
		if (isset($_REQUEST['propid'])){
			$PropId = $_REQUEST['propid'];
		}
		if ($PropId =='') {
			throw new exception("PropId not specified");
		}

		$objProp = $objDict->Properties[$PropId];
		
		
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
			if (!isset($objListDict->Lists[$ListId])){
				throw new exception("Invalid List");
			}
		}
		
		if ($System->Session->Error){
			$FormFields = GetUserInput(PAGE_NAME);
			if (isset($FormFields['dictid'])){
				$DictId = $FormFields['dictid'];
				$objDict = $Dicts->Dictionaries[$DictId];
			}
			if (isset($FormFields['propid'])){
				$PropId = $FormFields['propid'];
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
		
		$Page->Title = $Mode." property list";
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
				$optTabContent .= "<div class='tabContent' id='thislists'>";				
				$optTabContent .= funSelectList("this");
				$optTabContent .= "</div>";
				
				$optTabs .= "<li><a href='#mylists'>Lists in My Groups</a></li>";			    
				$optTabContent .= "<div class='tabContent' id='mylists'>";								
				$optTabContent .= funSelectList("my");
				$optTabContent .= "</div>";
				
				$optTabs .= "<li><a href='#publishedlists'>Lists in Published Dictionaries</a></li>";			    
				$optTabContent .= "<div class='tabContent' id='publishedlists'>";												
				$optTabContent .= funSelectList("published");
				$optTabContent .= "</div>";
				
				if (!empty($optTabs)){
					$PanelB .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
				}
								
				break;
				
				
			case 'delete':
				
				$PanelB .= pnlProperty( $DictId, $PropId );
				$PanelB .= pnlList( $ListDictId, $ListId );
				
				$PanelB .= "<a href='doPropertyList.php?dictid=$DictId&propid=$PropId&listdictid=$ListDictId&listid=$ListId&mode=delete'>confirm remove list from this property?</a><br/>";
				
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

function funSelectList($Selection='this'){

	global $System;
	global $Mode;

	global $objDict;
	
	global $Dicts;
	
	$Content = "";
	
	$Content .= "<div class='sdbluebox'>";

	$DictFieldName = "listdictid";
	$ListFieldName = "listid";
	
	$opts = array();

	
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if (is_null($optDict->EcoSystem)){
					if ($optDict->GroupId == $objDict->GroupId){					
						foreach ($optDict->Lists as $optList){
							$opts[$optDict->Id][$optList->Id] = $optList;
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
					
					foreach ($optDict->Lists as $optList){
						$opts[$optDict->Id][$optList->Id] = $optList;
					}
				}
			}
			break;
			
		case "published":
			
			foreach ($Dicts->Dictionaries as $optDict){

				if (!$optDict->Publish){
					continue;
				}				

				foreach ($optDict->Lists as $optList){
					$opts[$optDict->Id][$optList->Id] = $optList;
				}
			}

			break;
			
	}

	
	if (count($opts) > 0){

		$Content .= "<table class='list'>";

		$Content .= "<thead><tr><th>Dictionary</th><th>List</th><th>Description</th></tr></thead>";
		$Content .= "<tbody>";
		
		foreach ($opts as $optDictId=>$optLists){
					
			$Content .= "<tr>";			
			
			$Content .= "<td rowspan='".(count($optLists) + 1)."'>";
				$optDict = $Dicts->Dictionaries[$optDictId];
				$Content .= $optDict->Name;
			$Content .= "</td>";
			$Content .= "</tr>";
			
			usort($optLists, 'funSortLists');
							
			foreach ($optLists as $optList){
				
				$UrlParams = array();
				$UrlParams[$DictFieldName] = $optList->DictId;
				$UrlParams[$ListFieldName] = $optList->Id;
				$ReturnUrl = UpdateUrl($UrlParams,'doPropertyList.php');
									
				$Content .= "<tr><td><a href='$ReturnUrl'>".$optList->Label."<a></td>";
				$Content .= "<td>".nl2br($optList->Description)."</td>";
				$Content .= "</tr>";
			}
	
		}
		$Content .= "</tbody>";
		$Content .= "</table>";
		
	}
		
 	$Content .= "</div>";
 		
	return $Content;
}
	
		
function funSortLists($objList1, $objList2){
	
	if (strtolower($objList1->Label) == strtolower($objList2->Label)) {
        return 0;
    }
    return (strtolower($objList1->Label) < strtolower($objList2->Label)) ? -1 : 1;
}

	
?>