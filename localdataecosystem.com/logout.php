<?php

	require_once('class/clsSystem.php');

	session_start();
	$System = new clsSystem();
	$Session = $System->Session;
	
	
	$Session->Clear('UserName');
	$Session->Clear('UserId');

	session_destroy();

	header("Location: .");
	
?>