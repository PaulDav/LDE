<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlList.php");
	
	require_once("class/clsDict.php");	
	require_once("class/clsModel.php");
	
	define('PAGE_NAME', 'list');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	$objModel = new clsModel();
			
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
		$ListId = '';
		$Label = '';
		$Description = '';
		$Source = '';
		$DescribedAt = '';
		

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

		switch ($Mode){
			case 'new':
				break;
			default:
				if ($ListId =='') {
					throw new exception("ListId not specified");
				}
				break;
		}

		if (!empty($ListId)){
			$objList = $objDict->Lists[$ListId];
			$Label = $objList->Label;
			$Description = $objList->Description;
			$Source = $objList->Source;
			$DescribedAt = $objList->DescribedAt;
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
			
			if (isset($FormFields['label'])){
				$Label = $FormFields['label'];
			}
			if (isset($FormFields['description'])){
				$Description = $FormFields['description'];
			}
			if (isset($FormFields['source'])){
				$Source = $FormFields['source'];
			}
			if (isset($FormFields['describedat'])){
				$DescribedAt = $FormFields['describedat'];
			}
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." list";
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
				$PanelB .= pnlList( $DictId, $ListId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='list.php?dictid=$DictId&listid=$ListId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='list.php?dictid=$DictId&listid=$ListId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";				

				
				$Tabs .= "<li><a href='#dict'>Dictionary";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='dict'>";
					$TabContent .= "<div id='dict'><h3>in Dictionary</h3></div>";	
					$TabContent .= pnlDict($DictId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
			    $Tabs .= "<li><a href='#values'>Values";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='values'>";
					$TabContent .= "<h3>Values</h3>";	

					if (count($objList->Values) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Id</th><th>Value</th><th>Description</th><th>Code</th><th>URI</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $objList->Values as $objListValue){
							
							$num = $num + 1;
							
							$TabContent .= "<tr>";
							
							$TabContent .= "<td><a href='listvalue.php?dictid=".$objDict->Id."&listid=".$objList->Id."&listvalueid=".$objListValue->Id."'>".$objListValue->Id."</a></td>";

							$objValueDict = $objDict;
							if (!is_null($objListValue->ValueDictId)){
								if (!($objListValue->ValueDictId == $DictId)){
									$objValueDict = $Dicts->Dictionaries[$objListValue->ValueDictId];
								}
							}
							
							$objValue = $objValueDict->Values[$objListValue->ValueId];
							
							$TabContent .= "<td>".$objValue->Label."</td>";
							$TabContent .= "<td>".nl2br(Truncate($objValue->Description))."</td>";
							
							$TabContent .= "<td>".$objValue->Code."</td>";
							$TabContent .= "<td>".$objValue->URI."</td>";
							
							$TabContent .= "</tr>";
							
							
						}
				 		$TabContent .= '</table>';
					}					
					
					if ($objDict->canEdit === true){				
						$TabContent .= "<div class='hmenu'><ul><li><a href='listvalue.php?dictid=$DictId&listid=$ListId&mode=new'>&bull; add</a></li> </ul></div>";
					}

				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";

			    
			    $Tabs .= "<li><a href='#properties'>used in";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='properties'>";
			    
			    	$arrProperties = array();
					foreach ($Dicts->Dictionaries as $optDict){
						foreach ($optDict->Properties as $optProp){
							foreach ($optProp->Lists as $optPropList){
								if (($optPropList->ListDictId == $objList->DictId) && ($optPropList->ListId == $objList->Id)){
									$arrProperties[$optDict->Id][$optProp->Id] = $optProp;
								}
							}
						}
					}
			    
					
					if (count($arrProperties) > 0){

						$TabContent .= "<h3>used in Properties</h3>";	
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Dictionary</th><th>Property</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';
					
						foreach ( $arrProperties as $optDictId=>$optProperties){							
							$optDict = $Dicts->Dictionaries[$optDictId];
							foreach ($optProperties as $optProp){ 
																
								$num = $num + 1;
									
								$TabContent .= "<tr>";
								$TabContent .= "<td><a href=dict.php?dictid=".$optDict->Id.">".$optDict->Name."</a></td>";
								$TabContent .= "<td><a href=property.php?dictid=".$optProp->DictId."&propid=".$optProp->Id.">".$optProp->Label."</a></td>";												
								$TabContent .= "</tr>";
							}							
						}
				 		$TabContent .= '</table>';
					}	
					
			    $TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
					
			    
				break;
			case 'new':
			case 'edit':
				
				$PanelB .= '<form method="post" action="doList.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $DictId == '')){
					$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				}
				if (!( $ListId == '')){
					$PanelB .= "<input type='hidden' name='listid' value='$ListId'/>";
				}

				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $ListId;
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
					$PanelB .= 'Source';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="source" size="30" maxlength="100" value="'.$Source.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';

				$PanelB .= '<tr>';
					$PanelB .= '<th>';
					$PanelB .= 'Described at';
					$PanelB .= '</th>';
					$PanelB .= '<td>';
					$PanelB .= '<input type="text" name="describedat" size="30" maxlength="100" value="'.$DescribedAt.'">';
					$PanelB .= '</td>';
				$PanelB .= '</tr>';
				
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New List">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update List">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlList( $DictId, $ListId );

				$PanelB .= "<a href='doList.php?dictid=$DictId&listid=$ListId&mode=delete'>confirm delete?</a><br/>";
				
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