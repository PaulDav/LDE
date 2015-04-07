<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");

	require_once("panel/pnlSpec.php");	
	require_once("panel/pnlSpecMap.php");	
	
	require_once("panel/pnlProfile.php");
	require_once("panel/pnlProfileClass.php");
	
	require_once("panel/pnlGroup.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsProfile.php");
	
	define('PAGE_NAME', 'spec');

	session_start();
		
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
	
	$objSpec = null;
	$objProfile = null;

	try {

		$Dicts = new clsDicts();
		$Profiles = new clsProfiles();
		$Specs = new clsSpecs();
				
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	

		$PanelB = '';
		$PanelC = '';

		$Tabs = "";
		$TabContent = "";

		$SpecId = null;
		$ProfileId = null;
		$GroupId = null;
		$Name = '';
		$Description = '';
		$FileType = null;
		$Publish = false;
		$ProfileId = null;
		$objProfile = null;

		if (isset($_REQUEST['specid'])){
			$SpecId = $_REQUEST['specid'];
		}
		
		if (isset($_REQUEST['groupid'])){
			$GroupId = $_REQUEST['groupid'];
		}
		

		switch ($Mode){
			case 'new':
				if ($GroupId =='') {
					throw new exception("GroupId not specified");
				}

				break;
			default:
				if ($SpecId =='') {
					throw new exception("SpecId not specified");
				}

				break;
		}

		if (!empty($SpecId)){
			$objSpec = $Specs->Items[$SpecId];
			$GroupId = $objSpec->GroupId;
			$Name = $objSpec->Name;
			$Description = $objSpec->Description;
			$FileType = $objSpec->FileType;
			$Publish = $objSpec->Publish;
			$ProfileId = $objSpec->ProfileId;
			$objProfile = $Profiles->Items[$ProfileId];
			
		}		


		if ($System->Session->Error){
			if (isset($FormFields['groupid'])){
				$GroupId = $FormFields['groupid'];
			}
			if (isset($FormFields['name'])){
				$Name = $FormFields['name'];
			}
			if (isset($FormFields['description'])){
				$Description = $FormFields['description'];
			}				
			if (isset($FormFields['publish'])){
				if ($FormFields['publish'] == 'yes'){
					$Publish = true;
				}
			}				
			if (isset($FormFields['profileid'])){
				$ProfileId = $FormFields['profileid'];
			}				
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if (!empty($GroupId)){
			$Group = new clsGroup($GroupId);
			if ($Group->canView === false){
				throw new exception("You cannot view this Group");
			}
		}		
		
		$Page->Title = $Mode." import specification";
		$PanelB .= "<h1>".$Page->Title."</h1>";
				
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objSpec->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($Group->canEdit){
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
				
				$PanelB .= pnlSpec( $SpecId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSpec->canEdit === true){
					$PanelB .= "<li><a href='spec.php?specid=$SpecId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSpec->canControl === true){
					$PanelB .= "<li><a href='spec.php?specid=$SpecId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$Tabs .= "<li><a href='#group'>Group";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='group'>";
					$TabContent .= "<h3>in Group</h3>";	
					$TabContent .= pnlGroup($GroupId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

			    if (is_object($objProfile)){
				    $Tabs .= "<li><a href='#map'>Map";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='map'>";
						$TabContent .= "<h3>Map</h3>";
						
						$TabContent .= pnlSpecMap($SpecId);
						$PanelB .= "<div class='hmenu'><ul>";
						if ($objSpec->canEdit === true){
							$TabContent .= "<li><a href='spec.php?specid=$SpecId&mode=edit#map'>&bull; edit</a></li> ";
						}
						$PanelB .= "</ul></div>";
						
						
					$TabContent .= "</div>";
				    $Tabs .= "</a></li>";
			    }
			    

			    $Tabs .= "<li><a href='#translations'>Translations";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='translations'>";
					$TabContent .= "<h3>Translations</h3>";	

					if (count($objSpec->Translations) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>id</th><th>Name</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objSpec->Translations as $objTranslation){
							
							$num = $num + 1;
							
							$TabContent .= "<tr>";
							$TabContent .= "<td><a href='translation.php?specid=".$objSpec->Id."&transid=".$objTranslation->Id."'>".$objTranslation->Id."</a></td>";
							$TabContent .= "<td>".$objTranslation->Name."</td>";
							$TabContent .= "<td>".nl2br(Truncate($objTranslation->Description))."</td>";
						}
				 		$TabContent .= '</table>';
					}
					
										
					if ($objSpec->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='translation.php?specid=$objSpec->Id&mode=new'>&bull; add</a></li> </ul></div>";
					}
	
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doSpec.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $SpecId == '')){
					$PanelB .= "<input type='hidden' name='specid' value='$SpecId'/>";
				}
				if (!( $GroupId == '')){
					$PanelB .= "<input type='hidden' name='groupid' value='$GroupId'/>";
				}				
										
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $SpecId;
						$PanelB .= '</td>';
					$PanelB .= '</tr>';					
				}

				
				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Profile';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<select name='profileid'>";
					$PanelB .= "<option/>";
					foreach ($Profiles->Items as $optProfile){
						$PanelB .= "<option value='".$optProfile->Id."' ";
						if ($optProfile->Id == $ProfileId){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">".$optProfile->Name."</option>";
					}					
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				
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
					$PanelB .= '<th>';
					$PanelB .= 'File Type';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= "<select name='filetype'>";
					$PanelB .= "<option/>";
					foreach ($System->Config->ImportFileTypes as $optFileType){
						$PanelB .= "<option value='$optFileType' ";
						if ($optFileType == $FileType){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">".$optFileType."</option>";
					}					
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				

				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Publish?';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					
					$PanelB .= "<select name='publish'>";
					$PanelB .= "<option>No</option>";
					$PanelB .= "<option";
					if ($Publish === true){
						$PanelB .= " selected='true' ";
					}
					$PanelB .= ">Yes</option>";
					$PanelB .= "</select>";
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
								
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Specification">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Specification">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';
				
				
				
				if (is_object($objProfile)){
				    $Tabs .= "<li><a href='#map'>Map";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='map'>";
						$TabContent .= "<h3>Map</h3>";
						
						
						$TabContent .= "<form method='post' action='doSpecMap.php'>";	
						$TabContent .= "<input type='hidden' name='specid' value='$SpecId'";					
						$objForm = new clsForm($objProfile->Id);
						$TabContent .= frmSpecMap($objForm);
						$TabContent .= "<input type='submit' value='Update'/>";
						
						$TabContent .= "</form>";
						
					$TabContent .= "</div>";
				    $Tabs .= "</a></li>";
			    }
				

				break;
				
			case 'delete':
				
				$PanelB .= pnlSpec( $SpecId );
				
				$PanelB .= "<a href='doSpec.php?specid=$SpecId&Mode=delete'>confirm delete?</a><br/>";
				
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
		
	
	
function frmSpecMap($objForm){
		
	global $Dicts;
	global $Specs;
	global $objProfile;
	global $objSpec;
	
	$Content = "";
	
	$Content .= "<input type='hidden' name='".$objForm->FormName."'/>";
	
					
	$objClass = $objForm->Class;
	$Content .= "<h4>".$objClass->Label."</h4>";
	
	$Content .= "<table class='sdbluebox'>";
	$Content .= "<thead><tr><th/><th>Column</th><th>Default</th><th>Translation</th></tr></thead>";
	foreach ($objForm->FormFields as $FieldNum=>$arrFields){
		if (isset($arrFields[1])){
			$objFormField = $arrFields[1];

			$FieldName = $objFormField->FieldName;
			
			$objProp = $objFormField->Property;
			$Content .= "<tr>";
			$Content .= "<th>".$objProp->Label."</th>";

			$ColNum = '';
			$Default = '';
			$TransId = "";
			$xmlField = $Specs->xpath->query("spec:Fields/spec:Field[@name='$FieldName']",$objSpec->xml)->item(0);
			if (is_object($xmlField)){
				$ColNum = $xmlField->getAttribute("col");
				$Default = $xmlField->getAttribute("default");
				$TransId = $xmlField->getAttribute("translation");
			}
						
			$Content .= "<td><input type='text'  name='".$FieldName."_colnum' value='$ColNum' size='3'/></td>";
			$Content .= "<td><input type='text'  name='".$FieldName."_default' value='$Default' size='10'/></td>";
			
			$Content .= "<td>";
			$Content .= "<select name='".$FieldName."_translation'>";
			$Content .= "<option/>";
			foreach ($objSpec->Translations as $optTrans){
				$Content .= "<option";
				if (!IsEmptyString($TransId)){
					if ($optTrans->Id == $TransId){
						$Content .= " selected='true' ";
					}
				}
				$Content .= " value='".$optTrans->Id."'>".$optTrans->Name."</option>";
			}
			$Content .= "</select>";
			$Content .= "</td>";
			
			
			$Content .= "</tr>";
		}
	}
			
	$Content .= "</table>";
	
	
	foreach ($objForm->LinkForms as $ProfileRelId=>$arrLinks){
		foreach ($arrLinks as $seq=>$objLinkForm){
			if ($seq == 0){
//				if ($objLinkForm->Cardinality == 'extend'){
					$Content .= "<strong>".$objLinkForm->Relationship->Label."</strong>";
					$Content .= "<div class='tab'>";
					$Content .= frmSpecMap($objLinkForm->ObjectForm);			
					$Content .= "</div>";
				}
//			}
		}			
	}
	

	
	return $Content;
								
}
	

?>