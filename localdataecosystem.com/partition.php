<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	
	require_once("function/utils.inc");

	require_once("panel/pnlPartition.php");	
	require_once("panel/pnlPartitionMap.php");	
	
	require_once("panel/pnlProfile.php");
	
	require_once("panel/pnlGroup.php");
	
	require_once("class/clsGroup.php");
	require_once("class/clsDict.php");	
	require_once("class/clsProfile.php");
	require_once("class/clsPartition.php");
	
	define('PAGE_NAME', 'partition');

	session_start();
		
	$System = new clsSystem();
	
	
	SaveUserInput(PAGE_NAME);
	$FormFields = getUserInput(PAGE_NAME);
	
	$Page = new clsPage();
	
	$objPartition = null;
	$objProfile = null;

	try {

		$Dicts = new clsDicts();
		$Profiles = new clsProfiles();
		$Partitions = new clsPartitions();
				
		$Mode = 'view';
		if (isset($_REQUEST['mode'])){
			$Mode = $_REQUEST['mode'];
		}	

		$PanelB = '';
		$PanelC = '';

		$Tabs = "";
		$TabContent = "";

		$PartId = null;
		$ProfileId = null;
		$GroupId = null;
		$Name = '';
		$Description = '';
		$Publish = false;
		$ProfileId = null;
		$objProfile = null;

		if (isset($_REQUEST['partid'])){
			$PartId = $_REQUEST['partid'];
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
				if ($PartId =='') {
					throw new exception("PartId not specified");
				}

				break;
		}

		if (!empty($PartId)){
			$objPartition = new clsPartition($PartId);
			$GroupId = $objPartition->GroupId;
			$Name = $objPartition->Name;
			$Description = $objPartition->Description;
			$Publish = $objPartition->Publish;
			$ProfileId = $objPartition->ProfileId;
			
			$objProfile = $Profiles->Profiles[$ProfileId];
			
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
		
		$Page->Title = $Mode." partition";
		$PanelB .= "<h1>".$Page->Title."</h1>";
				
		$ModeOk = false;
		switch ($Mode){
			case 'view':
				if ($objPartition->canView){
					$ModeOk = true;
				}
				break;
			case 'new':
				if ($Group->canEdit){
					$ModeOk = true;
				}
				break;
			case 'edit':
				if ($objPartition->canEdit){
					$ModeOk = true;
				}
				break;
			case 'delete':
				if ($objPartition->canEdit){
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
				
				$PanelB .= pnlPartition( $PartId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objPart->canEdit === true){
					$PanelB .= "<li><a href='partition.php?partid=$PartId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objPart->canControl === true){
					$PanelB .= "<li><a href='partition.php?partid=$PartId&mode=delete'>&bull; delete</a></li> ";
				}
				
				$Tabs .= "<li><a href='#group'>Group";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='group'>";
					$TabContent .= "<h3>in Group</h3>";	
					$TabContent .= pnlGroup($GroupId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

			    if (is_object($objProfile)){
				    $Tabs .= "<li><a href='#filter'>Filter";
					$num = 0;
					$TabContent .= "<div class='tabContent hide' id='filter'>";
						$TabContent .= "<h3>Filter</h3>";
						
					$TabContent .= "</div>";
				    $Tabs .= "</a></li>";
			    }
			    
				break;
			case 'new':
			case 'edit':
				$PanelB .= '<form method="post" action="doPartition.php">';
		
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				if (!( $PartId == '')){
					$PanelB .= "<input type='hidden' name='partid' value='$PartId'/>";
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
						$PanelB .= $PartId;
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
					foreach ($Profiles->Profiles as $optProfile){
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
							$PanelB .= '<input type="submit" value="Create New Partition">';
							break;
						case "edit":
							$PanelB .= '<input type="submit" value="Update Partition">';
							break;
					}

					$PanelB .= '</td>';
				$PanelB .= '</tr>';
		
			 	$PanelB .= '</table>';
				$PanelB .= '</form>';
								
				break;
				
			case 'delete':
				
				$PanelB .= pnlPartition( $PartId );
				
				$PanelB .= "<a href='doPartition.php?partid=$PartId&Mode=delete'>confirm delete?</a><br/>";
				
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