<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlDict.php");
	require_once("panel/pnlProperty.php");
	
	require_once("form/frmField.php");
	
	
	require_once("class/clsDict.php");	
	
	define('PAGE_NAME', 'property');

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
		$Label = '';
		$Description = '';
		$PropType = '';
		

		if (isset($_REQUEST['dictid'])){
			$DictId = $_REQUEST['dictid'];
		}
		if ($DictId =='') {
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

		switch ($Mode){
			case 'new':
				break;
			default:
				if ($PropId =='') {
					throw new exception("PropId not specified");
				}
				break;
		}

		if (!empty($PropId)){
			$objProp = $objDict->Properties[$PropId];
			$Label = $objProp->Label;
			$Description = $objProp->Description;
			$PropType = $objProp->Type;
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

			if (isset($FormFields['proptype'])){
				$PropType = $FormFields['proptype'];
			}			
			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if ($objDict->canView === false){
			throw new exception("You cannot view this Dictionary");
		}
		
		$Page->Title = $Mode." property";		
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
				$PanelB .= pnlProperty( $DictId, $PropId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objDict->canEdit === true){
					$PanelB .= "<li><a href='property.php?dictid=$DictId&propid=$PropId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objDict->canControl === true){
					$PanelB .= "<li><a href='property.php?dictid=$DictId&propid=$PropId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$PanelB .= "</ul></div>";				

				
				$Tabs .= "<li><a href='#dict'>Dictionary";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='dict'>";
					$TabContent .= "<h3>in Dictionary</h3>";	
					$TabContent .= pnlDict($DictId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";
			    
			    switch ($objProp->Type){
			    	case 'simple':
			    		
						if ($objDict->canEdit === true){			    		
				    		$Tabs .= "<li><a href='#field'>Field";
							$TabContent .= "<div class='tabContent hide' id='field'>";
								$TabContent .= "<h3>Field</h3>";		
								$TabContent .= frmField($objProp->Field);
								$TabContent .= "</div>";
						    $Tabs .= "</a></li>";
						}
							
			    		
			    		if ($objProp->Field->DataType == 'value'){
						    $Tabs .= "<li><a href='#lists'>Lists";
							$num = 0;
							$TabContent .= "<div class='tabContent hide' id='lists'>";
								$TabContent .= "<h3>Lists</h3>";
								
								if (count($objProp->Lists) > 0){
								
									$TabContent .= "<table class='list'>";
									$TabContent .= '<thead>';
									$TabContent .= '<tr>';
										$TabContent .= "<th>Dictionary</th><th>Id</th><th>Label</th><th>Description</th>";
									$TabContent .= '</tr>';
									$TabContent .= '</thead>';
													
									
									foreach ( $objProp->Lists as $objPropertyList){
									
										$num = $num + 1;
										
										$TabContent .= "<tr>";
										
										$ListId = $objPropertyList->ListId;
										$objListDict = $objDict;
										if (!($objPropertyList->ListDictId == $DictId)){
											$objListDict = $Dicts->Dictionaries[$objPropertyList->ListDictId];
										}
										$objList = $objListDict->Lists[$ListId];
										
										$TabContent .= "<td>".$objList->DictId."</td>";								
										$TabContent .= "<td><a href='list.php?dictid=".$objList->DictId."&listid=$ListId'>".$ListId."</a></td>";
																	
										$TabContent .= "<td>".$objList->Label."</td>";
										$TabContent .= "<td>".nl2br(Truncate($objList->Description))."</td>";
										
										$TabContent .= "<td><li><a href='propertylist.php?dictid=$DictId&propid=$PropId&listdictid=".$objList->DictId."&listid=".$objList->Id."&mode=delete'>remove</a></li></td>";
										
										$TabContent .= "</tr>";
										
									}
									
									
						 			$TabContent .= '</table>';
								}
									
								$TabContent .= "<li><a href='propertylist.php?dictid=$DictId&propid=$PropId&mode=new'>add</a></li> ";
								
							$TabContent .= "</div>";						    
						    $Tabs .= "($num)</a></li>";
					    }
					    break;
					    
			    	case 'complex':
			    
					    $Tabs .= "<li><a href='#elements'>Element Groups";
						$num = 0;
						$TabContent .= "<div class='tabContent hide' id='elements'>";
							$TabContent .= "<h3>Element Groups</h3>";	
							
							$TabContent .= "<div class='tab'>";
							foreach ($objProp->ElementGroups as $objElementGroup){
								$num = $num + 1;
								$TabContent .= "<table class='list'>";
								$TabContent .= '<thead>';
								$TabContent .= '<tr>';
								$TabContent .= "<th>Seq</th><th>Dictionary</th><th>Property</th><th>Cardinality</th>";
								$TabContent .= '</tr>';
								$TabContent .= '</thead><tbody>';

								$seq = 0;
								foreach ($objElementGroup->Elements as $objElement){
									$objElementProperty = $Dicts->getProperty($objElement->DictId, $objElement->PropId);
									if (is_object($objElementProperty)){
									
										$seq = $seq + 1;
										$TabContent .= '<tr>';
										
										$TabContent .= "<td><a href='propertyelement.php?dictid=$DictId&propid=$PropId&groupseq=$num&elementdictid=".$objElement->DictId."&elementpropid=".$objElement->PropId."'>$seq</td>";
										if (is_object($objElement)){
											$objElementDict = $Dicts->Dictionaries[$objElement->DictId];
											$TabContent .= '<td>'.$objElementDict->Name.'</td>';
											$TabContent .= '<td>'.$objElementProperty->Label.'</td>';
										}
										$TabContent .= '<td>'.$objElement->Cardinality.'</td>';
										$TabContent .= '</tr>';
									}
								}
								
								
								$TabContent .= '</tbody></table>';
							
								if ($objDict->canEdit === true){
									$TabContent .= "<div class='hmenu'><ul><li><a href='propertyelement.php?dictid=$DictId&propid=$PropId&groupseq=$num&mode=new'>&bull; add an element</a></li> </ul></div>";
									$TabContent .= "<div class='hmenu'><ul><li><a href='doPropertyGroup.php?dictid=$DictId&propid=$PropId&groupseq=$num&mode=delete'>&bull; delete the group</a></li> </ul></div>";									
								}
								$TabContent .= '<br/>';
							}
							
							
							$TabContent .= "</div>";
							
							if ($objDict->canEdit === true){								
								$TabContent .= "<div class='hmenu'><ul><li><a href='doPropertyGroup.php?dictid=$DictId&propid=$PropId&mode=new'>&bull; add a group</a></li> </ul></div>";
							}					
							
						$TabContent .= "</div>";
					    $Tabs .= "($num)</a></li>";
					    
					    break;
			    }
			    
			    $Tabs .= "<li><a href='#subproperties'>Sub Properties";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='subproperties'>";
					$TabContent .= "<h3>Sub Properties</h3>";

					$arrSubProps = array();
					foreach ($Dicts->Dictionaries as $optDict){
						foreach ($optDict->Properties as $optProp){
							if (($optProp->SubDictOf == $DictId) && ($optProp->SubPropertyOf == $PropId )){
								$arrSubProps[$optProp->DictId][$optProp->Id] = $optProp;
							}
						}
					}
					

					if (count($arrSubProps) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Dictionary</th><th>Label</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';

						foreach ($arrSubProps as $SubDictId=>$SubProps){
							$TabContent .= "<tr>";
							$TabContent .= "<td rowspan='".(count($SubProps) + 1)."'>";
							$optDict = $Dicts->Dictionaries[$SubDictId];
							$TabContent .= $optDict->Name;
							$TabContent .= "</td>";
							$TabContent .= "</tr>";
						
							foreach ( $SubProps as $SubProp){
								
								$num = $num + 1;
								
								$TabContent .= "<tr>";
								
								$TabContent .= "<td><a href='property.php?dictid=".$SubProp->DictId."&propid=".$SubProp->Id."'>".$SubProp->Label."</a></td>";
								$TabContent .= "<td>".nl2br(Truncate($SubProp->Description))."</td>";
							}
						}
				 		$TabContent .= '</table>';
					}
					
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    
			    $Tabs .= "<li><a href='#classes'>used in Classes";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='classes'>";
					$TabContent .= "<h3>used in Classes</h3>";	
					
					$arrClasses = array();
					foreach ($Dicts->Dictionaries as $optDict){
						foreach ($optDict->Classes as $optClass){
							foreach ($optClass->Properties as $optClassProp){
								if (($optClassProp->PropDictId == $DictId) && ($optClassProp->PropId == $PropId )){
									$arrClasses[$optClass->DictId][$optClass->Id] = $optClass;
								}
							}
						}
					}
					

					if (count($arrClasses) > 0){
						
						$TabContent .= "<table class='list'>";
						$TabContent .= '<thead>';
						$TabContent .= '<tr>';
							$TabContent .= "<th>Dictionary</th><th>Id</th><th>Label</th><th>Description</th>";
						$TabContent .= '</tr>';
						$TabContent .= '</thead>';

						foreach ($arrClasses as $ClassDictId=>$Classes){
							$TabContent .= "<tr>";
							$TabContent .= "<td rowspan='".(count($Classes) + 1)."'>";
							$optDict = $Dicts->Dictionaries[$ClassDictId];
							$TabContent .= $optDict->Name;
							$TabContent .= "</td>";
							$TabContent .= "</tr>";
						
							foreach ( $Classes as $Class){
								
								$num = $num + 1;
								
								$TabContent .= "<tr>";
								
								$TabContent .= "<td><a href='class.php?dictid=".$Class->DictId."&classid=".$Class->Id."'>".$Class->Id."</a></td>";
								$TabContent .= "<td>".$Class->Label."</td>";
								$TabContent .= "<td>".nl2br(Truncate($Class->Description))."</td>";
							}
						}
				 		$TabContent .= '</table>';
					}
					
				$TabContent .= "</div>";
			    $Tabs .= "($num)</a></li>";
			    
			    if ( $objDict->canEdit ){
					$Tabs .= "<li><a href='#setsuperprop'>set the Super Property</a></li>";
				    
					$TabContent .= "<div class='tabContent hide' id='setsuperprop'>";
					
					if (!is_null($objProp->SubPropertyOf)){
						$TabContent .= "<a href='doSuperProp.php?mode=delete&dictid=$DictId&propid=$PropId'>remove super property</a><br/>";
					}
										
					$TabContent .= "<h3>Set the Super Property from any of these selections</h3>";	
				
					$optTabs = "";
					$optTabContent = "";
					
					$optTabs .= "<li><a href='#thissuperprops'>Properties in this Group</a></li>";
					$optTabContent .= funSelectProp("this");
					
					$optTabs .= "<li><a href='#mysuperprops'>Properties in My Groups</a></li>";			    
					$optTabContent .= funSelectProp("my");
					
					$optTabs .= "<li><a href='#publishedsuperprops'>Properties in Published Dictionaries</a></li>";			    
					$optTabContent .= funSelectProp("published");
					
					if (!empty($optTabs)){
						$TabContent .= "<ul class='tabstrip'>".$optTabs."</ul>".$optTabContent;
					}
					$TabContent .= "</div>";
				}
				
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doProperty.php">';

				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $DictId == '')){
					$PanelB .= "<input type='hidden' name='dictid' value='$DictId'/>";
				}
				if (!( $PropId == '')){
					$PanelB .= "<input type='hidden' name='propid' value='$PropId'/>";
				}

				$PanelB .= '<table class="sdbluebox">';
				
				if ($Mode == "edit"){
					$PanelB .= '<tr>';
						$PanelB .= '<th>';
						$PanelB .= 'Id';
						$PanelB .= '</th>';
						$PanelB .= '<td>';
						$PanelB .= $PropId;
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
					$PanelB .= 'Type';
					$PanelB .= '</th>';
					$PanelB .= '<td>';						
					$PanelB .= "<select name='proptype'>";
					foreach ($System->Config->PropertyTypes as $optPropType){
						$PanelB .= "<option";
						if ($PropType == $optPropType){
							$PanelB .= " selected='true' ";
						}
						$PanelB .= ">$optPropType</option>";
					}
					$PanelB .= "</select>";						
					
					$PanelB .= '</td>';
				$PanelB .= '</tr>';					
				
				
				$PanelB .= '<tr>';
					$PanelB .= '<td/>';
					$PanelB .= '<td>';
					
					switch ( $Mode ){
						case "new":
							$PanelB .= '<input type="submit" value="Create New Property">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Property">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';

				break;
				
			case 'delete':
				
				$PanelB .= pnlProperty( $DictId, $PropId );

				$PanelB .= "<a href='doProperty.php?dictid=$DictId&propid=$PropId&mode=delete'>confirm delete?</a><br/>";
				
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

	$Content .= "<div class='tabContent' id='".$Selection."superprops'>";
	
	$Content .= "<div class='sdbluebox'>";
	

	$opts = array();
	
	switch ($Selection){
		case "this":
			foreach ($Dicts->Dictionaries as $optDict){
				if (is_null($optDict->EcoSystem)){
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
				
				$ReturnUrl = "doSuperProp.php?mode=edit&dictid=".$objDict->Id."&propid=".$objProp->Id."&superdictid=$optDictId&superpropid=".$optProp->Id;
				
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