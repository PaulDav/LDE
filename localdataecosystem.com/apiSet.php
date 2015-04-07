<?php
	require_once("class/clsSystem.php");
	require_once("class/clsData.php");
	
	define('PAGE_NAME', 'apiSet');
	
	session_start();
	
	$System = new clsSystem();
	global $System;
	
	SaveUserInput(PAGE_NAME);
		
	$SetId = null;
	
	if (isset($_SESSION['forms'][PAGE_NAME]['setid'])){
		$SetId = $_SESSION['forms'][PAGE_NAME]['setid'];
	}
	
	$objSet = new clsSet($SetId);	
	unset($_SESSION['forms'][PAGE_NAME]);
	
	header ('Content-type: text/xml');
	
	$result = $objSet->xml->saveXML();

	echo $result;
	$System->db->close();
	
	exit;
	
?>