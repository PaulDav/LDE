<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlValue.php");
	require_once("panel/pnlList.php");
	
	require_once("panel/pnlListValue.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'listvalue');

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
		$ListValueId = '';
		$ListId = '';
		$ValueId = '';
		$ValueDictId = '';
		
		$Label = '';
		$Description = '';		
		$URI = '';
		$Code = '';
		
		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if ($DictId =='') {
			throw new exception("DictId not specified");
		}
		
		$objDict = $Dicts->Dictionaries[$DictId];
		$GroupId = $objDict->GroupId;

		
		if (isset($_REQUEST['listid'])){
			$ListId = $_REQUEST['listid'];
		}
		if ($ListId =='') {
			throw new exception("ListId not specified");
		}

		$objList = $objDict->Lists[$ListId];
				
		if (isset($_REQUEST['valueid'])){
			$ValueId = $_REQUEST['valueid'];
			$ValueDictId = $DictId;
		}
		if (isset($_REQUEST['valuedictid'])){
			$ValueDictId = $_REQUEST['valuedictid'];
		}

		if (isset($_REQUEST['listvalueid'])){
			$ListValueId = $_REQUEST['listvalueid'];
		}		
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if ($ListValueId =='') {
					throw new exception("ListValueId not specified");
				}
				break;
		}

		if (!empty($ListValueId)){
			if (!isset($objList->Values[$ListValueId])){
				throw new exception("Unknown List Value Id");			
			}
			$objListValue = $objList->Values[$ListValueId];
			if ($ValueId == ''){
				$ValueId = $objListValue->ValueId;
				$ValueDictId = $objListValue->ValueDictId;
			}

			$objValueDict = $objDict;
			if (!($ValueDictId = $objDict->Id)){
				$objValueDict = $Dicts->Dictionaries[$ValueDictId];
			}
			
			$objValue = $objValueDict->Values[$ValueId];

			$Label = $objValue->Label;
			$Description = $objValue->Description;		
			$URI = $objValue->URI;
			$Code = $objValue->Code;
		}


		if ($System->Session->Error){
			$FormFields = GetUserInput(PAGE_NAME);
			if (isset($FormFields['dictid'])){
				$DictId = $FormFields['dictid'];
				$objDict = $Dicts->Dictionaries[$DictId];
			}
			if (isset($FormFields['listid'])){
				$ListId = $FormFields['listid'];
			}
			
			if (isset($FormFields['valueid'])){
				$ValueId = $FormFields['valueid'];
			}
			if (isset($FormFields['listvalueid'])){
				$ListValueId = $FormFields['listvalueid'];
			}

			if (isset($FormFields['label'])){			
				$Label = $FormFields['label'];
			}
			if (isset($FormFields['description'])){			
				$Description = $FormFields['description'];
			}
			if (isset($FormFields['uri'])){			
				$URI = $FormFields['uri'];
			}
			if (isset($FormFields['code'])){			
				$Code = $FormFields['code'];
			}
						
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." list value";
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
				$PanelB .= pnlListValue( $DictId, $ListId, $ListValueId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='listvalue.php?dictid=$DictId&listid=$ListId&listvalueid=$ListValueId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='listvalue.php?dictid=$DictId&listid=$ListId&listvalueid=$ListValueId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";				
				
				$Tabs .= "<li><a href='#dict'>Dictionary";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='dict'>";
					$TabContent .= "<div id='dict'><h3>in Dictionary</h3></div>";	
					$TabContent .= pnlDict($DictId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

			    $Tabs .= "<li><a href='#list'>List";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='list'>";
					$TabContent .= "<h3>in List</h3>";	
					$TabContent .= pnlList($DictId,$ListId);
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
				break;
			case 'new':
			case 'edit':

				$PanelB .= '<form method="post" action="doListValue.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $DictId == '')){
					$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				}
				if (!( $ListId == '')){
					$PanelB .= "<input type='hidden' name='listid' value='$ListId'/>";
				}
				if (!( $ValueId == '')){
					$PanelB .= "<input type='hidden' name='valueid' value='$ValueId'/>";
				}
				if (!( $ListValueId == '')){
					$PanelB .= "<input type='hidden' name='listvalueid' value='$ListValueId'/>";
				}
				
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $ValueId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}
				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Label';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="label" size="30" maxlength="100" value="'.$Label.'">';
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
					$PanelB .= '<th>';
					$PanelB .= 'Code';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="code" size="12" maxlength="30" value="'.$Code.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'URI';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="uri" size="50" maxlength="300" value="'.$URI.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Value">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Value">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
								
				
			case 'delete':
				
				$PanelB .= pnlListValue( $DictId, $ListId, $ListValueId );

				$PanelB .= "<a href='doListValue.php?dictid=$DictId&listid=$ListId&listvalueid=$ListValueId&mode=delete'>confirm delete?</a><br/>";
				
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