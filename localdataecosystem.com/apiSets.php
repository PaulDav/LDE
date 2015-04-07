<?php
//	require_once("class/clsApi.php");
	require_once("class/clsSystem.php");
	require_once("class/clsData.php");
	
	define('PAGE_NAME', 'apiSets');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);
		
	$ContextId = null;
	$LicenceTypeId = null;
	$OrgId = null;
	$ShapeId = null;

	if (isset($_SESSION['forms'][PAGE_NAME]['contextid'])){
		$ContextId = $_SESSION['forms'][PAGE_NAME]['contextid'];		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['licencetypeid'])){
		$LicenceTypeId = $_SESSION['forms'][PAGE_NAME]['licencetypeid'];		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
		$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
		$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];		
	}
	
	$Sets = new clsSets();
	$Sets->ContextId = $ContextId;
	$Sets->LicenceTypeId = $LicenceTypeId;
	$Sets->OrgId = $OrgId;
	$Sets->ShapeId = $ShapeId;
	
	unset($_SESSION['forms'][PAGE_NAME]);
	
	header ('Content-type: text/xml');
	echo $Sets->xml;
	exit;
	
?>