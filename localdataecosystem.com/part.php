<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlProperty.php");
	require_once("panel/pnlPart.php");
	
	require_once("form/frmField.php");
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'element');

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
		$PropId = '';
		$PartId='';
		
		$Label = '';
		$Description = '';
		$DataType = null;
		$Cardinality = null;
		

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
		if (!isset($objDict->Properties[$PropId])){
			throw new exception("Property does not exist");
		}
		$objProp = $objDict->Properties[$PropId];

		if (isset($_REQUEST['partid'])){
			$PartId = $_REQUEST['partid'];
		}		
		
		switch ($Mode){
			case 'new':
				break;
			default:
				if ($PartId =='') {
					throw new exception("PartId not specified");
				}
				break;
		}

		if (!empty($PartId)){
			$objPart = $objProp->Parts[$PartId];
			$Label = $objPart->Label;
			$Description = $objPart->Description;
			$Cardinality = $objPart->Cardinality;			
			$DataType = $objPart->Field->DataType;
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
			
			if (isset($FormFields['label'])){
				$Label = $FormFields['label'];
			}
			if (isset($FormFields['description'])){
				$Description = $FormFields['description'];
			}
			
			if (isset($FormFields['datatype'])){
				$DataType = $FormFields['datatype'];
			}
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." property part";		
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
				$PanelB .= pnlPart( $DictId, $PropId, $PartId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='part.php?dictid=$DictId&propid=$PropId&partid=$PartId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='part.php?dictid=$DictId&propid=$PropId&partid=$PartId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				

				switch ($objPart->Type){
			    	case 'simple':
			    		
						if ($objDict->canEdit === true){			    		
				    		$Tabs .= "<li><a href='#field'>Field";
							$TabContent .= "<div class='tabContent hide' id='field'>";
								$TabContent .= "<h3>Field</h3>";		
								$TabContent .= frmField($objPart->Field);
								$TabContent .= "</div>";
						    $Tabs .= "</a></li>";
						}
							
			    		break;
				}
				
				$Tabs .= "<li><a href='#property'>in Property";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='property'>";
					$TabContent .= "<div id='dict'><h3>in Property</h3></div>";	
					$TabContent .= pnlProperty($DictId,$PropId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doPart.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				$PanelB .= "<input type='hidden' name='propid' value='$PropId'/>";
				if (!( $PartId == '')){
					$PanelB .= "<input type='hidden' name='partid' value='$PartId'/>";
				}
				
				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $PartId;
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
					$PanelB .= '<th>';
					$PanelB .= 'Data Type';
					$PanelB .= '</th>';
					$PanelB .= '<td>';						
					$PanelB .= "<select name='datatype'>";
					foreach ($System->Config->DataTypes as $optDataType){
						$PanelB .= "<option";
						if ($DataType == $optDataType){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">$optDataType</option>";
					}
					$PanelB .= "</select>";						
					
					$PanelB .= '</td>';
				$PanelB .= '</tr>';					
				
				
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Part">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Part">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlPart( $DictId, $PropId, $PartId );

				$PanelB .= "<a href='doPart.php?dictid=$DictId&propid=$PropId&partid=$PartId&mode=delete'>confirm delete?</a><br/>";
				
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