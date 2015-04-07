<?php

require_once(dirname(__FILE__).'/../class/clsData.php');
require_once('pnlProfile.php');

require_once(dirname(__FILE__).'/../function/utils.inc');

Function pnlSetProfile( $SetId, $SetProfileId){

	$objSet = new clsSet($SetId);
	$objSetProfile = $objSet->SetProfiles[$SetProfileId];
	
	$Content = '';

	$Content .= '<table class="sdgreybox">';
	$Content .= "<tr><th>Id</th><td><a href='setprofile.php?setid=$SetId&setprofileid=$SetProfileId'>".$SetProfileId."</a></td></tr>";
	$Content .= "<tr><th>Profile</th><td>".pnlProfile($objSetProfile->ProfileId)."</td></tr>";	
    $Content .= '</table>';
	    
    return $Content;
}


?>