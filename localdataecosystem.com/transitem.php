<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	
	require_once("panel/pnlTranslation.php");
	require_once("panel/pnlTransItem.php");
		
	require_once("class/clsProfile.php");	
	
	define('PAGE_NAME', 'transitem');

	session_start();
		
	$System = new clsSystem();
	
	$Page = new clsPage();

	try {

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
		$ItemId = null;
		$TransId = null;
		
		$FromValue = '';
		$ToValue = '';
		
		if (isset($_REQUEST['specid'])){
			$SpecId = $_REQUEST['specid'];
		}
		if (is_null($SpecId)) {
			throw new exception("Spec Id not specified");
		}

		if (!isset($Specs->Items[$SpecId])){
			throw new exception("Unknown Spec");
		}
		$objSpec = $Specs->Items[$SpecId];
		
		if (isset($_REQUEST['transid'])){
			$TransId = $_REQUEST['transid'];
		}
		if (is_null($TransId)) {
			throw new exception("Trans Id not specified");
		}

		if (!isset($objSpec->Translations[$TransId])){
			throw new exception("Unknown Translation");
		}
		$objTrans = $objSpec->Translations[$TransId];
		
		$GroupId = $objSpec->GroupId;

		
		if (isset($_REQUEST['itemid'])){
			$ItemId = $_REQUEST['itemid'];
		}
		if (!is_null($ItemId)) {
			if (!isset($objTrans->Items[$ItemId])){
				throw new exception("Unknown Item");
			}
			$objItem = $objTrans->Items[$ItemId];
			$FromValue = $objItem->FromValue;
			$ToValue = $objItem->ToValue;
		}

		
		switch ($Mode){
			case 'new':
				break;
			default:
				if (is_null($ItemId)) {
					throw new exception("ItemId not specified");
				}
				break;
		}


		if ($System->Session->Error){			
			unset($_SESSION['forms'][PAGE_NAME]);
			$System->Session->Clear('Error');			
		}
		
		if (!$objSpec->canView){
			throw new exception("You cannot view this Item");
		}
		
		$Page->Title = $Mode." translation item";		
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
				$PanelB .= pnlTransItem( $SpecId, $TransId, $ItemId );
				
				$PanelB .= "<div class='hmenu'><ul>";
				if ($objSpec->canEdit === true){
					$PanelB .= "<li><a href='transitem.php?specid=$SpecId&transid=$TransId&itemid=$ItemId&mode=edit'>&bull; edit</a></li> ";
				}
				if ($objSpec->canControl === true){
					$PanelB .= "<li><a href='transitem.php?specid=$SpecId&transid=$TransId&itemid=$ItemId&mode=delete'>&bull; delete</a></li> ";
				}

				$PanelB .= "</ul></div>";				
				
				$Tabs .= "<li><a href='#trans'>Translation";
				$num = 0;
				$TabContent .= "<div class='tabContent hide' id='trans'>";
					$TabContent .= "<div id='dict'><h3>in Translation</h3></div>";	
					$TabContent .= pnlTranslation($SpecId, $TransId);	
				$TabContent .= "</div>";
			    $Tabs .= "</a></li>";

			    
				break;
			case 'new':
			case 'edit':
				
				$PanelB .= '<form method="post" action="doTransItem.php">';
	
				$PanelB .= "<input type='hidden' name='mode' value='$Mode'/>";
				$PanelB .= "<input type='hidden' name='specid' value='$SpecId'/>";
				$PanelB .= "<input type='hidden' name='transid' value='$TransId'/>";
				if (!is_null($ItemId)){
					$PanelB .= "<input type='hidden' name='itemid' value='$ItemId'/>";
				}
				
				$PanelB .= "<div class='sdbluebox'>";
				$PanelB .= '<table>';
					
				$PanelB .= '<tr>';
				$PanelB .= '<th>From Value</th>';
				$PanelB .= "<td><input name='fromvalue' value='$FromValue' size='30'/></td>";
				$PanelB .= '</tr>';

				$PanelB .= '<tr>';
				$PanelB .= '<th>To Value</th>';
				$PanelB .= "<td>";
				if (is_null($objTrans->List)){
					
				}
				else
				{
					$PanelB .= "<select name='tovalue'>";
					$PanelB .= "<option/>";
					foreach ($objTrans->List->Values as $objListValue){
						if (!is_null($objListValue->ValueId)){
							$optValue = $Dicts->Dictionaries[$objListValue->ValueDictId]->Values[$objListValue->ValueId];
							$PanelB .= "<option";						
							if ($optValue->Label == $ToValue){
								$PanelB .= " selected='true' ";
							}
							$PanelB .= ">".$optValue->Label."</option>";
						}
					}
					$PanelB .= "</select>";
				}
				
				$PanelB .= "</table>";
					
				switch ( $Mode ){
					case "new":
						$PanelB .= '<input type="submit" value="Add Item">';
						break;
					case "edit":
						$PanelB .= '<input type="submit" value="Update Item">';
						break;
				}
			
			 	$PanelB .= '</div>';
				$PanelB .= '</form>';

				break;
				
				
			case 'delete':
				
				$PanelB .= pnlTransItem( $SpecId, $TransId, $ItemId );

				$PanelB .= "<a href='doTransItem.php?specid=$SpecId&transid=$TransId&itemid=$ItemId&mode=delete'>confirm delete?</a><br/>";
				
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