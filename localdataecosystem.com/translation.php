<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlSpec.php");
	require_once("panel/pnlTranslation.php");
	
	require_once("class/clsDict.php");	
	require_once("class/clsProfile.php");	
	
	
	define('PAGE_NAME', 'translation');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	try {

		$Specs = new clsSpecs();
		$Dicts = new clsDicts();
		
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	
		
		$PanelB = '';
		$PanelC = '';
		
		$Tabs = "";
		$TabContent = "";
						
		$SpecId = null;
		$TransId = null;
		$Name = '';
		$Description = "";
		
		$ListDictId = null;
		$ListId = null;
				

		if (isset($_REQUEST['specid'])){
			$SpecId = $_REQUEST['specid'];
		}
		if (is_null($SpecId)) {
			throw new exception("SpecId not specified");
		}
		if (!isset($Specs->Items[$SpecId])){
			throw new exception("Unknown SpecId");
		}
		
		$objSpec = $Specs->Items[$SpecId];
		$GroupId = $objSpec->GroupId;
		
		if (isset($_REQUEST['transid'])){
			$TransId = $_REQUEST['transid'];
		}

		switch ($Mode){
			case 'new':
				break;
			default:
				if (is_null($TransId)) {
					throw new exception("TransId not specified");
				}
				break;
		}

		if (!is_null($TransId)){
			if (!isset($objSpec->Translations[$TransId])){
				throw new exception("Unknown Translation");
			}
			$objTrans = $objSpec->Translations[$TransId];
			$Name = $objTrans->Name;
			$Description = $objTrans->Description;
		}
		
		
		if ($System->Session->Error){
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objSpec->canView === false){
			throw new exception("You cannot view this Specification");
		}
		
		$Page->Title = $Mode." translation";		
		$PanelB .= "<h1>".$Page->Title."</h1>";
		
		
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objSpec->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($objSpec->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objSpec->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objSpec->canEdit){
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
				$PanelB .= pnlTranslation( $SpecId, $TransId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSpec->canEdit === true){
					$PanelB .= "<li><a href='translation.php?specid=$SpecId&transid=$TransId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSpec->canControl === true){
					$PanelB .= "<li><a href='translation.php?specid=$SpecId&transid=$TransId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				

				
				$Tabs .= "<li><a href='#spec'>Specification";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='spec'>";
					$TabContent .= "<div id='dict'><h3>in Specification</h3></div>";	
					$TabContent .= pnlSpec($SpecId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
			    $Tabs .= "<li><a href='#items'>Items";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='items'>";
					$TabContent .= "<h3>Items</h3>";
					
					if (count($objTrans->Items) > 0){

						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>From Value</th><th>To Value</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';

						foreach ( $objTrans->Items as $objItem){
							$num = $num + 1;
							
							$TabContent .= "<tr>";
							$TabContent .= "<td><a href='transitem.php?specid=$SpecId&transid=$TransId&itemid=".$objItem->Id."'>".$objItem->Id."</td>";
							$TabContent .= "<td>".$objItem->FromValue."</td>";
							$TabContent .= "<td>".$objItem->ToValue."</td>";														
							$TabContent .= "</tr>";
							
							
						}
				 		$TabContent .= '</table>';
						
					}
					
					
					if ($objSpec->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='transitem.php?specid=$SpecId&transid=$TransId&mode=new'>&bull; add</a></li> </ul></div>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    
			    if ( $objSpec->canEdit ){
					$Tabs .= "<li><a href='#setlist'>set a List</a></li>";
				    
					$TabContent .= "<div class='tabContent hide' id='setlist'>";
					
					if (!is_null($objTrans->List)){
						$TabContent .= "<a href='doTransList.php?mode=delete&specid=$SpecId&transid=$TransId'>remove list</a><br/>";
					}
										
					$TabContent .= "<h3>Set a List from any of these selections</h3>";	
				
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#thislists'>Lists in this Group</a></li>";
					$optTabContent .= funSelectList("this");
					
					$optTabs .= "<li><a href='#mylists'>Lists in My Groups</a></li>";
					$optTabContent .= funSelectList("my");
										
					$optTabs .= "<li><a href='#publishedlists'>Lists in Published Dictionaries</a></li>";			    
					$optTabContent .= funSelectList("published");

					if (!empty($optTabs)){
						$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					$TabContent .= "</div>";
				}
			    				
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doTranslation.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!is_null($SpecId)){
					$PanelB .= "<input type='hidden' name='specid' value='$SpecId'/>";
				}
				if (!is_null($TransId)){
					$PanelB .= "<input type='hidden' name='transid' value='$TransId'/>";
				}
				
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $TransId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Name';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="name" size="30" maxlength="100" value="'.$Name.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

						
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Description';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<textarea rows = "5" cols = "80" name="description" >';
					$PanelB .= $Description;
					$PanelB .= '</textarea>';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Translation">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Translation">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlTranslation( $SpecId, $TransId );

				$PanelB .= "<a href='doTranslation.php?specid=$SpecId&transid=$TransId&mode=delete'>confirm delete?</a><br/>";
				
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
	global $objList;
	global $objSpec;
	global $objTrans;
	
	global $Dicts;

	$Content = "";

	$Content .= "<div class='tabContent' id='".$Selection."lists'>";
	
	$Content .= "<div class='sdbluebox'>";
	

	$opts = array();
	
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if ($optDict->GroupId == $objSpec->GroupId){					
					foreach ($optDict->Lists as $optList){					
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
				$optDict = new $Dicts->Dictionaries[$optDictId];
				$Content .= $optDict->Name;
			$Content .= "</td>";
			$Content .= "</tr>";

			foreach ($optLists as $optList){
				
				$ReturnUrl = "doTransList.php?mode=edit&specid=".$objSpec->Id."&transid=".$objTrans->Id."&listdictid=$optDictId&listid=".$optList->Id;
				
				$Content .= "<tr><td><a href='$ReturnUrl'>".$optList->Label."</a></td>";
				$Content .= "<td>".nl2br($optList->Description)."</td>";
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