<?php

require_once("class/clsSystem.php");
require_once("class/clsRights.php");
	
	define('PAGE_NAME', 'apiLicences');
	
	session_start();
	$System = new clsSystem();
		
	SaveUserInput(PAGE_NAME);

	$OrgId = null;
	$ShapeId = null;
	
	if (isset($_SESSION['forms'][PAGE_NAME]['orgid'])){
		$OrgId = $_SESSION['forms'][PAGE_NAME]['orgid'];		
	}
	
	if (isset($_SESSION['forms'][PAGE_NAME]['shapeid'])){
		$ShapeId = $_SESSION['forms'][PAGE_NAME]['shapeid'];		
	}
	
	$Licences = new clsLicences();
	$Licences->OrgId = $OrgId;
	$Licences->ShapeId = $ShapeId;
	
	unset($_SESSION['forms'][PAGE_NAME]);
	
	header ('Content-type: text/xml');
	echo $Licences->xml;
	exit;
	
?>