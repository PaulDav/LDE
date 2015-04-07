<?php
	require_once("class/clsPage.php");
	require_once("class/clsSystem.php");
		
	session_start();

	$System = new clsSystem();
			
	$Page = new clsPage();
	$PanelA = '';
	$PanelB = '';
	$PanelC = '';
		
	$Page->ContentPanelA = $PanelA;
	$Page->ContentPanelB = $PanelB;
	$Page->ContentPanelC = $PanelC;
	
	$Page -> Display();	
	
?>