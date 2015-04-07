<?php

include('PHPcoord-2.3.php');

function OSGB36toWGS84($Easting, $Northing){

	$os1 = new OSRef($Easting, $Northing);
	return $os1->toLatLng();
		
}

?>