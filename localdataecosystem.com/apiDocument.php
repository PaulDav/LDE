<?php
	require_once("class/clsSystem.php");
	require_once("class/clsData.php");
	
	define('PAGE_NAME', 'apiDocument');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
	
	$System = new clsSystem();
	global $System;
		
	$DocId = null;
	
	if (isset($_SESSION['forms'][PAGE_NAME]['docid'])){
		$DocId = $_SESSION['forms'][PAGE_NAME]['docid'];
	}
	
	$objDoc = new clsDocument($DocId);	
	unset($_SESSION['forms'][PAGE_NAME]);
	
	header ('Content-type: text/xml');
	
	$result = $objDoc->xml->saveXML();
	echo $result;
	
	exit;
	
?>