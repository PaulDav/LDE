<?php
	require_once("class/clsApi.php");
		
	session_start();

	$Api = new clsApi();
	
	header ('Content-type: text/xml');
	echo $Api->xml;		
	exit;
	
?>