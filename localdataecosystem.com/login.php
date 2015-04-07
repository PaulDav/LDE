<?php

	require_once("class/clsPage.php");
	
	session_start();
		
	$LoginPage = new clsPage();
	$LoginPage->Title = "Login";
	
	$ContentPanelB = '';
	
	$ContentPanelB .= '<p><a href="account.php">Not a member?</a></p>';
	$ContentPanelB .= '<form method="post" action="doLogin.php">';
	$ContentPanelB .= '<table class="sdbluebox">';
	$ContentPanelB .= '<tr><td colspan="2">Members log in here:</td></tr>';
	$ContentPanelB .= '<tr><td>Your ID:</td><td><input type="text" name="handle"/></td></tr>';
   	$ContentPanelB .= '<tr><td>Password:</td><td><input type="password" name="passwd"/></td></tr>';
	$ContentPanelB .= '<tr><td colspan="2" align="center"><input type="submit" value="Log in"/></td></tr>';
	$ContentPanelB .= '<tr><td colspan="2"><a href="usrreset.php">Forgot your password?</a></td></tr>';
	$ContentPanelB .= '</table></form>';

	$LoginPage->ContentPanelB = $ContentPanelB;
	
	$LoginPage -> Display();
	
?>