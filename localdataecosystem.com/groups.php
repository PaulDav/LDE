<?php

	require_once("class/clsSystem.php");

	require_once("class/clsPage.php");
	require_once("function/utils.inc");
	require_once("class/clsGroup.php");
			
	define('PAGE_NAME', 'groups');
		
	session_start();

	$System = new clsSystem();
	$objPage = new clsPage();
		
	try {
		
		$objPage->Title = "design groups";

		$ContentPanelB = '';
			
		$ContentPanelB .= "<h1>".$objPage->Title."</h1>";
		
		$ContentPanelB .= "<ul id='tabs'>";
		if ($System->LoggedOn){
			$ContentPanelB .= "<li><a href='#my'>My Groups</a></li>";
		}		
		$ContentPanelB .= "<li><a href='#public'>Public Groups</a></li>";
		$ContentPanelB .= "</ul>";

		$ContentPanelB .= "<div class='tabContent hide' id='public'>";
		$ContentPanelB .= ListGroups('public');
		$ContentPanelB .= "</div>";
		
		if ($System->LoggedOn){
			$ContentPanelB .= "<div class='tabContent hide' id='my'>";
			$ContentPanelB .= "<a href='group.php?mode=new'>add</a><br/>";
			$ContentPanelB .= ListGroups('my');
			$ContentPanelB .= "</div>";		
	 	}
	 	
	 	$objPage->ContentPanelB = $ContentPanelB;
	 	
	}
	catch(Exception $e)  {
		$System->Session->ErrorMessage = $e->getMessage();
	}
	 	
	$objPage -> Display();
	

	
function ListGroups($Selection){
	
	global $System;
	
	$Content = "";

	$objGroups = new clsGroups();

	switch ($Selection){
		case "public":
			$objGroups->Published = true;
			break;			
		case "my":
			$objGroups->UserId = $System->User->Id;
			break;
	}

	$objGroups->objRst->Paged = true;	
	
	$objGroups->objRst->PageNo = 1;
	if (isset($_REQUEST[$Selection."page"])){
		$objGroups->objRst->PageNo = $_REQUEST[$Selection."page"];
	}
	
	$objGroups->getIds();

	if (!is_null($objGroups->objRst->PageNo)){
		$Content .= "<div>";
		$Content .= "page ".$objGroups->objRst->PageNo." of ".$objGroups->objRst->NumPages;
		if ($objGroups->objRst->PageNo > 1){
			$ReturnURL = UpdateUrl(array($Selection."page"=>($objGroups->objRst->PageNo-1)))."#$Selection";
			$Content .= " <a href=$ReturnURL>&bull; previous</a> ";
		}
		if ($objGroups->objRst->PageNo < $objGroups->objRst->NumPages){
			$ReturnURL = UpdateUrl(array($Selection."page"=>($objGroups->objRst->PageNo+1)))."#$Selection";
			$Content .= " <a href=$ReturnURL>&bull; next</a>";
		}
			
		$Content .= "</div>";
	}

	$arrGroups = array();
	
	foreach ($objGroups->Ids as $GroupId) {
		$objGroup=new clsGroup($GroupId);
		if ($objGroup->canView){
			$arrGroups[] = $objGroup;
		}
	}
	
	
	if (count($arrGroups) > 0){
				
		$Content .= "<table class='list'>";
		$Content .= '<thead>';
		$Content .= '<tr>';
			$Content .= "<th>Group</th><th>Description</th>";
			if ($System->LoggedOn){					
				$Content .= "<th>My Membership</th><th>My Rights</th>";
			}
			$Content .= "<th>owner</th>";			
		$Content .= '</tr>';		
		$Content .= '</thead>';

		foreach ( $arrGroups as $objGroup){
			$Content .= "<tr>";

			$Content .= "<td>";
			if (!is_null($objGroup->Picture)) {
				$Content .= "<a href='group.php?groupid=".$objGroup->Id."'><img class='byimage' src='image.php?Id=".$objGroup->Picture."' /></a><br/>";
			}
			$Content .= "<a href='group.php?groupid=".$objGroup->Id."'>".$objGroup->Name."</td>";
			$Content .= "<td>".nl2br(Truncate($objGroup->Description))."</td>";
			
			if ($System->LoggedOn){
				$Content .= "<td>";					
				if ($objGroup->MyStatus->Status > 0){
					$Content .= $objGroup->MyStatus->Name;
				}
				else
				{
					$Content .= "<a href='doUserGroup.php?GroupId=".$objGroup->Id."&Mode=request'>&bull; request to join</a>";
				}
				$Content .= "</td>";
			
				$Content .= "<td>";
				$Content .= $objGroup->MyRights->Name;
				$Content .= "</td>";
			}
			
			$objGroupOwner = new clsUser($objGroup->OwnerId);
							
			$Content .= "<td>";
			if (!is_null($objGroupOwner->PictureOf)) {
				$Content .= '<img height = "30" src="image.php?Id='.$objGroupOwner->PictureOf.'" /><br/>';
			}

			$Content .= $objGroupOwner->Name."</td>";
			
		}
 		$Content .= '</table>';
		
	}
	return $Content;	
		
}	
	
?>